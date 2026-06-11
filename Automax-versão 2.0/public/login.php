<?php
declare(strict_types=1);

if (!defined('BASE_URL')) {
    $s = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/login.php');
    define('BASE_URL', rtrim(dirname($s), '/') . '/');
}

require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/config/Auth.php';

if (usuarioLogado()) { header('Location: ' . BASE_URL . 'index.php'); exit; }

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $stmt  = db()->prepare("SELECT * FROM USUARIO WHERE login = ? AND ativo = 1 LIMIT 1");
    $stmt->execute([$login]);
    $user = $stmt->fetch();
    if ($user && $user['senha'] === $senha) {
        $_SESSION['usuario'] = [
            'id'     => $user['id_usuario'],
            'nome'   => $user['nome'],
            'perfil' => $user['perfil'],
        ];
        header('Location: ' . BASE_URL . 'index.php'); exit;
    }
    $erro = 'Login ou senha incorretos.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — AutoMax</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Sora',sans-serif;background:#0d1117;color:#e6edf3;min-height:100vh;
     display:flex;align-items:center;justify-content:center;}
.bg-glow{position:fixed;inset:0;pointer-events:none;
  background:radial-gradient(ellipse 60% 50% at 50% 0%,rgba(247,129,102,.12),transparent);}
.login-wrap{position:relative;z-index:1;width:100%;max-width:400px;padding:1.5rem;}
.card{background:#161b22;border:1px solid #30363d;border-radius:14px;padding:2.5rem;}
.brand{text-align:center;margin-bottom:2rem;}
.brand-icon{font-size:2.5rem;margin-bottom:.5rem;}
.brand-name{font-size:1.5rem;font-weight:700;color:#f78166;}
.brand-sub{font-size:.75rem;color:#8b949e;text-transform:uppercase;letter-spacing:1px;}
.field{margin-bottom:1rem;}
label{display:block;font-size:.75rem;font-weight:700;color:#8b949e;text-transform:uppercase;
      letter-spacing:.5px;margin-bottom:.4rem;}
input{width:100%;padding:.65rem .9rem;background:#0d1117;color:#e6edf3;
      border:1px solid #30363d;border-radius:8px;font-size:.9rem;font-family:inherit;}
input:focus{outline:none;border-color:#f78166;box-shadow:0 0 0 3px rgba(247,129,102,.15);}
.btn-login{width:100%;padding:.75rem;background:#f78166;color:#fff;border:none;
           border-radius:8px;font-size:.95rem;font-weight:700;font-family:inherit;
           cursor:pointer;margin-top:.5rem;transition:opacity .15s;}
.btn-login:hover{opacity:.85;}
.alert-err{background:rgba(247,129,102,.1);border:1px solid rgba(247,129,102,.3);
           color:#f78166;padding:.65rem .9rem;border-radius:8px;font-size:.85rem;
           margin-bottom:1rem;text-align:center;}
.hint{margin-top:1.5rem;padding:1rem;background:rgba(255,255,255,.03);border-radius:8px;
      font-size:.75rem;color:#8b949e;line-height:1.6;}
.hint strong{color:#58a6ff;}
</style>
</head>
<body>
<div class="bg-glow"></div>
<div class="login-wrap">
  <div class="card">
    <div class="brand">
      <div class="brand-icon">⚙️</div>
      <div class="brand-name">AutoMax</div>
      <div class="brand-sub">Sistema de Oficina</div>
    </div>
    <?php if ($erro): ?>
      <div class="alert-err"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="field"><label>Login</label><input type="text" name="login" autofocus required></div>
      <div class="field"><label>Senha</label><input type="password" name="senha" required></div>
      <button class="btn-login" type="submit">Entrar</button>
    </form>
    <div class="hint">
      <strong>antonio</strong>/123 (Gerente) &nbsp;|&nbsp;
      <strong>luciana</strong>/123 (Recepcionista) &nbsp;|&nbsp;
      <strong>jonas</strong>/123 (Mecânico)
    </div>
  </div>
</div>
</body></html>
