<?php
declare(strict_types=1);

/**
 * ClienteController
 *
 * Coordena as requisições de CRUD de clientes,
 * delegando a lógica de negócio ao Model Cliente.
 * Princípio SRP: gerencia apenas o fluxo da entidade Cliente.
 */
class ClienteController
{
    private PDO     $pdo;
    private Cliente $model;

    public function __construct(PDO $pdo)
    {
        $this->pdo   = $pdo;
        $this->model = new Cliente($pdo);
    }

    /** Ponto de entrada: despacha para a ação correta. */
    public function handle(): void
    {
        $modo = $_GET['modo'] ?? 'lista';
        $id   = (int)($_GET['id'] ?? 0);

        match ($modo) {
            'excluir' => $this->excluir($id),
            'novo',
            'editar',
            'salvar'  => $this->formulario($modo, $id),
            default   => $this->lista(),
        };
    }

    // ── Listagem ───────────────────────────────────────────
    private function lista(): void
    {
        $q      = trim($_GET['q'] ?? '');
        $lista  = $this->model->listar($q);
        require ROOT_DIR . '/app/views/clientes/lista.php';
    }

    // ── Formulário (novo/editar) e save ───────────────────
    private function formulario(string $modo, int $id): void
    {
        $cliente = null;

        if (in_array($modo, ['editar', 'salvar']) && $id) {
            $cliente = $this->model->buscarPorId($id);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idPost   = (int)($_POST['id'] ?? 0);
            $nome     = trim($_POST['nome'] ?? '');
            $cpf      = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
            $telefone = preg_replace('/\D/', '', $_POST['telefone'] ?? '');
            $celular  = preg_replace('/\D/', '', $_POST['celular'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $endereco = trim($_POST['endereco'] ?? '');

            $erros = [];
            if (!$nome) $erros[] = 'Nome obrigatório.';
            if (strlen($cpf) !== 11) $erros[] = 'CPF inválido (informe 11 dígitos).';
            if ($this->model->cpfDuplicado($cpf, $idPost)) $erros[] = 'CPF já cadastrado.';

            if ($erros) {
                foreach ($erros as $e) flash($e, 'danger');
            } else {
                $dados = compact('nome', 'cpf', 'telefone', 'celular', 'email', 'endereco');
                if ($idPost > 0) {
                    $this->model->atualizar($idPost, $dados);
                    flash('Cliente atualizado!', 'success');
                } else {
                    $this->model->inserir($dados);
                    flash('Cliente cadastrado!', 'success');
                }
                header('Location: ' . BASE_URL . 'clientes.php'); exit;
            }

            // Re-popula o form após erro
            $cliente = compact('nome', 'cpf', 'telefone', 'celular', 'email', 'endereco');
            $cliente['id_cliente'] = $idPost;
            $modo = $idPost ? 'editar' : 'novo';
        }

        require ROOT_DIR . '/app/views/clientes/form.php';
    }

    // ── Exclusão (soft-delete) ─────────────────────────────
    private function excluir(int $id): void
    {
        if (!$id || !ehGerente()) {
            header('Location: ' . BASE_URL . 'clientes.php'); exit;
        }
        if ($this->model->possuiVinculos($id)) {
            flash('Não é possível excluir: cliente possui veículos ou OS vinculados.', 'danger');
        } else {
            $this->model->desativar($id);
            flash('Cliente desativado com sucesso.', 'success');
        }
        header('Location: ' . BASE_URL . 'clientes.php'); exit;
    }
}
