<?php
declare(strict_types=1);

/**
 * RelatorioController
 *
 * Coordena as requisições da seção de Relatórios.
 * Apenas Gerentes podem acessar esta área (verificação feita aqui
 * e reforçada no entry point público).
 *
 * Modos disponíveis (GET ?modo=):
 *   index      → painel geral de relatórios
 *   faturamento → faturamento por período
 *   pecas       → peças mais utilizadas
 *   clientes    → clientes mais ativos
 *   estoque     → estoque crítico
 */
class RelatorioController
{
    private PDO       $pdo;
    private Relatorio $model;

    public function __construct(PDO $pdo)
    {
        $this->pdo   = $pdo;
        $this->model = new Relatorio($pdo);
    }

    public function handle(): void
    {
        // Acesso restrito a Gerentes
        if (!ehGerente()) {
            flash('Acesso restrito à área de Relatórios.', 'danger');
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        $modo = $_GET['modo'] ?? 'index';

        match ($modo) {
            'faturamento' => $this->faturamento(),
            'pecas'       => $this->pecas(),
            'clientes'    => $this->clientes(),
            'estoque'     => $this->estoque(),
            default       => $this->index(),
        };
    }

    // ── Painel inicial ─────────────────────────────────────────────

    private function index(): void
    {
        // Resumo rápido para o painel de relatórios
        $osPorStatus   = $this->model->osPorStatus();
        $estoqueCritico = $this->model->estoqueCritico();
        require ROOT_DIR . '/app/views/relatorios/index.php';
    }

    // ── Faturamento por período ────────────────────────────────────

    private function faturamento(): void
    {
        $de  = $_GET['de']  ?? date('Y-m-01');          // primeiro dia do mês atual
        $ate = $_GET['ate'] ?? date('Y-m-d');            // hoje

        $linhas = $this->model->faturamentoPorPeriodo($de, $ate);
        $totais = $this->model->totaisPeriodo($de, $ate);

        require ROOT_DIR . '/app/views/relatorios/faturamento.php';
    }

    // ── Peças mais utilizadas ──────────────────────────────────────

    private function pecas(): void
    {
        $de  = $_GET['de']  ?? date('Y-m-01');
        $ate = $_GET['ate'] ?? date('Y-m-d');

        $lista = $this->model->pecasMaisUsadas($de, $ate);

        require ROOT_DIR . '/app/views/relatorios/pecas.php';
    }

    // ── Clientes mais ativos ───────────────────────────────────────

    private function clientes(): void
    {
        $de  = $_GET['de']  ?? date('Y-m-01');
        $ate = $_GET['ate'] ?? date('Y-m-d');

        $lista = $this->model->clientesMaisAtivos($de, $ate);

        require ROOT_DIR . '/app/views/relatorios/clientes.php';
    }

    // ── Estoque crítico ────────────────────────────────────────────

    private function estoque(): void
    {
        $lista = $this->model->estoqueCritico();
        require ROOT_DIR . '/app/views/relatorios/estoque.php';
    }
}
