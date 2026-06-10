<?php
/**
 * vendor/autoload.php — Autoloader PSR-4
 *
 * Carrega automaticamente todas as classes dos namespaces do projeto
 * seguindo o padrão PSR-4 sem dependência de Composer para este projeto.
 *
 * Mapeamento:
 *   app/config/     → Database, Auth, Layout
 *   app/models/     → Cliente, Veiculo, Peca, OrdemServico
 *   app/controllers/ → DashboardController, ClienteController,
 *                       VeiculoController, PecaController, OrdemController
 */
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    // Mapeia os diretórios de busca (ordem de prioridade)
    $dirs = [
        __DIR__ . '/../app/controllers/',
        __DIR__ . '/../app/models/',
        __DIR__ . '/../app/config/',
    ];

    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
