<?php
declare(strict_types=1);

/**
 * Model Relatorio
 *
 * Encapsula todas as queries analíticas do sistema AutoMax.
 * Princípio SRP: responsável exclusivamente pela agregação de dados
 * para fins de relatório — nenhuma escrita no banco é feita aqui.
 */
class Relatorio
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ── Faturamento por período ──────────────────────────────────────

    /**
     * Retorna faturamento diário agrupado no intervalo informado.
     */
    public function faturamentoPorPeriodo(string $de, string $ate): array
    {
        $stmt = $this->pdo->prepare("
            SELECT DATE_FORMAT(data_fechamento, '%d/%m/%Y') AS dia,
                   COUNT(*)                                  AS total_os,
                   SUM(valor_total)                          AS faturamento
            FROM ORDEM_SERVICO
            WHERE status = 'Finalizada'
              AND data_fechamento BETWEEN ? AND ?
            GROUP BY data_fechamento
            ORDER BY data_fechamento
        ");
        $stmt->execute([$de, $ate]);
        return $stmt->fetchAll();
    }

    /**
     * Totais consolidados do período: número de OS e faturamento.
     */
    public function totaisPeriodo(string $de, string $ate): array
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) AS total_os,
                   COALESCE(SUM(valor_total), 0) AS faturamento
            FROM ORDEM_SERVICO
            WHERE status = 'Finalizada'
              AND data_fechamento BETWEEN ? AND ?
        ");
        $stmt->execute([$de, $ate]);
        return $stmt->fetch();
    }

    // ── Peças mais utilizadas ────────────────────────────────────────

    /**
     * Ranking das peças mais consumidas em OS finalizadas.
     */
    public function pecasMaisUsadas(string $de, string $ate, int $limite = 10): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.codigo,
                   p.descricao,
                   SUM(i.quantidade)                        AS total_usado,
                   SUM(i.quantidade * i.preco_unit)         AS receita_gerada
            FROM ITEM_OS i
            JOIN PECA p           ON p.id_peca  = i.id_peca
            JOIN ORDEM_SERVICO os ON os.id_os   = i.id_os
            WHERE os.status = 'Finalizada'
              AND os.data_fechamento BETWEEN ? AND ?
            GROUP BY p.id_peca, p.codigo, p.descricao
            ORDER BY total_usado DESC
            LIMIT $limite
        ");
        $stmt->execute([$de, $ate]);
        return $stmt->fetchAll();
    }

    // ── OS por status ────────────────────────────────────────────────

    /**
     * Contagem de OS agrupada por status (sem filtro de data).
     */
    public function osPorStatus(): array
    {
        return $this->pdo->query("
            SELECT status, COUNT(*) AS total
            FROM ORDEM_SERVICO
            GROUP BY status
            ORDER BY total DESC
        ")->fetchAll();
    }

    // ── Clientes com mais OS ─────────────────────────────────────────

    /**
     * Ranking de clientes com maior número de ordens de serviço.
     */
    public function clientesMaisAtivos(string $de, string $ate, int $limite = 10): array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.nome AS cliente,
                   c.celular,
                   COUNT(os.id_os)                       AS total_os,
                   COALESCE(SUM(os.valor_total), 0)      AS valor_total
            FROM ORDEM_SERVICO os
            JOIN CLIENTE c ON c.id_cliente = os.id_cliente
            WHERE os.data_abertura BETWEEN ? AND ?
            GROUP BY c.id_cliente, c.nome, c.celular
            ORDER BY total_os DESC
            LIMIT $limite
        ");
        $stmt->execute([$de, $ate]);
        return $stmt->fetchAll();
    }

    // ── Estoque crítico ──────────────────────────────────────────────

    /**
     * Peças com estoque abaixo ou igual ao mínimo configurado.
     */
    public function estoqueCritico(): array
    {
        return $this->pdo->query("
            SELECT codigo, descricao, estoque_atual, estoque_minimo, fornecedor,
                   (estoque_minimo - estoque_atual) AS defasagem
            FROM PECA
            WHERE estoque_atual <= estoque_minimo
              AND ativo = 1
            ORDER BY defasagem DESC
        ")->fetchAll();
    }
}
