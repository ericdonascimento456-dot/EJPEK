<?php
declare(strict_types=1);

/**
 * Model Veiculo
 *
 * Encapsula regras de negócio e acesso à tabela VEICULO.
 * Princípio SRP: responsável exclusivamente pela entidade Veículo.
 */
class Veiculo
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Lista veículos ativos com nome do cliente, filtro opcional. */
    public function listar(string $q = ''): array
    {
        $where  = "WHERE v.ativo=1";
        $params = [];
        if ($q) {
            $where  .= " AND (v.placa LIKE ? OR v.modelo LIKE ? OR v.marca LIKE ? OR c.nome LIKE ?)";
            $params  = ["%$q%", "%$q%", "%$q%", "%$q%"];
        }
        $stmt = $this->pdo->prepare(
            "SELECT v.*, c.nome AS cliente_nome
             FROM VEICULO v
             JOIN CLIENTE c ON c.id_cliente=v.id_cliente
             $where ORDER BY v.placa"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Lista todos os veículos ativos (para selects). */
    public function listarTodos(): array
    {
        return $this->pdo->query(
            "SELECT v.id_veiculo, v.placa, v.modelo, v.marca, v.id_cliente
             FROM VEICULO v WHERE v.ativo=1 ORDER BY v.placa"
        )->fetchAll();
    }

    /** Busca veículo pelo ID. */
    public function buscarPorId(int $id): array|false
    {
        $st = $this->pdo->prepare("SELECT * FROM VEICULO WHERE id_veiculo=?");
        $st->execute([$id]);
        return $st->fetch();
    }

    /** Verifica duplicidade de placa. */
    public function placaDuplicada(string $placa, int $ignorarId = 0): bool
    {
        $dup = $this->pdo->prepare("SELECT id_veiculo FROM VEICULO WHERE placa=? AND id_veiculo<>?");
        $dup->execute([$placa, $ignorarId]);
        return (bool) $dup->fetch();
    }

    /** Verifica se o veículo possui OS vinculadas. */
    public function possuiOs(int $id): bool
    {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM ORDEM_SERVICO WHERE id_veiculo=?");
        $st->execute([$id]);
        return $st->fetchColumn() > 0;
    }

    /** Insere novo veículo. */
    public function inserir(array $d): void
    {
        $this->pdo->prepare(
            "INSERT INTO VEICULO (placa,modelo,marca,ano,cor,km_atual,renavam,chassi,combustivel,foto,id_cliente)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)"
        )->execute([
            $d['placa'], $d['modelo'], $d['marca'], $d['ano'],
            $d['cor'], $d['km_atual'], $d['renavam'], $d['chassi'],
            $d['combustivel'], $d['foto'], $d['id_cliente'],
        ]);
    }

    /** Atualiza veículo existente. */
    public function atualizar(int $id, array $d): void
    {
        $sql    = "UPDATE VEICULO SET placa=?,modelo=?,marca=?,ano=?,cor=?,km_atual=?,renavam=?,chassi=?,combustivel=?,id_cliente=?";
        $params = [
            $d['placa'], $d['modelo'], $d['marca'], $d['ano'],
            $d['cor'], $d['km_atual'], $d['renavam'], $d['chassi'],
            $d['combustivel'], $d['id_cliente'],
        ];
        if (!empty($d['foto'])) {
            $sql    .= ",foto=?";
            $params[] = $d['foto'];
        }
        $sql    .= " WHERE id_veiculo=?";
        $params[] = $id;
        $this->pdo->prepare($sql)->execute($params);
    }

    /** Desativa (soft-delete) um veículo. */
    public function desativar(int $id): void
    {
        $this->pdo->prepare("UPDATE VEICULO SET ativo=0 WHERE id_veiculo=?")->execute([$id]);
    }
}
