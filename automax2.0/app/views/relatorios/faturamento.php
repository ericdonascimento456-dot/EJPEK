<?php renderHeader('Relatório de Faturamento'); ?>
<div class="page-title">
  <span><i class="bi bi-cash-stack" style="color:var(--am-green)"></i> Faturamento por Período</span>
  <a href="<?= BASE_URL ?>relatorios.php" class="am-btn am-btn-muted am-btn-sm"><i class="bi bi-arrow-left"></i> Relatórios</a>
</div>

<!-- Filtro de período -->
<div class="am-card">
  <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
    <input type="hidden" name="modo" value="faturamento">
    <div>
      <label class="am-label">De</label>
      <input class="am-input" type="date" name="de" value="<?= htmlspecialchars($de) ?>" style="max-width:180px">
    </div>
    <div>
      <label class="am-label">Até</label>
      <input class="am-input" type="date" name="ate" value="<?= htmlspecialchars($ate) ?>" style="max-width:180px">
    </div>
    <button class="am-btn am-btn-primary" type="submit"><i class="bi bi-search"></i> Filtrar</button>
  </form>
</div>

<!-- Cards de totais -->
<div class="stat-grid">
  <div class="am-card">
    <div class="stat-icon" style="color:var(--am-green)"><i class="bi bi-cash-coin"></i></div>
    <div class="stat-num" style="color:var(--am-green)">R$ <?= number_format((float)$totais['faturamento'], 2, ',', '.') ?></div>
    <div class="stat-label">Faturamento no período</div>
  </div>
  <div class="am-card">
    <div class="stat-icon" style="color:var(--am-blue)"><i class="bi bi-clipboard2-check-fill"></i></div>
    <div class="stat-num" style="color:var(--am-blue)"><?= $totais['total_os'] ?></div>
    <div class="stat-label">OS finalizadas</div>
  </div>
  <div class="am-card">
    <div class="stat-icon" style="color:var(--am-yellow)"><i class="bi bi-calculator"></i></div>
    <div class="stat-num" style="color:var(--am-yellow)">
      R$ <?= $totais['total_os'] > 0 ? number_format((float)$totais['faturamento'] / (int)$totais['total_os'], 2, ',', '.') : '0,00' ?>
    </div>
    <div class="stat-label">Ticket médio</div>
  </div>
</div>

<!-- Tabela diária -->
<div class="am-card">
  <div class="am-card-header"><i class="bi bi-table"></i> Detalhamento Diário</div>
  <?php if (!$linhas): ?>
    <p style="color:var(--am-muted);text-align:center;padding:2rem">Nenhuma OS finalizada neste período.</p>
  <?php else: ?>
  <table class="am-table">
    <thead>
      <tr><th>Data</th><th>OS Finalizadas</th><th>Faturamento</th></tr>
    </thead>
    <tbody>
    <?php foreach ($linhas as $l): ?>
    <tr>
      <td style="color:var(--am-muted);font-size:.85rem"><?= htmlspecialchars($l['dia']) ?></td>
      <td><?= $l['total_os'] ?></td>
      <td style="color:var(--am-green);font-weight:600">R$ <?= number_format((float)$l['faturamento'], 2, ',', '.') ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php renderFooter(); ?>
