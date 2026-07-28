<div class="row g-4 mb-4">
    <!-- Card: Total Produtos -->
    <div class="col-md-3">
        <div class="card card-premium h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.8rem;">Produtos Cadastrados</h6>
                    <h3 class="mb-0 fw-bold"><?= $stats['total_products'] ?></h3>
                </div>
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Card: Mercados Ativos -->
    <div class="col-md-3">
        <div class="card card-premium h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.8rem;">Mercados Ativos</h6>
                    <h3 class="mb-0 fw-bold"><?= $stats['total_markets'] ?></h3>
                </div>
                <div class="stat-icon bg-success-subtle text-success">
                    <i class="bi bi-shop"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Card: Atualizados Hoje -->
    <div class="col-md-3">
        <div class="card card-premium h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.8rem;">Preços Atualizados Hoje</h6>
                    <h3 class="mb-0 fw-bold"><?= $stats['updates_today'] ?></h3>
                </div>
                <div class="stat-icon bg-info-subtle text-info">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Card: Ofertas Ativas -->
    <div class="col-md-3">
        <div class="card card-premium h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.8rem;">Ofertas e Promoções</h6>
                    <h3 class="mb-0 fw-bold"><?= $stats['promotions_today'] ?></h3>
                </div>
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-percent"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Gráficos de Inteligência -->
    <div class="col-md-6">
        <div class="card card-premium p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-calendar2-week text-primary me-2"></i>Melhor Dia da Semana para Compras</h5>
            <div style="height: 250px;">
                <canvas id="bestDayChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-premium p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-graph-up-arrow text-danger me-2"></i>Produtos com Maior Oscilação de Preço</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Mínimo</th>
                            <th>Máximo</th>
                            <th>Média</th>
                            <th>Variação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($priceOscillation)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Aguardando mais dados históricos...</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($priceOscillation as $item): ?>
                                <tr>
                                    <td>
                                        <a href="?route=products/detail&id=<?= $item['id'] ?>" class="fw-semibold text-decoration-none">
                                            <?= htmlspecialchars($item['product_name']) ?>
                                        </a>
                                    </td>
                                    <td>R$ <?= number_format($item['min_price'], 2, ',', '.') ?></td>
                                    <td>R$ <?= number_format($item['max_price'], 2, ',', '.') ?></td>
                                    <td>R$ <?= number_format($item['avg_price'], 2, ',', '.') ?></td>
                                    <td>
                                        <span class="badge bg-danger-subtle text-danger">
                                            <i class="bi bi-arrow-up-right me-1"></i>+<?= $item['variation_percentage'] ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Últimas Promoções -->
    <div class="col-md-8">
        <div class="card card-premium p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h5 class="fw-bold mb-0"><i class="bi bi-tags text-warning me-2"></i>Últimas Promoções Encontradas</h5>
                <a href="?route=products" class="btn btn-sm btn-outline-primary btn-modern">Ver todos</a>
            </div>
            
            <div class="row g-3">
                <?php if (empty($promotions)): ?>
                    <div class="col-12 text-center py-4 text-muted">
                        <i class="bi bi-emoji-smile fs-1"></i>
                        <p class="mt-2 mb-0">Nenhuma promoção encontrada nas últimas coletas.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($promotions as $promo): ?>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 bg-body-tertiary h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-danger mb-2">-<?= round($promo['discount_percentage']) ?>% DESCONTO</span>
                                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($promo['product_name']) ?></h6>
                                    <p class="text-muted small mb-2"><i class="bi bi-shop me-1"></i><?= htmlspecialchars($promo['market_name']) ?></p>
                                </div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span class="fs-4 fw-bold text-success">R$ <?= number_format($promo['price'], 2, ',', '.') ?></span>
                                    <span class="text-decoration-line-through text-muted small">R$ <?= number_format($promo['avg_historical_price'], 2, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mercados Monitorados -->
    <div class="col-md-4">
        <div class="card card-premium p-4 h-100">
            <h5 class="fw-bold mb-4"><i class="bi bi-buildings text-info me-2"></i>Supermercados</h5>
            <div class="list-group list-group-flush">
                <?php foreach ($markets as $m): ?>
                    <div class="list-group-item bg-transparent px-0 py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded bg-body-secondary p-2 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-shop fs-4 text-secondary"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($m['name']) ?></h6>
                                <p class="text-muted small mb-0"><?= $m['product_count'] ?> produtos monitorados</p>
                            </div>
                        </div>
                        <div class="text-end">
                            <?php if ($m['active']): ?>
                                <span class="badge bg-success-subtle text-success mb-1 d-inline-block">Ativo</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger mb-1 d-inline-block">Inativo</span>
                            <?php endif; ?>
                            <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                                <?= $m['last_update'] ? date('d/m H:i', strtotime($m['last_update'])) : 'Sem dados' ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para os gráficos do Dashboard -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Configura Gráfico do Melhor Dia da Semana
    const dayCtx = document.getElementById('bestDayChart');
    if (dayCtx) {
        const days = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        
        // PHP fornece os dados em array $bestDays
        const rawDaysData = <?= json_encode($bestDays) ?>;
        
        // Inicializa dados com nulos
        const chartData = [null, null, null, null, null, null, null];
        
        rawDaysData.forEach(row => {
            // DAYOFWEEK em MySQL vai de 1 (Domingo) a 7 (Sábado)
            chartData[row.day_num - 1] = parseFloat(row.avg_price).toFixed(2);
        });

        new Chart(dayCtx, {
            type: 'bar',
            data: {
                labels: days,
                datasets: [{
                    label: 'Preço Médio Cesta (R$)',
                    data: chartData,
                    backgroundColor: chartData.map((val, idx) => {
                        // Destaca o dia mais barato (menor valor não nulo)
                        const validVals = chartData.filter(v => v !== null);
                        const minVal = Math.min(...validVals);
                        return val == minVal ? '#198754' : '#0d6efd';
                    }),
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        title: { display: true, text: 'Preço Médio (R$)' }
                    }
                }
            }
        });
    }
});
</script>
