<?php
declare(strict_types=1);

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'automax');
define('DB_USER', 'root');
define('DB_PASS', '');

// Raiz física do projeto: app/config/ -> app/ -> sgf_sistema/
define('ROOT_DIR', dirname(__DIR__, 2));
define('UPLOAD_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'veiculos' . DIRECTORY_SEPARATOR);

// UPLOAD_URL: usa BASE_URL (já definido pelo entry point) para montar a URL correta.
// uploads/ fica um nível acima de public/, então sobe com ../
if (!defined('UPLOAD_URL')) {
    $base = defined('BASE_URL') ? BASE_URL : '/';
    // Remove o 'public/' final e adiciona 'uploads/veiculos/'
    $upload = rtrim($base, '/');
    $upload = preg_replace('#/public$#', '', $upload);
    define('UPLOAD_URL', $upload . '/uploads/veiculos/');
}

/**
 * Provedor unificado de conexão PDO — Singleton estático.
 * Controllers não instanciam PDO diretamente (princípio DIP).
 */
class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT
                 . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                $msg = htmlspecialchars($e->getMessage());
                exit("<!DOCTYPE html><html><head><meta charset='UTF-8'>
                      <style>body{font-family:monospace;padding:2rem;background:#0d1117;color:#f78166}
                      pre{background:#161b22;padding:1rem;border-radius:6px;color:#e6edf3}</style></head>
                      <body><h2>&#10060; Erro de conexão com o banco</h2>
                      <pre>$msg</pre>
                      <p>Verifique: MySQL rodando? Banco <strong>automax</strong> importado?
                      Credenciais em <code>app/config/Database.php</code></p></body></html>");
            }
        }
        return self::$pdo;
    }
}

function db(): PDO
{
    return Database::getConnection();
}
