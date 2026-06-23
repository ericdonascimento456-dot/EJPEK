<?php
declare(strict_types=1);

/**
 * RelatorioController
 *
 * Página gerencial única: KPIs do mês, gráficos de faturamento e OS
 * nos últimos 12 meses, donut de status e ranking de peças.
 * Acesso restrito a Gerentes.
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
        if (!ehGerente()) {
            flash('Acesso restrito a Gerentes.', 'danger');
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }

        $historico    = $this->model->historico12Meses();
        $mes_atual    = $this->model->kpisMesAtual();
        $top_pecas    = $this->model->topPecas();
        $status       = $this->model->osPorStatus();

        // Variáveis disponibilizadas para a view
        $meses_completos = $historico['meses'];
        $labels          = $historico['labels'];
        $fat_data        = $historico['fat_data'];
        $os_data         = $historico['os_data'];
        $status_labels   = $status['labels'];
        $status_data     = $status['data'];

        require ROOT_DIR . '/app/views/relatorios/index.php';
    }
}
