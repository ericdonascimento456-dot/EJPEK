<?php
declare(strict_types=1);

/**
 * app/config/Layout.php
 *
 * Fornece renderHeader() e renderFooter() para todas as views.
 * O CSS e JS ficam em public/css/ e public/js/ (carregados via BASE_URL).
 */

function renderHeader(string $titulo = 'AutoMax Oficina'): void
{
    $u       = usuario();
    $nome    = htmlspecialchars($u['nome'] ?? '');
    $perf    = htmlspecialchars($u['perfil'] ?? '');
    $flashes = getFlash();

    $atual = basename($_SERVER['PHP_SELF']);
    $nav = [
        'index.php'    => ['label' => 'Dashboard',          'icon' => 'speedometer2'],
        'clientes.php' => ['label' => 'Clientes',            'icon' => 'people-fill'],
        'veiculos.php' => ['label' => 'Ve&#237;culos',       'icon' => 'car-front-fill'],
        'pecas.php'    => ['label' => 'Pe&#231;as',          'icon' => 'gear-fill'],
        'ordens.php'   => ['label' => 'Ordens de Servi&#231;o', 'icon' => 'clipboard2-check-fill'],
    ];

    $cssUrl = BASE_URL . 'css/estilo.css';

    echo "<!DOCTYPE html>\n<html lang=\"pt-BR\">\n<head>\n";
    echo "<meta charset=\"UTF-8\">\n";
    echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";
    echo "<title>{$titulo} &mdash; AutoMax</title>\n";
    echo "<link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\">\n";
    echo "<link href=\"https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css\" rel=\"stylesheet\">\n";
    echo "<link href=\"https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&family=JetBrains+Mono:wght@400;600&display=swap\" rel=\"stylesheet\">\n";
    echo "<link rel=\"stylesheet\" href=\"{$cssUrl}\">\n";
    echo "</head>\n<body>\n";

    // Sidebar
    echo '<div class="sidebar" id="sidebar">';
    echo '<div class="sidebar-brand">'
       . '<div class="logo-text">&#9881; AutoMax</div>'
       . '<div class="logo-sub">Oficina Mec&#226;nica</div>'
       . '</div>';
    echo '<nav class="sidebar-nav">';
    foreach ($nav as $file => $item) {
        $isActive = ($atual === $file) ? 'active' : '';
        echo "<a class='nav-item-link {$isActive}' href='" . BASE_URL . "{$file}'>"
           . "<i class='bi bi-{$item['icon']}'></i> {$item['label']}</a>";
    }
    echo '</nav>';

    $inicial = strtoupper(mb_substr($u['nome'] ?? 'U', 0, 1));
    echo '<div class="sidebar-footer">';
    echo "<div class=\"user-chip\">"
       . "<div class=\"user-avatar\">{$inicial}</div>"
       . "<div><div class=\"user-name\">{$nome}</div><div class=\"user-role\">{$perf}</div></div>"
       . "</div>";
    echo "<a href=\"" . BASE_URL . "logout.php\" class=\"btn-logout\">"
       . "<i class='bi bi-box-arrow-right'></i> Sair</a>";
    echo '</div>'; // sidebar-footer
    echo '</div>'; // sidebar

    echo '<button class="hamburger" onclick="document.getElementById(\'sidebar\').classList.toggle(\'open\')">'
       . '<span></span><span></span><span></span></button>';
    echo '<div class="main-wrap">';

    // Flash messages
    foreach ($flashes as $f) {
        $tipo = match($f['tipo']) {
            'success' => 'am-alert-success',
            'danger'  => 'am-alert-danger',
            default   => 'am-alert-warning'
        };
        $icon = match($f['tipo']) {
            'success' => 'check-circle-fill',
            'danger'  => 'x-circle-fill',
            default   => 'exclamation-triangle-fill'
        };
        $msg = htmlspecialchars($f['msg']);
        echo "<div class='am-alert {$tipo}'><i class='bi bi-{$icon}'></i> {$msg}</div>";
    }
}

function renderFooter(): void
{
    $jsUrl = BASE_URL . 'js/dashboard.js';
    echo "</div><!-- main-wrap -->\n";
    echo "<script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js\"></script>\n";
    echo "<script src=\"{$jsUrl}\"></script>\n";
    echo "</body></html>\n";
}
