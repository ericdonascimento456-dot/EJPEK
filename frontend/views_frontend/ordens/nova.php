<?php renderHeader('Nova OS'); ?>
<div class="page-title">
  Nova Ordem de Serviço
  <a href="<?= BASE_URL ?>ordens.php" class="am-btn am-btn-muted am-btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>
<div class="am-card" style="max-width:700px">
  <form method="POST">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
      <div>
        <label class="am-label">Cliente *</label>
        <select class="am-select" name="id_cliente" id="sel-cliente" required onchange="filtrarVeiculos()">
          <option value="">Selecione…</option>
          <?php foreach($clientes as $c): ?>
          <option value="<?= $c['id_cliente'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="am-label">Veículo *</label>
        <select class="am-select" name="id_veiculo" id="sel-veiculo" required>
          <option value="">Selecione o cliente primeiro…</option>
          <?php foreach($veiculos as $v): ?>
          <option value="<?= $v['id_veiculo'] ?>" data-cliente="<?= $v['id_cliente'] ?>" style="display:none">
            <?= htmlspecialchars($v['placa'].' — '.$v['marca'].' '.$v['modelo']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="am-label">Prazo Previsto</label>
        <input class="am-input" type="date" name="prazo_previsto" min="<?= date('Y-m-d') ?>">
      </div>
      <div style="grid-column:1/-1">
        <label class="am-label">Diagnóstico / Descrição do Problema *</label>
        <textarea class="am-textarea" name="diagnostico" rows="4" required placeholder="Descreva o problema relatado pelo cliente…"></textarea>
      </div>
    </div>
    <div style="margin-top:1.5rem;display:flex;gap:.75rem">
      <button class="am-btn am-btn-primary" type="submit"><i class="bi bi-plus-lg"></i> Abrir OS</button>
      <a href="<?= BASE_URL ?>ordens.php" class="am-btn am-btn-muted">Cancelar</a>
    </div>
  </form>
</div>
<script>
function filtrarVeiculos() {
    const idCliente = document.getElementById('sel-cliente').value;
    const sel = document.getElementById('sel-veiculo');
    sel.innerHTML = '<option value="">Selecione…</option>';
    document.querySelectorAll('#sel-veiculo-source option').forEach(o => {
        if (o.dataset.cliente === idCliente) {
            const clone = o.cloneNode(true);
            clone.style.display = '';
            sel.appendChild(clone);
        }
    });
    if (sel.options.length === 1) sel.options[0].text = 'Nenhum veículo para este cliente';
}
const source = document.createElement('select');
source.id = 'sel-veiculo-source';
source.style.display = 'none';
document.querySelectorAll('#sel-veiculo option[data-cliente]').forEach(o => {
    source.appendChild(o.cloneNode(true));
});
document.body.appendChild(source);
</script>
<?php renderFooter(); ?>
