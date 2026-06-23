<?php renderHeader('Estoque Crítico'); ?>
<div class="page-title">
  <span><i class="bi bi-exclamation-triangle-fill" style="color:var(--am-accent)"></i> Estoque Crítico</span>
  <a href="<?= BASE_URL ?>relatorios.php" class="am-btn am-btn-muted am-btn-sm"><i class="bi bi-arrow-left"></i> Relatórios</a>
</div>

<div class="am-card">
  <div class="am-card-header"><i class="bi bi-box-seam"></i> Peças abaixo do estoque mínimo</div>
  <?php if (!$lista): ?>
    <p style="color:var(--am-green);text-align:center;padding:2rem"><i class="bi bi-check-circle-fill"></i> Nenhuma peça em situação crítica. Estoque em dia!</p>
  <?php else: ?>
  <p style="color:var(--am-muted);font-size:.85rem;margin-bottom:1rem">
    <i class="bi bi-info-circle"></i> <?= count($lista) ?> peça(s) precisam de reposição imediata.
  </p>
  <table class="am-table">
    <thead>
      <tr><th>Código</th><th>Descrição</th><th>Fornecedor</th><th>Estoque Atual</th><th>Mínimo</th><th>Defasagem</th></tr>
    </thead>
    <tbody>
    <?php foreach ($lista as $p):
      $defasagem = (int)$p['defasagem'];
      $cor = $defasagem > 5 ? 'var(--am-accent)' : 'var(--am-yellow)';
    ?>
    <tr>
      <td><code style="background:rgba(255,255,255,.06);padding:2px 7px;border-radius:4px"><?= htmlspecialchars($p['codigo']) ?></code></td>
      <td style="font-weight:600"><?= htmlspecialchars($p['descricao']) ?></td>
      <td style="color:var(--am-muted);font-size:.85rem"><?= htmlspecialchars($p['fornecedor'] ?? '—') ?></td>
      <td style="color:var(--am-accent);font-weight:700"><?= $p['estoque_atual'] ?></td>
      <td style="color:var(--am-muted)"><?= $p['estoque_minimo'] ?></td>
      <td><span class="badge-status badge-danger" style="background:rgba(247,129,102,.15);color:<?= $cor ?>">-<?= $defasagem ?> un.</span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
<?php renderFooter(); ?>
