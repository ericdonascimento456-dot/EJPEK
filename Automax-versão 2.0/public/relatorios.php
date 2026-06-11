<?php
declare(strict_types=1);
$s = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/relatorios.php');
$base = rtrim(dirname($s), '/') . '/';
header('Location: ' . $base . 'index.php');
exit;
