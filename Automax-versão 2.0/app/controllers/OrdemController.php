<?php
declare(strict_types=1);

/**
 * OrdemController
 *
 * Coordena as requisições de Ordens de Serviço: listagem,
 * criação, detalhe e ações sobre a OS.
 */
class OrdemController
{
    private PDO          $pdo;
    private OrdemServico $model;

    public function __construct(PDO $pdo)
    {
        $this->pdo   = $pdo;
        $this->model = new OrdemServico($pdo);
    }

    public function handle(): void
    {
        $modo = $_GET['modo'] ?? 'lista';

        match ($modo) {
            'nova'    => $this->nova(),
            'detalhe' => $this->detalhe((int)($_GET['id'] ?? 0)),
            default   => $this->lista(),
        };
    }

    // ── Listagem ───────────────────────────────────────────
    private function lista(): void
    {
        $q      = trim($_GET['q'] ?? '');
        $status = $_GET['status'] ?? '';
        $lista  = $this->model->listar($q, $status);
        $statuses = OrdemServico::STATUSES;
        require ROOT_DIR . '/app/views/ordens/lista.php';
    }

    // ── Nova OS ────────────────────────────────────────────
    private function nova(): void
    {
        $clientes = $this->pdo->query("SELECT id_cliente, nome FROM CLIENTE WHERE ativo=1 ORDER BY nome")->fetchAll();
        $veiculos = $this->pdo->query(
            "SELECT v.id_veiculo, v.placa, v.modelo, v.marca, v.id_cliente FROM VEICULO v WHERE v.ativo=1 ORDER BY v.placa"
        )->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idCliente  = (int)$_POST['id_cliente'];
            $idVeiculo  = (int)$_POST['id_veiculo'];
            $diagnostico = trim($_POST['diagnostico'] ?? '');
            $prazo      = $_POST['prazo_previsto'] ?? null;

            if (!$idCliente || !$idVeiculo || !$diagnostico) {
                flash('Preencha todos os campos obrigatórios.', 'danger');
            } else {
                $novoId = $this->model->abrir([
                    'diagnostico' => $diagnostico,
                    'prazo'       => $prazo,
                    'id_cliente'  => $idCliente,
                    'id_veiculo'  => $idVeiculo,
                    'id_usuario'  => usuario()['id'],
                ]);
                flash('Ordem de serviço #' . $novoId . ' criada!', 'success');
                header('Location: ' . BASE_URL . 'ordens.php?modo=detalhe&id=' . $novoId); exit;
            }
        }

        require ROOT_DIR . '/app/views/ordens/nova.php';
    }

    // ── Detalhe e ações ────────────────────────────────────
    private function detalhe(int $idOs): void
    {
        $os = $this->model->buscarPorId($idOs);
        if (!$os) {
            flash('OS não encontrada.', 'danger');
            header('Location: ' . BASE_URL . 'ordens.php'); exit;
        }

        $itens     = $this->model->buscarItens($idOs);
        $totalItens = array_sum(array_map(fn($i) => $i['quantidade'] * $i['preco_unit'], $itens));
        $pecasDisp  = (new Peca($this->pdo))->listarDisponíveis();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $acao = $_POST['acao'] ?? '';

            if ($acao === 'adicionar_peca' && $os['status'] !== 'Finalizada') {
                $idPeca = (int)$_POST['id_peca'];
                $qtd    = max(1, (int)$_POST['quantidade']);
                $p      = (new Peca($this->pdo))->buscarPorId($idPeca);

                if (!$p || $p['estoque_atual'] < $qtd) {
                    flash('Estoque insuficiente.', 'danger');
                } else {
                    $this->model->adicionarPeca($idOs, $idPeca, $qtd, (float)$p['preco_venda']);
                    flash('Peça adicionada.', 'success');
                }
                header('Location: ' . BASE_URL . 'ordens.php?modo=detalhe&id=' . $idOs); exit;
            }

            if ($acao === 'remover_item') {
                // ⚠️ FASE DE TESTES — remoção de itens desabilitada temporariamente
                flash('Funcionalidade em desenvolvimento. Disponível na versão final.', 'warning');
                header('Location: ' . BASE_URL . 'ordens.php?modo=detalhe&id=' . $idOs); exit;
            }

            if ($acao === 'atualizar_status') {
                $novoStatus = $_POST['status'] ?? '';
                $this->model->atualizarStatus($idOs, $novoStatus);
                flash("Status atualizado para: $novoStatus", 'success');
                header('Location: ' . BASE_URL . 'ordens.php?modo=detalhe&id=' . $idOs); exit;
            }

            if ($acao === 'finalizar') {
                // ⚠️ FASE DE TESTES — finalização desabilitada temporariamente
                flash('Finalização de OS em desenvolvimento. Disponível na versão final.', 'warning');
                header('Location: ' . BASE_URL . 'ordens.php?modo=detalhe&id=' . $idOs); exit;
            }
        }

        $combustivelLabels = [
            'gasolina' => 'Gasolina', 'etanol' => 'Etanol', 'flex' => 'Flex',
            'diesel'   => 'Diesel',   'gnv'    => 'GNV',    'eletrico' => 'Elétrico',
        ];

        require ROOT_DIR . '/app/views/ordens/detalhe.php';
    }
}
