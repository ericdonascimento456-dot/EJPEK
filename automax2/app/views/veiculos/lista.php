<?php renderHeader('Veículos'); ?>
<div class="page-title">
  Veículos
  <a href="<?= BASE_URL ?>veiculos.php?modo=novo" class="am-btn am-btn-success"><i class="bi bi-plus-lg"></i> Novo Veículo</a>
</div>
<div class="am-card">
  <form method="GET" style="display:flex;gap:.75rem;margin-bottom:1rem">
    <input class="am-input" name="q" placeholder="🔍 Buscar por placa, modelo, marca ou cliente…" value="<?= htmlspecialchars($q) ?>" style="max-width:420px">
    <button class="am-btn am-btn-muted" type="submit">Buscar</button>
    <?php if($q): ?><a href="<?= BASE_URL ?>veiculos.php" class="am-btn am-btn-muted">Limpar</a><?php endif; ?>
  </form>
  <table class="am-table">
    <thead>
      <tr><th>Foto</th><th>Placa</th><th>Veículo</th><th>Ano</th><th>Cor</th><th>Combustível</th><th>KM</th><th>Cliente</th><th>Ações</th></tr>
    </thead>
    <tbody>
    <?php foreach ($lista as $v): ?>
      <tr>
        <td>
          <?php if ($v['foto']): ?>
          <img src="<?= UPLOAD_URL . htmlspecialchars($v['foto']) ?>" style="width:54px;height:40px;object-fit:cover;border-radius:6px;border:1px solid var(--am-border)">
          <?php else: ?>
          <div style="width:54px;height:40px;background:rgba(255,255,255,.04);border-radius:6px;border:1px solid var(--am-border);display:flex;align-items:center;justify-content:center;color:var(--am-muted);font-size:1.1rem">🚗</div>
          <?php endif; ?>
        </td>
        <td><code style="background:rgba(255,255,255,.06);padding:2px 8px;border-radius:4px"><?= htmlspecialchars($v['placa']) ?></code></td>
        <td><strong><?= htmlspecialchars($v['marca']) ?> <?= htmlspecialchars($v['modelo']) ?></strong></td>
        <td><?= $v['ano'] ?></td>
        <td><?= htmlspecialchars($v['cor'] ?? '—') ?></td>
        <td><span class="badge-status badge-info"><?= $combustiveis[$v['combustivel']] ?? $v['combustivel'] ?></span></td>
        <td style="color:var(--am-muted)"><?= number_format((float)$v['km_atual'], 0, ',', '.') ?> km</td>
        <td><?= htmlspecialchars($v['cliente_nome']) ?></td>
        <td style="display:flex;gap:.4rem">
          <a href="<?= BASE_URL ?>veiculos.php?modo=editar&id=<?= $v['id_veiculo'] ?>" class="am-btn am-btn-warn am-btn-sm"><i class="bi bi-pencil"></i></a>
          <?php if(ehGerente()): ?>
          <a href="<?= BASE_URL ?>veiculos.php?modo=excluir&id=<?= $v['id_veiculo'] ?>" class="am-btn am-btn-danger am-btn-sm"
             onclick="return confirm('Remover veículo <?= htmlspecialchars(addslashes($v['placa'])) ?>?')"><i class="bi bi-trash"></i></a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$lista): ?><tr><td colspan="9" style="text-align:center;color:var(--am-muted);padding:2rem">Nenhum veículo encontrado.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php renderFooter(); ?>
