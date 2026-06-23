<?php renderHeader('Peças Mais Utilizadas'); ?>
<div class="page-title">
  <span><i class="bi bi-gear-wide-connected" style="color:var(--am-yellow)"></i> Peças Mais Utilizadas</span>
  <a href="<?= BASE_URL ?>relatorios.php" class="am-btn am-btn-muted am-btn-sm"><i class="bi bi-arrow-left"></i> Relatórios</a>
</div>

<div class="am-card">
  <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
    <input type="hidden" name="modo" value="pecas">
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
  <div class="am-card-header"><i class="bi bi-bar-chart-steps"></i> Ranking de Peças</div>
  <?php if (!$lista): ?>
    <p style="color:var(--am-muted);text-align:center;padding:2rem">Nenhuma peça utilizada em OS finalizadas neste período.</p>
  <?php else:
    $maxUso = (int)$lista[0]['total_usado'];
  ?>
  <table class="am-table">
    <thead>
      <tr><th>#</th><th>Código</th><th>Descrição</th><th>Qtd. Usada</th><th>Receita Gerada</th><th>Volume</th></tr>
    </thead>
    <tbody>
    <?php foreach ($lista as $i => $p): ?>
    <tr>
      <td style="color:var(--am-muted);font-size:.8rem"><?= $i + 1 ?>º</td>
      <td><code style="background:rgba(255,255,255,.06);padding:2px 7px;border-radius:4px"><?= htmlspecialchars($p['codigo']) ?></code></td>
      <td><?= htmlspecialchars($p['descricao']) ?></td>
      <td style="font-weight:700;color:var(--am-yellow)"><?= $p['total_usado'] ?></td>
      <td style="color:var(--am-green)">R$ <?= number_format((float)$p['receita_gerada'], 2, ',', '.') ?></td>
      <td style="width:140px">
        <div style="height:8px;background:var(--am-border);border-radius:4px">
          <div style="width:<?= $maxUso > 0 ? round($p['total_usado'] / $maxUso * 100) : 0 ?>%;height:100%;border-radius:4px;background:var(--am-yellow)"></div>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php renderFooter(); ?>
