<?php
declare(strict_types=1);

/**
 * Model Cliente
 *
 * Encapsula todas as regras de negócio e comunicação com a tabela CLIENTE.
 * Princípio SRP: responsável exclusivamente pela entidade Cliente.
 */
class Cliente
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Lista clientes ativos, com filtro opcional por nome/CPF. */
    public function listar(string $q = ''): array
    {
        $where  = "WHERE ativo=1";
        $params = [];
        if ($q) {
            $where   .= " AND (nome LIKE ? OR cpf LIKE ?)";
            $params   = ["%$q%", "%$q%"];
        }
        $stmt = $this->pdo->prepare("SELECT * FROM CLIENTE $where ORDER BY nome");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Busca um cliente pelo ID. */
    public function buscarPorId(int $id): array|false
    {
        $st = $this->pdo->prepare("SELECT * FROM CLIENTE WHERE id_cliente=?");
        $st->execute([$id]);
        return $st->fetch();
    }

    /** Verifica duplicidade de CPF (excluindo o próprio registro na edição). */
    public function cpfDuplicado(string $cpf, int $ignorarId = 0): bool
    {
        $dup = $this->pdo->prepare("SELECT id_cliente FROM CLIENTE WHERE cpf=? AND id_cliente<>?");
        $dup->execute([$cpf, $ignorarId]);
        return (bool) $dup->fetch();
    }

    /** Verifica se o cliente possui OS ou veículos vinculados. */
    public function possuiVinculos(int $id): bool
    {
        $em_uso = $this->pdo->prepare(
            "SELECT COUNT(*) FROM ORDEM_SERVICO WHERE id_cliente=?
             UNION ALL
             SELECT COUNT(*) FROM VEICULO WHERE id_cliente=?"
        );
        $em_uso->execute([$id, $id]);
        $total = array_sum(array_column($em_uso->fetchAll(PDO::FETCH_COLUMN), 0));
        return $total > 0;
    }

    /** Insere um novo cliente. */
    public function inserir(array $dados): void
    {
        $this->pdo->prepare(
            "INSERT INTO CLIENTE (nome,cpf,telefone,celular,email,endereco) VALUES (?,?,?,?,?,?)"
        )->execute([
            $dados['nome'], $dados['cpf'], $dados['telefone'],
            $dados['celular'], $dados['email'], $dados['endereco'],
        ]);
    }

    /** Atualiza um cliente existente. */
    public function atualizar(int $id, array $dados): void
    {
        $this->pdo->prepare(
            "UPDATE CLIENTE SET nome=?,cpf=?,telefone=?,celular=?,email=?,endereco=? WHERE id_cliente=?"
        )->execute([
            $dados['nome'], $dados['cpf'], $dados['telefone'],
            $dados['celular'], $dados['email'], $dados['endereco'], $id,
        ]);
    }

    /** Desativa (soft-delete) um cliente. */
    public function desativar(int $id): void
    {
        $this->pdo->prepare("UPDATE CLIENTE SET ativo=0 WHERE id_cliente=?")->execute([$id]);
    }
}
