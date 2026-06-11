<?php
declare(strict_types=1);
$s = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/logout.php');
$base = rtrim(dirname($s), '/') . '/';
session_start();
session_destroy();
header('Location: ' . $base . 'login.php');
exit;
