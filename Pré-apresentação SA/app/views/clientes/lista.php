<?php renderHeader('Clientes'); ?>
<div class="page-title">
  Clientes
  <a href="<?= BASE_URL ?>clientes.php?modo=novo" class="am-btn am-btn-success"><i class="bi bi-plus-lg"></i> Novo Cliente</a>
</div>
<div class="am-card">
  <form method="GET" style="display:flex;gap:.75rem;margin-bottom:1rem">
    <input class="am-input" name="q" placeholder="🔍 Buscar por nome ou CPF…" value="<?= htmlspecialchars($q) ?>" style="max-width:360px">
    <button class="am-btn am-btn-muted" type="submit">Buscar</button>
    <?php if($q): ?><a href="<?= BASE_URL ?>clientes.php" class="am-btn am-btn-muted">Limpar</a><?php endif; ?>
  </form>
  <table class="am-table">
    <thead><tr><th>#</th><th>Nome</th><th>CPF</th><th>Celular</th><th>E-mail</th><th>Ações</th></tr></thead>
    <tbody>
    <?php foreach ($lista as $c): ?>
      <tr>
        <td style="color:var(--am-muted)"><?= $c['id_cliente'] ?></td>
        <td><strong><?= htmlspecialchars($c['nome']) ?></strong></td>
        <td style="font-family:'JetBrains Mono',monospace;font-size:.8rem"><?= preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $c['cpf']) ?></td>
        <td><?= $c['celular'] ? preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $c['celular']) : '—' ?></td>
        <td style="color:var(--am-muted)"><?= htmlspecialchars($c['email'] ?? '—') ?></td>
        <td style="display:flex;gap:.4rem">
          <a href="<?= BASE_URL ?>clientes.php?modo=editar&id=<?= $c['id_cliente'] ?>" class="am-btn am-btn-warn am-btn-sm"><i class="bi bi-pencil"></i></a>
          <?php if(ehGerente()): ?>
          <a href="<?= BASE_URL ?>clientes.php?modo=excluir&id=<?= $c['id_cliente'] ?>" class="am-btn am-btn-danger am-btn-sm"
             onclick="return confirm('Desativar <?= htmlspecialchars(addslashes($c['nome'])) ?>?')"><i class="bi bi-trash"></i></a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$lista): ?><tr><td colspan="6" style="text-align:center;color:var(--am-muted);padding:2rem">Nenhum cliente encontrado.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php renderFooter(); ?>
