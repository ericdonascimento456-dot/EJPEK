<?php renderHeader('Relatórios'); ?>
<div class="page-title">
  <span><i class="bi bi-bar-chart-fill" style="color:var(--am-blue)"></i> Relatórios</span>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
  <a href="<?= BASE_URL ?>relatorios.php?modo=faturamento" class="am-card" style="text-decoration:none;transition:border-color .15s" onmouseover="this.style.borderColor='var(--am-blue)'" onmouseout="this.style.borderColor='var(--am-border)'">
    <div class="stat-icon" style="color:var(--am-green)"><i class="bi bi-cash-stack"></i></div>
    <div class="stat-num" style="color:var(--am-green)">Faturamento</div>
    <div class="stat-label">Receita por período</div>
  </a>
  <a href="<?= BASE_URL ?>relatorios.php?modo=pecas" class="am-card" style="text-decoration:none;transition:border-color .15s" onmouseover="this.style.borderColor='var(--am-blue)'" onmouseout="this.style.borderColor='var(--am-border)'">
    <div class="stat-icon" style="color:var(--am-yellow)"><i class="bi bi-gear-wide-connected"></i></div>
    <div class="stat-num" style="color:var(--am-yellow)">Peças</div>
    <div class="stat-label">Mais utilizadas no período</div>
  </a>
  <a href="<?= BASE_URL ?>relatorios.php?modo=clientes" class="am-card" style="text-decoration:none;transition:border-color .15s" onmouseover="this.style.borderColor='var(--am-blue)'" onmouseout="this.style.borderColor='var(--am-border)'">
    <div class="stat-icon" style="color:var(--am-blue)"><i class="bi bi-people-fill"></i></div>
    <div class="stat-num" style="color:var(--am-blue)">Clientes</div>
    <div class="stat-label">Mais ativos no período</div>
  </a>
  <a href="<?= BASE_URL ?>relatorios.php?modo=estoque" class="am-card" style="text-decoration:none;transition:border-color .15s" onmouseover="this.style.borderColor='var(--am-accent)'" onmouseout="this.style.borderColor='var(--am-border)'">
    <div class="stat-icon" style="color:var(--am-accent)"><i class="bi bi-exclamation-triangle-fill"></i></div>
    <div class="stat-num" style="color:var(--am-accent)">Estoque Crítico</div>
    <div class="stat-label">Abaixo do mínimo</div>
  </a>
</div>

<!-- Distribuição de OS por status -->
<div class="am-card">
  <div class="am-card-header"><i class="bi bi-pie-chart-fill"></i> Distribuição de OS por Status</div>
  <?php if (!$osPorStatus): ?>
    <p style="color:var(--am-muted);text-align:center;padding:1.5rem">Nenhuma OS registrada.</p>
  <?php else: ?>
  <table class="am-table">
    <thead>
      <tr><th>Status</th><th>Quantidade</th><th>Proporção</th></tr>
    </thead>
    <tbody>
    <?php
      $totalOs = array_sum(array_column($osPorStatus, 'total'));
      foreach ($osPorStatus as $row):
        $badge = match($row['status']) {
          'Finalizada'      => 'badge-ok',
          'Cancelada'       => 'badge-muted',
          'Em andamento'    => 'badge-info',
          'Aguardando peca' => 'badge-warn',
          default           => 'badge-warn'
        };
        $pct = $totalOs > 0 ? round($row['total'] / $totalOs * 100, 1) : 0;
    ?>
    <tr>
      <td><span class="badge-status <?= $badge ?>"><?= htmlspecialchars($row['status']) ?></span></td>
      <td><strong><?= $row['total'] ?></strong></td>
      <td>
        <div style="display:flex;align-items:center;gap:.75rem">
          <div style="flex:1;height:8px;background:var(--am-border);border-radius:4px;max-width:160px">
            <div style="width:<?= $pct ?>%;height:100%;border-radius:4px;background:var(--am-blue)"></div>
          </div>
          <span style="color:var(--am-muted);font-size:.8rem"><?= $pct ?>%</span>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>

<!-- Estoque crítico resumido -->
<?php if ($estoqueCritico): ?>
<div class="am-card">
  <div class="am-card-header" style="color:var(--am-accent)"><i class="bi bi-exclamation-triangle-fill"></i> Alerta: <?= count($estoqueCritico) ?> peça(s) com estoque crítico</div>
  <p style="color:var(--am-muted);font-size:.85rem;margin-bottom:1rem">As peças abaixo estão no limite ou abaixo do estoque mínimo configurado.</p>
  <table class="am-table">
    <thead><tr><th>Código</th><th>Descrição</th><th>Atual</th><th>Mínimo</th><th>Defasagem</th></tr></thead>
    <tbody>
    <?php foreach (array_slice($estoqueCritico, 0, 5) as $p): ?>
    <tr>
      <td><code style="background:rgba(255,255,255,.06);padding:2px 7px;border-radius:4px"><?= htmlspecialchars($p['codigo']) ?></code></td>
      <td><?= htmlspecialchars($p['descricao']) ?></td>
      <td style="color:var(--am-accent);font-weight:700"><?= $p['estoque_atual'] ?></td>
      <td style="color:var(--am-muted)"><?= $p['estoque_minimo'] ?></td>
      <td><span class="badge-status badge-danger">-<?= $p['defasagem'] ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (count($estoqueCritico) > 5): ?>
    <div style="margin-top:.75rem">
      <a href="<?= BASE_URL ?>relatorios.php?modo=estoque" class="am-btn am-btn-muted am-btn-sm">Ver todas (<?= count($estoqueCritico) ?>)</a>
    </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php renderFooter(); ?>
