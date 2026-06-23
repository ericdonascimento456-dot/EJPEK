<?php renderHeader('Clientes Mais Ativos'); ?>
<div class="page-title">
  <span><i class="bi bi-people-fill" style="color:var(--am-blue)"></i> Clientes Mais Ativos</span>
  <a href="<?= BASE_URL ?>relatorios.php" class="am-btn am-btn-muted am-btn-sm"><i class="bi bi-arrow-left"></i> Relatórios</a>
</div>

<div class="am-card">
  <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
    <input type="hidden" name="modo" value="clientes">
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

<div class="am-card">
  <div class="am-card-header"><i class="bi bi-trophy-fill"></i> Ranking de Clientes</div>
  <?php if (!$lista): ?>
    <p style="color:var(--am-muted);text-align:center;padding:2rem">Nenhum cliente com OS neste período.</p>
  <?php else:
    $maxOs = (int)$lista[0]['total_os'];
  ?>
  <table class="am-table">
    <thead>
      <tr><th>#</th><th>Cliente</th><th>Celular</th><th>Total de OS</th><th>Valor Total</th><th>Volume</th></tr>
    </thead>
    <tbody>
    <?php foreach ($lista as $i => $c): ?>
    <tr>
      <td style="color:var(--am-muted);font-size:.8rem"><?= $i + 1 ?>º</td>
      <td style="font-weight:600"><?= htmlspecialchars($c['cliente']) ?></td>
      <td style="color:var(--am-muted);font-size:.85rem"><?= htmlspecialchars($c['celular'] ?? '—') ?></td>
      <td style="font-weight:700;color:var(--am-blue)"><?= $c['total_os'] ?></td>
      <td style="color:var(--am-green)">R$ <?= number_format((float)$c['valor_total'], 2, ',', '.') ?></td>
      <td style="width:140px">
        <div style="height:8px;background:var(--am-border);border-radius:4px">
          <div style="width:<?= $maxOs > 0 ? round($c['total_os'] / $maxOs * 100) : 0 ?>%;height:100%;border-radius:4px;background:var(--am-blue)"></div>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php renderFooter(); ?>
