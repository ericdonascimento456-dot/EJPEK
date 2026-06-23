<?php renderHeader('OS #' . $os['id_os']); ?>
<div class="page-title" style="margin-bottom:1rem">
  <span>OS <span style="color:var(--am-blue)">#<?= $os['id_os'] ?></span></span>
  <div style="display:flex;gap:.5rem;align-items:center">
    <?php
    $badge = match($os['status']) {
        'Finalizada'      => 'badge-ok',
        'Cancelada'       => 'badge-muted',
        'Em andamento'    => 'badge-info',
        'Aguardando peca' => 'badge-warn',
        default           => 'badge-warn'
    };
    ?>
    <span class="badge-status <?= $badge ?>" style="font-size:.85rem;padding:.3rem .85rem"><?= $os['status'] ?></span>
    <a href="<?= BASE_URL ?>ordens.php" class="am-btn am-btn-muted am-btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem">
  <!-- Card Veículo -->
  <div class="am-card" style="margin-bottom:0">
    <div class="am-card-header" style="justify-content:space-between">
      <span><i class="bi bi-car-front-fill" style="color:var(--am-blue)"></i> Veículo</span>
      <?php if ($os['veiculo_foto']): ?>
        <img src="<?= UPLOAD_URL . htmlspecialchars($os['veiculo_foto']) ?>"
             alt="Foto do veículo"
             style="height:54px;width:86px;object-fit:cover;border-radius:6px;border:1px solid var(--am-border);cursor:pointer;transition:transform .15s"
             onclick="document.getElementById('modal-foto').style.display='flex'"
             title="Clique para ampliar">
      <?php endif; ?>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem .5rem;font-size:.875rem">
      <div style="color:var(--am-muted)">Placa</div>
      <div><code style="background:rgba(255,255,255,.08);padding:2px 8px;border-radius:4px;font-weight:700"><?= htmlspecialchars($os['placa']) ?></code></div>
      <div style="color:var(--am-muted)">Veículo</div>
      <div><strong><?= htmlspecialchars($os['marca'].' '.$os['modelo']) ?></strong></div>
      <div style="color:var(--am-muted)">Ano</div>
      <div><?= $os['ano'] ?></div>
      <div style="color:var(--am-muted)">Cor</div>
      <div><?= htmlspecialchars($os['cor'] ?? '—') ?></div>
      <div style="color:var(--am-muted)">Combustível</div>
      <div><?= $combustivelLabels[$os['combustivel'] ?? ''] ?? '—' ?></div>
      <div style="color:var(--am-muted)">KM Atual</div>
      <div><?= number_format((float)$os['km_atual'],0,',','.') ?> km</div>
      <?php if ($os['renavam']): ?>
      <div style="color:var(--am-muted)">RENAVAM</div>
      <div style="font-family:'JetBrains Mono',monospace;font-size:.8rem"><?= htmlspecialchars($os['renavam']) ?></div>
      <?php endif; ?>
      <?php if ($os['chassi']): ?>
      <div style="color:var(--am-muted)">Chassi</div>
      <div style="font-family:'JetBrains Mono',monospace;font-size:.75rem"><?= htmlspecialchars($os['chassi']) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Card Cliente + OS info -->
  <div style="display:flex;flex-direction:column;gap:1.25rem">
    <div class="am-card" style="margin-bottom:0">
      <div class="am-card-header"><i class="bi bi-person-fill" style="color:var(--am-green)"></i> Cliente</div>
      <div style="font-size:.875rem;display:grid;gap:.5rem">
        <div><strong style="font-size:1rem"><?= htmlspecialchars($os['cliente_nome']) ?></strong></div>
        <div style="color:var(--am-muted)"><?= htmlspecialchars($os['cliente_cel'] ?? '—') ?></div>
        <div style="color:var(--am-muted)"><?= htmlspecialchars($os['cliente_email'] ?? '—') ?></div>
      </div>
    </div>
    <div class="am-card" style="margin-bottom:0">
      <div class="am-card-header"><i class="bi bi-info-circle" style="color:var(--am-yellow)"></i> Dados da OS</div>
      <div style="font-size:.875rem;display:grid;grid-template-columns:1fr 1fr;gap:.5rem .3rem">
        <div style="color:var(--am-muted)">Abertura</div>
        <div><?= date('d/m/Y', strtotime($os['data_abertura'])) ?></div>
        <div style="color:var(--am-muted)">Prazo</div>
        <div><?= $os['prazo_previsto'] ? date('d/m/Y', strtotime($os['prazo_previsto'])) : '—' ?></div>
        <?php if ($os['data_fechamento']): ?>
        <div style="color:var(--am-muted)">Fechamento</div>
        <div style="color:var(--am-green)"><?= date('d/m/Y', strtotime($os['data_fechamento'])) ?></div>
        <?php endif; ?>
        <div style="color:var(--am-muted)">Aberta por</div>
        <div><?= htmlspecialchars($os['aberto_por'] ?? '—') ?></div>
      </div>
      <?php if ($os['diagnostico']): ?>
      <div style="margin-top:.75rem;padding:.75rem;background:rgba(255,255,255,.03);border-radius:6px;border:1px solid var(--am-border);font-size:.85rem">
        <div style="color:var(--am-muted);font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem">Diagnóstico</div>
        <?= nl2br(htmlspecialchars($os['diagnostico'])) ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Itens da OS -->
<div class="am-card">
  <div class="am-card-header"><i class="bi bi-gear-fill" style="color:var(--am-accent)"></i> Peças Utilizadas</div>
  <table class="am-table">
    <thead><tr><th>Código</th><th>Descrição</th><th>Qtd</th><th>Preço Unit.</th><th>Subtotal</th><?= $os['status']!=='Finalizada'?'<th></th>':'' ?></tr></thead>
    <tbody>
    <?php foreach($itens as $i): $sub = $i['quantidade'] * $i['preco_unit']; ?>
    <tr>
      <td><code style="background:rgba(255,255,255,.06);padding:2px 6px;border-radius:4px"><?= htmlspecialchars($i['codigo']) ?></code></td>
      <td><?= htmlspecialchars($i['peca_nome']) ?></td>
      <td style="text-align:center"><?= $i['quantidade'] ?></td>
      <td>R$ <?= number_format((float)$i['preco_unit'],2,',','.') ?></td>
      <td><strong>R$ <?= number_format($sub,2,',','.') ?></strong></td>
      <?php if($os['status']!=='Finalizada'): ?>
      <td>
        <?php if (ehGerente()): ?>
        <form method="POST" style="display:inline" onsubmit="return confirm('Remover esta peça da OS? A quantidade volta ao estoque.');">
          <input type="hidden" name="acao" value="remover_item">
          <input type="hidden" name="id_item" value="<?= $i['id_item'] ?>">
          <button class="am-btn am-btn-danger am-btn-sm" type="submit" title="Remover peça">
            <i class="bi bi-trash"></i>
          </button>
        </form>
        <?php else: ?>
        <button class="am-btn am-btn-muted am-btn-sm" disabled
                title="Apenas o Gerente pode remover" style="cursor:not-allowed;opacity:.45">
          <i class="bi bi-trash"></i>
        </button>
        <?php endif; ?>
      </td>
      <?php endif; ?>
    </tr>
    <?php endforeach; ?>
    <?php if(!$itens): ?>
    <tr><td colspan="6" style="text-align:center;color:var(--am-muted);padding:1.5rem">Nenhuma peça adicionada.</td></tr>
    <?php endif; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="<?= $os['status']!=='Finalizada' ? 4 : 3 ?>" style="text-align:right;font-weight:700;color:var(--am-muted);padding:.75rem .9rem">TOTAL DAS PEÇAS</td>
        <td style="font-weight:700;font-size:1.05rem;color:var(--am-green);padding:.75rem .9rem">R$ <?= number_format($totalItens,2,',','.') ?></td>
        <?= $os['status']!=='Finalizada' ? '<td></td>' : '' ?>
      </tr>
    </tfoot>
  </table>

  <!-- Adicionar peça -->
  <?php if ($os['status'] !== 'Finalizada' && $os['status'] !== 'Cancelada'): ?>
  <div style="margin-top:1.25rem;padding:1.25rem;background:rgba(88,166,255,.05);border:1px solid rgba(88,166,255,.15);border-radius:8px">
    <div style="font-size:.85rem;font-weight:700;color:var(--am-blue);margin-bottom:.75rem"><i class="bi bi-plus-circle"></i> Adicionar Peça</div>
    <form method="POST" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
      <input type="hidden" name="acao" value="adicionar_peca">
      <div style="flex:3;min-width:200px">
        <label class="am-label">Peça</label>
        <select class="am-select" name="id_peca" required>
          <option value="">Selecione…</option>
          <?php foreach($pecasDisp as $p): ?>
          <option value="<?= $p['id_peca'] ?>"><?= htmlspecialchars($p['codigo'].' — '.$p['descricao']) ?> (<?= $p['estoque_atual'] ?> em estoque) — R$ <?= number_format((float)$p['preco_venda'],2,',','.') ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="flex:1;min-width:100px">
        <label class="am-label">Quantidade</label>
        <input class="am-input" type="number" name="quantidade" value="1" min="1">
      </div>
      <button class="am-btn am-btn-info" type="submit"><i class="bi bi-plus-lg"></i> Adicionar</button>
    </form>
  </div>
  <?php endif; ?>
</div>

<!-- Ações da OS -->
<?php if ($os['status'] !== 'Finalizada' && $os['status'] !== 'Cancelada'): ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">
  <div class="am-card" style="margin-bottom:0">
    <div class="am-card-header"><i class="bi bi-arrow-repeat" style="color:var(--am-yellow)"></i> Atualizar Status</div>
    <form method="POST" style="display:flex;gap:.75rem">
      <input type="hidden" name="acao" value="atualizar_status">
      <select class="am-select" name="status">
        <?php foreach(['Aberta','Em andamento','Aguardando peca'] as $s): ?>
        <option value="<?= $s ?>" <?= $os['status']===$s?'selected':'' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
      <button class="am-btn am-btn-warn" type="submit"><i class="bi bi-check-lg"></i> Atualizar</button>
    </form>
  </div>
  <div class="am-card" style="margin-bottom:0">
    <div class="am-card-header"><i class="bi bi-check-circle-fill" style="color:var(--am-green)"></i> Finalizar OS</div>
    <p style="font-size:.8rem;color:var(--am-muted);margin:0 0 .85rem">
      Fecha a ordem com o total das peças utilizadas e registra a data de fechamento. Essa ação não pode ser desfeita.
    </p>
    <form method="POST" onsubmit="return confirm('Finalizar esta OS? Essa ação não pode ser desfeita.');">
      <input type="hidden" name="acao" value="finalizar">
      <button class="am-btn am-btn-success" type="submit" style="width:100%">
        <i class="bi bi-check-lg"></i> Finalizar OS — R$ <?= number_format($totalItens, 2, ',', '.') ?>
      </button>
    </form>
  </div>
</div>
<?php elseif($os['status']==='Finalizada'): ?>
<div class="am-card" style="border-color:rgba(63,185,80,.3);text-align:center">
  <div style="font-size:2rem;margin-bottom:.5rem">✅</div>
  <div style="font-size:1rem;font-weight:700;color:var(--am-green)">OS Finalizada</div>
  <div style="font-size:1.75rem;font-weight:700;margin-top:.5rem">R$ <?= number_format((float)$os['valor_total'],2,',','.') ?></div>
  <div style="color:var(--am-muted);font-size:.85rem">Fechada em <?= date('d/m/Y', strtotime($os['data_fechamento'])) ?></div>
</div>
<?php endif; ?>

<?php if ($os['veiculo_foto']): ?>
<div id="modal-foto"
     onclick="this.style.display='none'"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9999;
            align-items:center;justify-content:center;cursor:zoom-out">
  <img src="<?= UPLOAD_URL . htmlspecialchars($os['veiculo_foto']) ?>"
       style="max-width:90vw;max-height:88vh;border-radius:10px;box-shadow:0 8px 40px rgba(0,0,0,.6)">
  <div style="position:absolute;top:1.5rem;right:1.5rem;color:#fff;font-size:1.8rem;line-height:1;cursor:pointer"
       onclick="document.getElementById('modal-foto').style.display='none'">✕</div>
</div>
<?php endif; ?>
<?php renderFooter(); ?>
