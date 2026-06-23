<?php renderHeader('Relatórios'); ?>
<div class="page-title">Relatórios Gerenciais</div>

<!-- KPIs do mês -->
<div class="stat-grid" style="grid-template-columns:repeat(3,1fr)">
  <div class="stat-card">
    <div class="stat-icon" style="color:var(--am-green)">💰</div>
    <div class="stat-num" style="color:var(--am-green);font-size:1.6rem">R$ <?= number_format((float)$mes_atual['fat'],2,',','.') ?></div>
    <div class="stat-label">Faturamento — <?= date('M/Y') ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="color:var(--am-blue)">📋</div>
    <div class="stat-num" style="color:var(--am-blue)"><?= $mes_atual['total'] ?></div>
    <div class="stat-label">OS Finalizadas no Mês</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="color:var(--am-yellow)">📊</div>
    <div class="stat-num" style="color:var(--am-yellow);font-size:1.6rem">
      R$ <?= $mes_atual['total'] > 0 ? number_format((float)$mes_atual['fat']/$mes_atual['total'],2,',','.') : '0,00' ?>
    </div>
    <div class="stat-label">Ticket Médio do Mês</div>
  </div>
</div>

<!-- Gráfico faturamento 12 meses -->
<div class="am-card">
  <div class="am-card-header"><i class="bi bi-bar-chart-fill" style="color:var(--am-accent)"></i> Faturamento — Últimos 12 Meses</div>
  <canvas id="graficoFaturamento" height="90"></canvas>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.25rem">
  <!-- Gráfico OS por mês (linha) -->
  <div class="am-card" style="margin-bottom:0">
    <div class="am-card-header"><i class="bi bi-graph-up" style="color:var(--am-blue)"></i> Ordens Finalizadas por Mês</div>
    <canvas id="graficoOS" height="120"></canvas>
  </div>
  <!-- Donut status -->
  <div class="am-card" style="margin-bottom:0">
    <div class="am-card-header"><i class="bi bi-pie-chart-fill" style="color:var(--am-green)"></i> OS por Status</div>
    <canvas id="graficoStatus" height="180"></canvas>
  </div>
</div>

<!-- Top peças -->
<div class="am-card" style="margin-top:1.25rem">
  <div class="am-card-header"><i class="bi bi-gear-fill" style="color:var(--am-yellow)"></i> Top Peças Mais Utilizadas</div>
  <table class="am-table">
    <thead><tr><th>#</th><th>Descrição</th><th>Qtd. Usada</th><th>Receita Gerada</th><th>% do total</th></tr></thead>
    <tbody>
    <?php
    $receita_total = array_sum(array_column($top_pecas, 'receita'));
    foreach ($top_pecas as $i => $p):
        $pct = $receita_total > 0 ? ($p['receita'] / $receita_total * 100) : 0;
    ?>
    <tr>
      <td style="color:var(--am-muted)"><?= $i + 1 ?></td>
      <td><strong><?= htmlspecialchars($p['descricao']) ?></strong></td>
      <td style="text-align:center"><?= $p['qtd_usada'] ?></td>
      <td style="color:var(--am-green)">R$ <?= number_format((float)$p['receita'],2,',','.') ?></td>
      <td>
        <div style="display:flex;align-items:center;gap:.5rem">
          <div style="flex:1;background:rgba(255,255,255,.08);border-radius:999px;height:6px">
            <div style="width:<?= round($pct) ?>%;background:var(--am-accent);height:6px;border-radius:999px;transition:width .4s"></div>
          </div>
          <span style="font-size:.78rem;color:var(--am-muted);min-width:35px"><?= number_format($pct,1) ?>%</span>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Histórico mensal tabela -->
<div class="am-card">
  <div class="am-card-header"><i class="bi bi-calendar3" style="color:var(--am-muted)"></i> Histórico Mensal Detalhado</div>
  <table class="am-table">
    <thead><tr><th>Mês</th><th>OS Finalizadas</th><th>Faturamento</th><th>Ticket Médio</th></tr></thead>
    <tbody>
    <?php foreach (array_reverse($meses_completos) as $m): ?>
    <tr>
      <td><?= $m['label'] ?></td>
      <td style="text-align:center"><?= $m['total_os'] ?></td>
      <td style="color:<?= $m['faturamento'] > 0 ? 'var(--am-green)' : 'var(--am-muted)' ?>">
        R$ <?= number_format($m['faturamento'],2,',','.') ?>
      </td>
      <td style="color:var(--am-muted)">
        <?= $m['total_os'] > 0 ? 'R$ '.number_format($m['faturamento']/$m['total_os'],2,',','.') : '—' ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#8b949e';
Chart.defaults.borderColor = 'rgba(48,54,61,0.8)';
Chart.defaults.font.family = "'Sora', sans-serif";

const labels  = <?= $labels ?>;
const fatData = <?= $fat_data ?>;
const osData  = <?= $os_data ?>;

// ── Barras: Faturamento ──
new Chart(document.getElementById('graficoFaturamento'), {
  type: 'bar',
  data: {
    labels,
    datasets: [{
      label: 'Faturamento (R$)',
      data: fatData,
      backgroundColor: 'rgba(247,129,102,.75)',
      borderColor: '#f78166',
      borderWidth: 1,
      borderRadius: 6,
      hoverBackgroundColor: '#f78166',
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => ' R$ ' + ctx.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits:2})
        }
      }
    },
    scales: {
      y: {
        ticks: { callback: v => 'R$ ' + v.toLocaleString('pt-BR') },
        grid: { color: 'rgba(48,54,61,.6)' }
      },
      x: { grid: { display: false } }
    }
  }
});

// ── Linha: OS finalizadas ──
new Chart(document.getElementById('graficoOS'), {
  type: 'line',
  data: {
    labels,
    datasets: [{
      label: 'OS Finalizadas',
      data: osData,
      borderColor: '#58a6ff',
      backgroundColor: 'rgba(88,166,255,.12)',
      fill: true,
      tension: .4,
      pointBackgroundColor: '#58a6ff',
      pointRadius: 4,
      pointHoverRadius: 7,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(48,54,61,.6)' } },
      x: { grid: { display: false } }
    }
  }
});

// ── Donut: Status ──
const statusLabels = <?= $status_labels ?>;
const statusData   = <?= $status_data ?>;
const statusColors = {
  'Finalizada':      '#3fb950',
  'Cancelada':       '#8b949e',
  'Em andamento':    '#58a6ff',
  'Aguardando peca': '#d29922',
  'Aberta':          '#f78166',
};
new Chart(document.getElementById('graficoStatus'), {
  type: 'doughnut',
  data: {
    labels: statusLabels,
    datasets: [{
      data: statusData,
      backgroundColor: statusLabels.map(l => statusColors[l] ?? '#666'),
      borderColor: '#161b22',
      borderWidth: 3,
      hoverOffset: 8,
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom', labels: { padding: 14, boxWidth: 12 } }
    }
  }
});
</script>
<?php renderFooter(); ?>
