<?php renderHeader(($peca && ($peca['id_peca']??0)) ? 'Editar Peça' : 'Nova Peça'); ?>
<div class="page-title">
  <?= ($peca && ($peca['id_peca']??0)) ? 'Editar Peça' : 'Nova Peça' ?>
  <a href="<?= BASE_URL ?>pecas.php" class="am-btn am-btn-muted am-btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>
<div class="am-card" style="max-width:700px">
  <form method="POST">
    <input type="hidden" name="id" value="<?= (int)($peca['id_peca']??0) ?>">
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:1rem">
      <div>
        <label class="am-label">Código *</label>
        <input class="am-input" name="codigo" required value="<?= htmlspecialchars($peca['codigo']??'') ?>">
      </div>
      <div>
        <label class="am-label">Descrição *</label>
        <input class="am-input" name="descricao" required value="<?= htmlspecialchars($peca['descricao']??'') ?>">
      </div>
      <div>
        <label class="am-label">Preço Custo (R$)</label>
        <input class="am-input" type="number" step="0.01" min="0" name="preco_custo" value="<?= number_format((float)($peca['preco_custo']??0),2,'.','') ?>">
      </div>
      <div>
        <label class="am-label">Preço Venda (R$)</label>
        <input class="am-input" type="number" step="0.01" min="0" name="preco_venda" value="<?= number_format((float)($peca['preco_venda']??0),2,'.','') ?>">
      </div>
      <div>
        <label class="am-label">Estoque Atual</label>
        <input class="am-input" type="number" name="estoque_atual" value="<?= (int)($peca['estoque_atual']??0) ?>">
      </div>
      <div>
        <label class="am-label">Estoque Mínimo</label>
        <input class="am-input" type="number" min="0" name="estoque_minimo" value="<?= (int)($peca['estoque_minimo']??5) ?>">
      </div>
      <div style="grid-column:1/-1">
        <label class="am-label">Fornecedor</label>
        <input class="am-input" name="fornecedor" value="<?= htmlspecialchars($peca['fornecedor']??'') ?>">
      </div>
    </div>
    <div style="margin-top:1.5rem;display:flex;gap:.75rem">
      <button class="am-btn am-btn-primary" type="submit"><i class="bi bi-check-lg"></i> Salvar</button>
      <a href="<?= BASE_URL ?>pecas.php" class="am-btn am-btn-muted">Cancelar</a>
    </div>
  </form>
</div>
<?php renderFooter(); ?>
