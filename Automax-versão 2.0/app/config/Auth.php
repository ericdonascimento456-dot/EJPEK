<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica se há um usuário autenticado na sessão.
 */
function usuarioLogado(): bool
{
    return isset($_SESSION['usuario']);
}

/**
 * Exige autenticação; redireciona para login se não estiver logado.
 */
function exigeLogin(): void
{
    if (!usuarioLogado()) {
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
}

/**
 * Retorna os dados do usuário logado.
 */
function usuario(): array
{
    return $_SESSION['usuario'] ?? [];
}

/**
 * Verifica se o usuário tem perfil de Gerente.
 */
function ehGerente(): bool
{
    return (usuario()['perfil'] ?? '') === 'Gerente';
}

/**
 * Registra uma mensagem flash na sessão.
 */
function flash(string $msg, string $tipo = 'success'): void
{
    $_SESSION['flash'][] = ['msg' => $msg, 'tipo' => $tipo];
}

/**
 * Recupera e limpa as mensagens flash da sessão.
 */
function getFlash(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}
