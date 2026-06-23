<?php renderHeader(($veiculo && ($veiculo['id_veiculo'] ?? 0)) ? 'Editar Veículo' : 'Novo Veículo'); ?>
<div class="page-title">
  <?= ($veiculo && ($veiculo['id_veiculo'] ?? 0)) ? 'Editar Veículo' : 'Novo Veículo' ?>
  <a href="<?= BASE_URL ?>veiculos.php" class="am-btn am-btn-muted am-btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>
<div class="am-card" style="max-width:780px">
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= (int)($veiculo['id_veiculo'] ?? 0) ?>">
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem">
      <div>
        <label class="am-label">Placa *</label>
        <input class="am-input" name="placa" maxlength="8" required style="text-transform:uppercase"
               value="<?= htmlspecialchars($veiculo['placa'] ?? '') ?>">
      </div>
      <div>
        <label class="am-label">Marca *</label>
        <input class="am-input" name="marca" required value="<?= htmlspecialchars($veiculo['marca'] ?? '') ?>">
      </div>
      <div>
        <label class="am-label">Modelo *</label>
        <input class="am-input" name="modelo" required value="<?= htmlspecialchars($veiculo['modelo'] ?? '') ?>">
      </div>
      <div>
        <label class="am-label">Ano *</label>
        <input class="am-input" type="number" name="ano" min="1900" max="2100" required
               value="<?= htmlspecialchars((string)($veiculo['ano'] ?? '')) ?>">
      </div>
      <div>
        <label class="am-label">Cor</label>
        <input class="am-input" name="cor" value="<?= htmlspecialchars($veiculo['cor'] ?? '') ?>">
      </div>
      <div>
        <label class="am-label">KM Atual</label>
        <input class="am-input" type="number" step="0.01" name="km_atual"
               value="<?= htmlspecialchars((string)($veiculo['km_atual'] ?? '0')) ?>">
      </div>
      <div>
        <label class="am-label">Combustível</label>
        <select class="am-select" name="combustivel">
          <?php foreach($combustiveis as $val=>$label): ?>
          <option value="<?= $val ?>" <?= ($veiculo['combustivel']??'flex')===$val?'selected':'' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="am-label">RENAVAM</label>
        <input class="am-input" name="renavam" maxlength="11" value="<?= htmlspecialchars($veiculo['renavam'] ?? '') ?>">
      </div>
      <div>
        <label class="am-label">Chassi (VIN)</label>
        <input class="am-input" name="chassi" maxlength="17" style="text-transform:uppercase"
               value="<?= htmlspecialchars($veiculo['chassi'] ?? '') ?>">
      </div>
      <div style="grid-column:1/-1">
        <label class="am-label">Proprietário (Cliente) *</label>
        <select class="am-select" name="id_cliente" required>
          <option value="">Selecione…</option>
          <?php foreach($clientes as $cli): ?>
          <option value="<?= $cli['id_cliente'] ?>" <?= ($veiculo['id_cliente']??0)==$cli['id_cliente']?'selected':'' ?>>
            <?= htmlspecialchars($cli['nome']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="grid-column:1/-1">
        <label class="am-label">Foto do Veículo</label>
        <?php if (!empty($veiculo['foto'])): ?>
          <img src="<?= UPLOAD_URL . htmlspecialchars($veiculo['foto']) ?>" alt="Foto atual" class="foto-preview" id="preview-img">
        <?php else: ?>
          <img src="" alt="" class="foto-preview" id="preview-img" style="display:none">
        <?php endif; ?>
        <input class="am-input" type="file" name="foto" accept="image/jpeg,image/png,image/webp"
               style="margin-top:.5rem" onchange="previewFoto(this)">
        <small style="color:var(--am-muted)">JPG, PNG ou WebP — máx. 5MB. Deixe vazio para manter a foto atual.</small>
      </div>
    </div>
    <div style="margin-top:1.5rem;display:flex;gap:.75rem">
      <button class="am-btn am-btn-primary" type="submit"><i class="bi bi-check-lg"></i> Salvar</button>
      <a href="<?= BASE_URL ?>veiculos.php" class="am-btn am-btn-muted">Cancelar</a>
    </div>
  </form>
</div>
<script>
function previewFoto(input) {
    if (input.files && input.files[0]) {
        const img = document.getElementById('preview-img');
        img.src = URL.createObjectURL(input.files[0]);
        img.style.display = 'block';
    }
}
</script>
<?php renderFooter(); ?>
