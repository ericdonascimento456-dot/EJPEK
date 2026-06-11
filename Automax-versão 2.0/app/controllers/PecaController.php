<?php
declare(strict_types=1);

/**
 * PecaController
 *
 * Coordena as requisições de CRUD de peças e controle de estoque.
 */
class PecaController
{
    private PDO   $pdo;
    private Peca  $model;

    public function __construct(PDO $pdo)
    {
        $this->pdo   = $pdo;
        $this->model = new Peca($pdo);
    }

    public function handle(): void
    {
        $modo = $_GET['modo'] ?? 'lista';
        $id   = (int)($_GET['id'] ?? 0);

        match ($modo) {
            'excluir'        => $this->excluir($id),
            'novo', 'editar' => $this->formulario($modo, $id),
            default          => $this->lista(),
        };
    }

    // ── Listagem ───────────────────────────────────────────
    private function lista(): void
    {
        $q      = trim($_GET['q'] ?? '');
        $alerta = isset($_GET['alerta']);
        $lista  = $this->model->listar($q, $alerta);
        require ROOT_DIR . '/app/views/pecas/lista.php';
    }

    // ── Formulário ─────────────────────────────────────────
    private function formulario(string $modo, int $id): void
    {
        $peca = null;
        if ($modo === 'editar' && $id) {
            $peca = $this->model->buscarPorId($id);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idPost    = (int)($_POST['id'] ?? 0);
            $codigo    = trim($_POST['codigo'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $custo     = (float)str_replace(',', '.', $_POST['preco_custo'] ?? '0');
            $venda     = (float)str_replace(',', '.', $_POST['preco_venda'] ?? '0');
            $estoque   = (int)($_POST['estoque_atual'] ?? 0);
            $minimo    = (int)($_POST['estoque_minimo'] ?? 5);
            $forn      = trim($_POST['fornecedor'] ?? '');

            $erros = [];
            if (!$codigo)    $erros[] = 'Código obrigatório.';
            if (!$descricao) $erros[] = 'Descrição obrigatória.';
            if ($this->model->codigoDuplicado($codigo, $idPost)) $erros[] = 'Código já cadastrado.';

            if ($erros) {
                foreach ($erros as $e) flash($e, 'danger');
                $peca = [
                    'id_peca'        => $idPost,
                    'codigo'         => $codigo,
                    'descricao'      => $descricao,
                    'preco_custo'    => $custo,
                    'preco_venda'    => $venda,
                    'estoque_atual'  => $estoque,
                    'estoque_minimo' => $minimo,
                    'fornecedor'     => $forn,
                ];
                $modo = $idPost ? 'editar' : 'novo';
            } else {
                $dados = [
                    'codigo'         => $codigo,
                    'descricao'      => $descricao,
                    'preco_custo'    => $custo,
                    'preco_venda'    => $venda,
                    'estoque_atual'  => $estoque,
                    'estoque_minimo' => $minimo,
                    'fornecedor'     => $forn,
                ];
                if ($idPost > 0) {
                    $this->model->atualizar($idPost, $dados);
                    flash('Peça atualizada!', 'success');
                } else {
                    $this->model->inserir($dados);
                    flash('Peça cadastrada!', 'success');
                }
                header('Location: ' . BASE_URL . 'pecas.php'); exit;
            }
        }

        require ROOT_DIR . '/app/views/pecas/form.php';
    }

    // ── Exclusão ───────────────────────────────────────────
    private function excluir(int $id): void
    {
        if (!$id || !ehGerente()) {
            header('Location: ' . BASE_URL . 'pecas.php'); exit;
        }
        if ($this->model->emUso($id)) {
            flash('Não é possível excluir: peça usada em OS.', 'danger');
        } else {
            $this->model->desativar($id);
            flash('Peça removida.', 'success');
        }
        header('Location: ' . BASE_URL . 'pecas.php'); exit;
    }
}
