<?php
declare(strict_types=1);

/**
 * VeiculoController
 *
 * Coordena as requisições de CRUD de veículos,
 * incluindo upload de foto, delegando lógica ao Model Veiculo.
 */
class VeiculoController
{
    private PDO     $pdo;
    private Veiculo $model;

    public function __construct(PDO $pdo)
    {
        $this->pdo   = $pdo;
        $this->model = new Veiculo($pdo);
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
        $q    = trim($_GET['q'] ?? '');
        $lista = $this->model->listar($q);
        $clientes = $this->pdo->query("SELECT id_cliente, nome FROM CLIENTE WHERE ativo=1 ORDER BY nome")->fetchAll();
        $combustiveis = $this->combustiveis();
        require ROOT_DIR . '/app/views/veiculos/lista.php';
    }

    // ── Formulário ─────────────────────────────────────────
    private function formulario(string $modo, int $id): void
    {
        $clientes    = $this->pdo->query("SELECT id_cliente, nome FROM CLIENTE WHERE ativo=1 ORDER BY nome")->fetchAll();
        $combustiveis = $this->combustiveis();
        $veiculo     = null;

        if ($modo === 'editar' && $id) {
            $veiculo = $this->model->buscarPorId($id);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idPost     = (int)($_POST['id'] ?? 0);
            $placa      = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $_POST['placa'] ?? ''));
            $modelo     = trim($_POST['modelo'] ?? '');
            $marca      = trim($_POST['marca'] ?? '');
            $ano        = (int)($_POST['ano'] ?? 0);
            $cor        = trim($_POST['cor'] ?? '');
            $km         = (float)str_replace(',', '.', $_POST['km_atual'] ?? '0');
            $renavam    = preg_replace('/\D/', '', $_POST['renavam'] ?? '');
            $chassi     = strtoupper(trim($_POST['chassi'] ?? ''));
            $combustivel = $_POST['combustivel'] ?? 'flex';
            $idCliente  = (int)($_POST['id_cliente'] ?? 0);

            $erros = [];
            if (!$placa)      $erros[] = 'Placa obrigatória.';
            if (!$modelo)     $erros[] = 'Modelo obrigatório.';
            if (!$marca)      $erros[] = 'Marca obrigatória.';
            if ($ano < 1900 || $ano > 2100) $erros[] = 'Ano inválido.';
            if (!$idCliente)  $erros[] = 'Selecione um cliente.';
            if ($this->model->placaDuplicada($placa, $idPost)) $erros[] = 'Placa já cadastrada.';

            // Upload de foto
            $fotoPath = null;
            if (!empty($_FILES['foto']['name'])) {
                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $erros[] = 'Foto: apenas JPG, PNG ou WebP.';
                } elseif ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
                    $erros[] = 'Foto: máximo 5MB.';
                } else {
                    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
                    $nomeArquivo = uniqid('vei_') . '.' . $ext;
                    move_uploaded_file($_FILES['foto']['tmp_name'], UPLOAD_DIR . $nomeArquivo);
                    $fotoPath = $nomeArquivo;
                }
            }

            if ($erros) {
                foreach ($erros as $e) flash($e, 'danger');
                $veiculo = compact('placa', 'modelo', 'marca', 'ano', 'cor', 'renavam', 'chassi', 'combustivel', 'idCliente');
                $veiculo['id_veiculo'] = $idPost;
                $veiculo['km_atual']   = $km;
                $veiculo['id_cliente'] = $idCliente;
                $modo = $idPost ? 'editar' : 'novo';
            } else {
                $dados = [
                    'placa'       => $placa,
                    'modelo'      => $modelo,
                    'marca'       => $marca,
                    'ano'         => $ano,
                    'cor'         => $cor,
                    'km_atual'    => $km,
                    'renavam'     => $renavam ?: null,
                    'chassi'      => $chassi ?: null,
                    'combustivel' => $combustivel,
                    'foto'        => $fotoPath,
                    'id_cliente'  => $idCliente,
                ];
                if ($idPost > 0) {
                    $this->model->atualizar($idPost, $dados);
                    flash('Veículo atualizado!', 'success');
                } else {
                    $this->model->inserir($dados);
                    flash('Veículo cadastrado!', 'success');
                }
                header('Location: ' . BASE_URL . 'veiculos.php'); exit;
            }
        }

        require ROOT_DIR . '/app/views/veiculos/form.php';
    }

    // ── Exclusão ───────────────────────────────────────────
    private function excluir(int $id): void
    {
        if (!$id || !ehGerente()) {
            header('Location: ' . BASE_URL . 'veiculos.php'); exit;
        }
        if ($this->model->possuiOs($id)) {
            flash('Não é possível excluir: veículo possui OS vinculadas.', 'danger');
        } else {
            $this->model->desativar($id);
            flash('Veículo removido.', 'success');
        }
        header('Location: ' . BASE_URL . 'veiculos.php'); exit;
    }

    public function combustiveis(): array
    {
        return [
            'gasolina' => 'Gasolina',
            'etanol'   => 'Etanol',
            'flex'     => 'Flex',
            'diesel'   => 'Diesel',
            'gnv'      => 'GNV',
            'eletrico' => 'Elétrico',
        ];
    }
}
