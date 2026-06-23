<?php
declare(strict_types=1);

/**
 * DashboardController
 *
 * Responsável por coordenar os dados de resumo exibidos na tela inicial.
 * Princípio SRP: coordena o fluxo de requisição apenas do Dashboard.
 */
class DashboardController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Ação principal: coleta KPIs e últimas OS, renderiza a view. */
    public function index(): void
    {
        $os      = new OrdemServico($this->pdo);
        $peca    = new Peca($this->pdo);

        $clientes   = (int) $this->pdo->query("SELECT COUNT(*) FROM CLIENTE WHERE ativo=1")->fetchColumn();
        $osAbertas  = $os->totalAbertaS();
        $alertas    = $peca->totalAlertas();
        $fatMes     = $os->faturamentoMes();
        $ultimas    = $os->ultimas(5);

        // Passa dados para a view
        require ROOT_DIR . '/app/views/dashboard/index.php';
    }
}
