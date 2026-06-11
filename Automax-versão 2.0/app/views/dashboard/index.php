<?php renderHeader('Dashboard'); ?>
<div style="background:rgba(210,153,34,.1);border:1px solid rgba(210,153,34,.35);border-radius:10px;
            padding:.85rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem">
  <i class="bi bi-cone-striped" style="color:var(--am-yellow);font-size:1.4rem;flex-shrink:0"></i>
  <div>
    <strong style="color:var(--am-yellow);font-size:.9rem">Fase de Testes — v0.1 Beta</strong>
    <div style="color:var(--am-muted);font-size:.78rem;margin-top:.15rem">
      Algumas funcionalidades estão desabilitadas ou em desenvolvimento. Relatórios e finalização de OS estarão disponíveis na versão final.
    </div>
  </div>
</div>
<div class="page-title">Dashboard</div>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-icon" style="color:var(--am-blue)">👥</div>
    <div class="stat-num" style="color:var(--am-blue)"><?= $clientes ?></div>
    <div class="stat-label">Clientes Cadastrados</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="color:var(--am-yellow)">🔧</div>
    <div class="stat-num" style="color:var(--am-yellow)"><?= $osAbertas ?></div>
    <div class="stat-label">OS em Aberto</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="color:var(--am-accent)">⚠️</div>
    <div class="stat-num" style="color:var(--am-accent)"><?= $alertas ?></div>
    <div class="stat-label">Alertas de Estoque</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="color:var(--am-green)">💰</div>
    <div class="stat-num" style="color:var(--am-green); font-size:1.4rem;">R$ <?= number_format((float)$fatMes, 2, ',', '.') ?></div>
    <div class="stat-label">Faturamento do Mês</div>
  </div>
</div>

<div class="am-card">
  <div class="am-card-header"><i class="bi bi-clock-history"></i> Últimas Ordens de Serviço</div>
  <table class="am-table">
    <thead>
      <tr><th>#</th><th>Cliente</th><th>Placa</th><th>Status</th><th>Data</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach ($ultimas as $o):
        $badge = match($o['status']) {
            'Finalizada'      => 'badge-ok',
            'Cancelada'       => 'badge-muted',
            'Em andamento'    => 'badge-info',
            'Aguardando peca' => 'badge-warn',
            default           => 'badge-warn'
        };
    ?>
      <tr>
        <td><strong style="color:var(--am-blue)">#<?= $o['id_os'] ?></strong></td>
        <td><?= htmlspecialchars($o['cliente']) ?></td>
        <td><code style="background:rgba(255,255,255,.06);padding:2px 7px;border-radius:4px;font-size:.8rem"><?= htmlspecialchars($o['placa']) ?></code></td>
        <td><span class="badge-status <?= $badge ?>"><?= $o['status'] ?></span></td>
        <td style="color:var(--am-muted);font-size:.8rem"><?= date('d/m/Y', strtotime($o['data_abertura'])) ?></td>
        <td><a href="<?= BASE_URL ?>ordens.php?modo=detalhe&id=<?= $o['id_os'] ?>" class="am-btn am-btn-info am-btn-sm"><i class="bi bi-eye"></i> Ver</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php renderFooter(); ?>
