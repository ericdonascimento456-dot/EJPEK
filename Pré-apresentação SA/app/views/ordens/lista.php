<?php renderHeader('Ordens de Serviço'); ?>
<div class="page-title">
  Ordens de Serviço
  <a href="<?= BASE_URL ?>ordens.php?modo=nova" class="am-btn am-btn-primary"><i class="bi bi-plus-lg"></i> Nova OS</a>
</div>
<div class="am-card">
  <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1rem">
    <input class="am-input" name="q" placeholder="🔍 Cliente ou placa…" value="<?= htmlspecialchars($q) ?>" style="max-width:260px">
    <select class="am-select" name="status" style="max-width:220px">
      <option value="">Todos os status</option>
      <?php foreach($statuses as $s): ?>
      <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select>
    <button class="am-btn am-btn-muted" type="submit">Filtrar</button>
    <a href="<?= BASE_URL ?>ordens.php" class="am-btn am-btn-muted">Limpar</a>
  </form>
  <table class="am-table">
    <thead>
      <tr><th>#</th><th>Abertura</th><th>Cliente</th><th>Veículo</th><th>Status</th><th>Valor</th><th>Prazo</th><th>Ações</th></tr>
    </thead>
    <tbody>
    <?php foreach($lista as $o):
        $badge = match($o['status']) {
            'Finalizada'      => 'badge-ok',
            'Cancelada'       => 'badge-muted',
            'Em andamento'    => 'badge-info',
            'Aguardando peca' => 'badge-warn',
            default           => 'badge-warn'
        };
        $atrasada = ($o['prazo_previsto'] && $o['status'] !== 'Finalizada' && $o['status'] !== 'Cancelada'
                     && strtotime($o['prazo_previsto']) < time());
    ?>
    <tr <?= $atrasada ? 'style="background:rgba(247,129,102,.04)"' : '' ?>>
      <td><strong style="color:var(--am-blue)">#<?= $o['id_os'] ?></strong></td>
      <td style="color:var(--am-muted);font-size:.82rem"><?= date('d/m/Y', strtotime($o['data_abertura'])) ?></td>
      <td><?= htmlspecialchars($o['cliente_nome']) ?></td>
      <td>
        <code style="background:rgba(255,255,255,.06);padding:2px 7px;border-radius:4px"><?= htmlspecialchars($o['placa']) ?></code>
        <span style="color:var(--am-muted);font-size:.78rem"> <?= htmlspecialchars($o['marca'].' '.$o['modelo']) ?></span>
      </td>
      <td><span class="badge-status <?= $badge ?>"><?= $o['status'] ?></span></td>
      <td><?= $o['valor_total'] > 0 ? 'R$ '.number_format((float)$o['valor_total'],2,',','.') : '<span style="color:var(--am-muted)">—</span>' ?></td>
      <td style="font-size:.82rem;color:<?= $atrasada ? 'var(--am-accent)' : 'var(--am-muted)' ?>">
        <?= $o['prazo_previsto'] ? date('d/m/Y', strtotime($o['prazo_previsto'])) : '—' ?>
        <?= $atrasada ? ' ⚠️' : '' ?>
      </td>
      <td>
        <a href="<?= BASE_URL ?>ordens.php?modo=detalhe&id=<?= $o['id_os'] ?>" class="am-btn am-btn-info am-btn-sm"><i class="bi bi-eye"></i> Detalhes</a>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(!$lista): ?><tr><td colspan="8" style="text-align:center;color:var(--am-muted);padding:2rem">Nenhuma OS encontrada.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php renderFooter(); ?>
