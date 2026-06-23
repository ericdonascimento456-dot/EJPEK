<?php
declare(strict_types=1);

/**
 * Model OrdemServico
 *
 * Encapsula regras de negócio e acesso às tabelas ORDEM_SERVICO e ITEM_OS.
 * Princípio SRP: responsável exclusivamente pela entidade Ordem de Serviço.
 */
class OrdemServico
{
    private PDO $pdo;

    public const STATUSES = ['Aberta', 'Em andamento', 'Aguardando peca', 'Finalizada', 'Cancelada'];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Lista OS com dados de cliente e veículo, filtros opcionais. */
    public function listar(string $q = '', string $status = ''): array
    {
        $where  = "WHERE 1=1";
        $params = [];
        if ($status) { $where .= " AND os.status=?";                  $params[] = $status; }
        if ($q)      { $where .= " AND (c.nome LIKE ? OR v.placa LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }

        $stmt = $this->pdo->prepare("
            SELECT os.*, c.nome AS cliente_nome, v.placa, v.modelo, v.marca
            FROM ORDEM_SERVICO os
            JOIN CLIENTE c ON c.id_cliente=os.id_cliente
            JOIN VEICULO v ON v.id_veiculo=os.id_veiculo
            $where ORDER BY os.id_os DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Busca OS completa (com cliente, veículo e usuário de abertura). */
    public function buscarPorId(int $id): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT os.*,
                   c.nome AS cliente_nome, c.celular AS cliente_cel, c.email AS cliente_email,
                   v.placa, v.modelo, v.marca, v.ano, v.cor, v.km_atual, v.combustivel,
                   v.renavam, v.chassi, v.foto AS veiculo_foto,
                   u.nome AS aberto_por
            FROM ORDEM_SERVICO os
            JOIN CLIENTE c  ON c.id_cliente = os.id_cliente
            JOIN VEICULO v  ON v.id_veiculo = os.id_veiculo
            LEFT JOIN USUARIO u ON u.id_usuario = os.id_usuario_abertura
            WHERE os.id_os = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Busca os itens (peças) de uma OS. */
    public function buscarItens(int $idOs): array
    {
        $stmt = $this->pdo->prepare("
            SELECT i.*, p.descricao AS peca_nome, p.codigo
            FROM ITEM_OS i JOIN PECA p ON p.id_peca=i.id_peca
            WHERE i.id_os=?
        ");
        $stmt->execute([$idOs]);
        return $stmt->fetchAll();
    }

    /** Abre nova OS. */
    public function abrir(array $d): int
    {
        $this->pdo->prepare(
            "INSERT INTO ORDEM_SERVICO (diagnostico, prazo_previsto, id_cliente, id_veiculo, id_usuario_abertura)
             VALUES (?,?,?,?,?)"
        )->execute([$d['diagnostico'], $d['prazo'] ?: null, $d['id_cliente'], $d['id_veiculo'], $d['id_usuario']]);
        return (int) $this->pdo->lastInsertId();
    }

    /** Adiciona peça à OS e desconta estoque. */
    public function adicionarPeca(int $idOs, int $idPeca, int $qtd, float $preco): void
    {
        $this->pdo->prepare(
            "INSERT INTO ITEM_OS (id_os,id_peca,quantidade,preco_unit) VALUES (?,?,?,?)"
        )->execute([$idOs, $idPeca, $qtd, $preco]);

        $this->pdo->prepare(
            "UPDATE PECA SET estoque_atual=estoque_atual-? WHERE id_peca=?"
        )->execute([$qtd, $idPeca]);
    }

    /** Atualiza status da OS. */
    public function atualizarStatus(int $id, string $status): void
    {
        if (!in_array($status, self::STATUSES, true)) return;
        $this->pdo->prepare("UPDATE ORDEM_SERVICO SET status=? WHERE id_os=?")->execute([$status, $id]);
    }

    /** Busca um item específico da OS, validando que pertence a ela. */
    public function buscarItem(int $idOs, int $idItem): array|false
    {
        $st = $this->pdo->prepare("SELECT * FROM ITEM_OS WHERE id_item=? AND id_os=?");
        $st->execute([$idItem, $idOs]);
        return $st->fetch();
    }

    /** Remove um item da OS e devolve a quantidade utilizada ao estoque da peça. */
    public function removerItem(int $idOs, int $idItem): bool
    {
        $item = $this->buscarItem($idOs, $idItem);
        if (!$item) return false;

        $this->pdo->prepare("DELETE FROM ITEM_OS WHERE id_item=?")->execute([$idItem]);
        $this->pdo->prepare(
            "UPDATE PECA SET estoque_atual=estoque_atual+? WHERE id_peca=?"
        )->execute([$item['quantidade'], $item['id_peca']]);
        return true;
    }

    /** Finaliza a OS: grava o valor total apurado e a data de fechamento. */
    public function finalizar(int $id, float $valorTotal): void
    {
        $this->pdo->prepare(
            "UPDATE ORDEM_SERVICO SET status='Finalizada', data_fechamento=CURDATE(), valor_total=? WHERE id_os=?"
        )->execute([$valorTotal, $id]);
    }

    /** Contagens para o dashboard. */
    public function totalAbertaS(): int
    {
        return (int) $this->pdo->query(
            "SELECT COUNT(*) FROM ORDEM_SERVICO WHERE status NOT IN ('Finalizada','Cancelada')"
        )->fetchColumn();
    }

    /** Faturamento do mês atual. */
    public function faturamentoMes(): float
    {
        return (float) $this->pdo->query(
            "SELECT COALESCE(SUM(valor_total),0) FROM ORDEM_SERVICO
             WHERE status='Finalizada'
               AND MONTH(data_fechamento)=MONTH(CURDATE())
               AND YEAR(data_fechamento)=YEAR(CURDATE())"
        )->fetchColumn();
    }

    /** Últimas N OS para o dashboard. */
    public function ultimas(int $limite = 5): array
    {
        return $this->pdo->query("
            SELECT os.id_os, os.status, os.data_abertura, c.nome AS cliente, v.placa
            FROM ORDEM_SERVICO os
            JOIN CLIENTE c ON c.id_cliente=os.id_cliente
            JOIN VEICULO v ON v.id_veiculo=os.id_veiculo
            ORDER BY os.id_os DESC LIMIT $limite
        ")->fetchAll();
    }
}
