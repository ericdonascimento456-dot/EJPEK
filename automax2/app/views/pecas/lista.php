<?php renderHeader('Peças & Estoque'); ?>
<div class="page-title">
  Peças & Estoque
  <a href="<?= BASE_URL ?>pecas.php?modo=novo" class="am-btn am-btn-success"><i class="bi bi-plus-lg"></i> Nova Peça</a>
</div>
<div class="am-card">
  <form method="GET" style="display:flex;gap:.75rem;margin-bottom:1rem;flex-wrap:wrap">
    <input class="am-input" name="q" placeholder="🔍 Código, descrição ou fornecedor…" value="<?= htmlspecialchars($q) ?>" style="max-width:360px">
    <label style="display:flex;align-items:center;gap:.4rem;color:var(--am-muted);font-size:.85rem">
      <input type="checkbox" name="alerta" value="1" <?= $alerta?'checked':'' ?>> Somente estoque crítico
    </label>
    <button class="am-btn am-btn-muted" type="submit">Filtrar</button>
    <a href="<?= BASE_URL ?>pecas.php" class="am-btn am-btn-muted">Limpar</a>
  </form>
  <table class="am-table">
    <thead><tr><th>Código</th><th>Descrição</th><th>Custo</th><th>Venda</th><th>Margem</th><th>Estoque</th><th>Mínimo</th><th>Status</th><th>Ações</th></tr></thead>
    <tbody>
    <?php foreach($lista as $p):
        $margem = (float)$p['preco_venda'] - (float)$p['preco_custo'];
        $critico = $p['estoque_atual'] <= $p['estoque_minimo'];
        $zerado  = $p['estoque_atual'] <= 0;
    ?>
    <tr>
      <td><code style="background:rgba(255,255,255,.06);padding:2px 7px;border-radius:4px"><?= htmlspecialchars($p['codigo']) ?></code></td>
      <td><?= htmlspecialchars($p['descricao']) ?></td>
      <td style="color:var(--am-muted)">R$ <?= number_format((float)$p['preco_custo'],2,',','.') ?></td>
      <td>R$ <?= number_format((float)$p['preco_venda'],2,',','.') ?></td>
      <td style="color:<?= $margem>=0?'var(--am-green)':'var(--am-accent)' ?>">R$ <?= number_format($margem,2,',','.') ?></td>
      <td style="text-align:center;font-weight:700;color:<?= $zerado?'var(--am-accent)':($critico?'var(--am-yellow)':'var(--am-text)') ?>"><?= $p['estoque_atual'] ?></td>
      <td style="text-align:center;color:var(--am-muted)"><?= $p['estoque_minimo'] ?></td>
      <td>
        <?php if ($zerado): ?>  <span class="badge-status badge-danger">Sem estoque</span>
        <?php elseif ($critico): ?><span class="badge-status badge-warn">Crítico</span>
        <?php else: ?>           <span class="badge-status badge-ok">OK</span>
        <?php endif; ?>
      </td>
      <td style="display:flex;gap:.4rem">
        <a href="<?= BASE_URL ?>pecas.php?modo=editar&id=<?= $p['id_peca'] ?>" class="am-btn am-btn-warn am-btn-sm"><i class="bi bi-pencil"></i></a>
        <?php if(ehGerente()): ?>
        <a href="<?= BASE_URL ?>pecas.php?modo=excluir&id=<?= $p['id_peca'] ?>" class="am-btn am-btn-danger am-btn-sm"
           onclick="return confirm('Remover peça?')"><i class="bi bi-trash"></i></a>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$lista): ?><tr><td colspan="9" style="text-align:center;color:var(--am-muted);padding:2rem">Nenhuma peça encontrada.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php renderFooter(); ?>
