/**
 * public/js/dashboard.js
 * AutoMax — Scripts globais do sistema
 *
 * Responsável por interações assíncronas, manipulação de eventos
 * e atualização de indicadores em tempo real sem recarregar a página.
 */

// ── Sidebar toggle (mobile) ────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Fecha sidebar ao clicar fora no mobile
    document.addEventListener('click', (e) => {
        const sidebar = document.getElementById('sidebar');
        const hamburger = document.querySelector('.hamburger');
        if (sidebar && hamburger) {
            if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        }
    });
});
