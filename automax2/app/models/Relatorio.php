<?php
declare(strict_types=1);

/**
 * Model Relatorio
 *
 * Encapsula todas as queries analíticas do sistema AutoMax.
 * SRP: somente leitura/agregação — nenhuma escrita no banco.
 */
class Relatorio
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ── Faturamento / OS dos últimos 12 meses ────────────────────────

    /**
     * Retorna faturamento e total de OS por mês nos últimos 12 meses,
     * garantindo todos os meses mesmo sem dados.
     *
     * @return array{meses: array, labels: string, fat_data: string, os_data: string}
     */
    public function historico12Meses(): array
    {
        $rows = $this->pdo->query("
            SELECT
                DATE_FORMAT(data_fechamento, '%Y-%m') AS mes_key,
                DATE_FORMAT(data_fechamento, '%b/%Y')  AS mes_label,
                COALESCE(SUM(valor_total), 0)           AS faturamento,
                COUNT(*)                                AS total_os
            FROM ORDEM_SERVICO
            WHERE status = 'Finalizada'
              AND data_fechamento >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
            GROUP BY mes_key, mes_label
            ORDER BY mes_key ASC
        ")->fetchAll();

        // Garante os 12 meses mesmo sem dados
        $meses = [];
        for ($i = 11; $i >= 0; $i--) {
            $key         = date('Y-m', strtotime("-$i months"));
            $label       = date('M/Y', strtotime("-$i months"));
            $meses[$key] = ['label' => $label, 'faturamento' => 0.0, 'total_os' => 0];
        }
        foreach ($rows as $r) {
            $meses[$r['mes_key']]['faturamento'] = (float)$r['faturamento'];
            $meses[$r['mes_key']]['total_os']    = (int)$r['total_os'];
        }

        return [
            'meses'    => $meses,
            'labels'   => json_encode(array_column($meses, 'label')),
            'fat_data' => json_encode(array_column($meses, 'faturamento')),
            'os_data'  => json_encode(array_column($meses, 'total_os')),
        ];
    }

    // ── KPIs do mês atual ────────────────────────────────────────────

    /**
     * Faturamento e total de OS finalizadas no mês corrente.
     */
    public function kpisMesAtual(): array
    {
        return $this->pdo->query("
            SELECT COALESCE(SUM(valor_total), 0) AS fat,
                   COUNT(*)                       AS total
            FROM ORDEM_SERVICO
            WHERE status = 'Finalizada'
              AND MONTH(data_fechamento) = MONTH(CURDATE())
              AND YEAR(data_fechamento)  = YEAR(CURDATE())
        ")->fetch();
    }

    // ── Top peças mais utilizadas ────────────────────────────────────

    /**
     * As 8 peças com maior volume de uso em todas as OS.
     */
    public function topPecas(int $limite = 8): array
    {
        return $this->pdo->query("
            SELECT p.descricao,
                   SUM(i.quantidade)              AS qtd_usada,
                   SUM(i.quantidade * i.preco_unit) AS receita
            FROM ITEM_OS i
            JOIN PECA p ON p.id_peca = i.id_peca
            GROUP BY p.id_peca, p.descricao
            ORDER BY qtd_usada DESC
            LIMIT $limite
        ")->fetchAll();
    }

    // ── OS por status ─────────────────────────────────────────────────

    /**
     * Contagem de OS agrupada por status e dados para Chart.js.
     *
     * @return array{rows: array, labels: string, data: string}
     */
    public function osPorStatus(): array
    {
        $rows = $this->pdo->query("
            SELECT status, COUNT(*) AS total
            FROM ORDEM_SERVICO
            GROUP BY status
        ")->fetchAll();

        return [
            'rows'   => $rows,
            'labels' => json_encode(array_column($rows, 'status')),
            'data'   => json_encode(array_column($rows, 'total')),
        ];
    }
}
