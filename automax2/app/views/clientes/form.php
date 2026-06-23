<?php renderHeader($cliente && ($cliente['id_cliente'] ?? 0) ? 'Editar Cliente' : 'Novo Cliente'); ?>
<div class="page-title">
  <?= $cliente && ($cliente['id_cliente'] ?? 0) ? 'Editar Cliente' : 'Novo Cliente' ?>
  <a href="<?= BASE_URL ?>clientes.php" class="am-btn am-btn-muted am-btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>
<div class="am-card" style="max-width:680px">
  <form method="POST">
    <input type="hidden" name="id" value="<?= (int)($cliente['id_cliente'] ?? 0) ?>">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
      <div style="grid-column:1/-1">
        <label class="am-label">Nome Completo *</label>
        <input class="am-input" name="nome" required value="<?= htmlspecialchars($cliente['nome'] ?? '') ?>">
      </div>
      <div>
        <label class="am-label">CPF *</label>
        <input class="am-input" id="cpf" name="cpf" maxlength="14" required placeholder="000.000.000-00"
               value="<?= htmlspecialchars($cliente['cpf'] ?? '') ?>">
      </div>
      <div>
        <label class="am-label">Celular</label>
        <input class="am-input" id="celular" name="celular" maxlength="15" placeholder="(00) 00000-0000"
               value="<?= htmlspecialchars($cliente['celular'] ?? '') ?>">
      </div>
      <div>
        <label class="am-label">Telefone</label>
        <input class="am-input" id="telefone" name="telefone" maxlength="14" placeholder="(00) 0000-0000"
               value="<?= htmlspecialchars($cliente['telefone'] ?? '') ?>">
      </div>
      <div>
        <label class="am-label">E-mail</label>
        <input class="am-input" type="email" name="email" value="<?= htmlspecialchars($cliente['email'] ?? '') ?>">
      </div>
      <div style="grid-column:1/-1">
        <label class="am-label">Endereço</label>
        <input class="am-input" name="endereco" value="<?= htmlspecialchars($cliente['endereco'] ?? '') ?>">
      </div>
    </div>
    <div style="margin-top:1.5rem;display:flex;gap:.75rem">
      <button class="am-btn am-btn-primary" type="submit"><i class="bi bi-check-lg"></i> Salvar</button>
      <a href="<?= BASE_URL ?>clientes.php" class="am-btn am-btn-muted">Cancelar</a>
    </div>
  </form>
</div>
<script>
function mask(el, fn){ el.addEventListener('input', () => el.value = fn(el.value)); }
function maskCPF(v){ v=v.replace(/\D/g,'').slice(0,11);
  return v.replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d)/,'$1.$2').replace(/(\d{3})(\d{1,2})$/,'$1-$2'); }
function maskFone(v){ v=v.replace(/\D/g,'').slice(0,11);
  if(v.length<=10) return v.replace(/(\d{2})(\d{4})(\d)/,'($1) $2-$3');
  return v.replace(/(\d{2})(\d{5})(\d)/,'($1) $2-$3'); }
mask(document.getElementById('cpf'), maskCPF);
mask(document.getElementById('celular'), maskFone);
mask(document.getElementById('telefone'), maskFone);
</script>
<?php renderFooter(); ?>
