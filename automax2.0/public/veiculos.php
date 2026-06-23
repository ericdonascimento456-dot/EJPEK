<?php
declare(strict_types=1);

if (!defined('BASE_URL')) {
    $s = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/veiculos.php');
    define('BASE_URL', rtrim(dirname($s), '/') . '/');
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/config/Auth.php';
require_once __DIR__ . '/../app/config/Layout.php';

exigeLogin();

$ctrl = new VeiculoController(db());
$ctrl->handle();
