<?php
declare(strict_types=1);

/**
 * Model Peca
 *
 * Encapsula regras de negócio e acesso à tabela PECA.
 * Princípio SRP: responsável exclusivamente pela entidade Peça/Estoque.
 */
class Peca
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Lista peças ativas com filtros opcionais. */
    public function listar(string $q = '', bool $somenteAlerta = false): array
    {
        $where  = "WHERE ativo=1";
        $params = [];
        if ($q) {
            $where  .= " AND (codigo LIKE ? OR descricao LIKE ? OR fornecedor LIKE ?)";
            $params  = ["%$q%", "%$q%", "%$q%"];
        }
        if ($somenteAlerta) {
            $where .= " AND estoque_atual <= estoque_minimo";
        }
        $stmt = $this->pdo->prepare("SELECT * FROM PECA $where ORDER BY descricao");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Lista peças disponíveis para adição em OS (estoque > 0). */
    public function listarDisponíveis(): array
    {
        return $this->pdo->query(
            "SELECT id_peca, codigo, descricao, estoque_atual, preco_venda
             FROM PECA WHERE ativo=1 AND estoque_atual > 0 ORDER BY descricao"
        )->fetchAll();
    }

    /** Busca peça pelo ID. */
    public function buscarPorId(int $id): array|false
    {
        $st = $this->pdo->prepare("SELECT * FROM PECA WHERE id_peca=?");
        $st->execute([$id]);
        return $st->fetch();
    }

    /** Verifica duplicidade de código. */
    public function codigoDuplicado(string $codigo, int $ignorarId = 0): bool
    {
        $dup = $this->pdo->prepare("SELECT id_peca FROM PECA WHERE codigo=? AND id_peca<>?");
        $dup->execute([$codigo, $ignorarId]);
        return (bool) $dup->fetch();
    }

    /** Verifica se a peça está sendo usada em alguma OS. */
    public function emUso(int $id): bool
    {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM ITEM_OS WHERE id_peca=?");
        $st->execute([$id]);
        return $st->fetchColumn() > 0;
    }

    /** Insere nova peça. */
    public function inserir(array $d): void
    {
        $this->pdo->prepare(
            "INSERT INTO PECA (codigo,descricao,preco_custo,preco_venda,estoque_atual,estoque_minimo,fornecedor)
             VALUES (?,?,?,?,?,?,?)"
        )->execute([
            $d['codigo'], $d['descricao'], $d['preco_custo'],
            $d['preco_venda'], $d['estoque_atual'], $d['estoque_minimo'], $d['fornecedor'],
        ]);
    }

    /** Atualiza peça existente. */
    public function atualizar(int $id, array $d): void
    {
        $this->pdo->prepare(
            "UPDATE PECA SET codigo=?,descricao=?,preco_custo=?,preco_venda=?,estoque_atual=?,estoque_minimo=?,fornecedor=?
             WHERE id_peca=?"
        )->execute([
            $d['codigo'], $d['descricao'], $d['preco_custo'],
            $d['preco_venda'], $d['estoque_atual'], $d['estoque_minimo'], $d['fornecedor'], $id,
        ]);
    }

    /** Desconta estoque após uso em OS. */
    public function descontarEstoque(int $id, int $qtd): void
    {
        $this->pdo->prepare("UPDATE PECA SET estoque_atual=estoque_atual-? WHERE id_peca=?")->execute([$qtd, $id]);
    }

    /** Desativa (soft-delete) uma peça. */
    public function desativar(int $id): void
    {
        $this->pdo->prepare("UPDATE PECA SET ativo=0 WHERE id_peca=?")->execute([$id]);
    }

    /** Conta quantas peças com estoque crítico. */
    public function totalAlertas(): int
    {
        return (int) $this->pdo->query(
            "SELECT COUNT(*) FROM PECA WHERE ativo=1 AND estoque_atual <= estoque_minimo"
        )->fetchColumn();
    }
}
