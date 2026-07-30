<div class="mb-4">
    <a href="?route=products" class="btn btn-sm btn-outline-secondary btn-modern mb-3"><i class="bi bi-arrow-left"></i> Voltar para Produtos</a>
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h3 class="fw-bold mb-1"><?= htmlspecialchars($product['name']) ?></h3>
            <p class="text-muted mb-0">
                <span class="me-3"><strong>EAN:</strong> <?= $product['ean'] ?: 'N/D' ?></span>
                <span class="me-3"><strong>Marca:</strong> <?= htmlspecialchars($product['brand_name'] ?? 'Sem Marca') ?></span>
                <span><strong>Categoria:</strong> <?= htmlspecialchars($product['category_name'] ?? 'Sem Categoria') ?></span>
            </p>
        </div>
    </div>
</div>

<!-- Grid de Indicadores de Histórico -->
<?php
$prices = array_column(array_filter($latestPrices, function($p) { return $p['price'] !== null; }), 'price');
$minPrice = !empty($prices) ? min($prices) : null;
$maxPrice = !empty($prices) ? max($prices) : null;
$avgPrice = !empty($prices) ? array_sum($prices) / count($prices) : null;
$variation = ($minPrice && $maxPrice && $minPrice > 0) ? (($maxPrice - $minPrice) / $minPrice) * 100 : 0;
?>

<div class="row g-4 mb-4">
    <!-- Menor Preço -->
    <div class="col-md-3">
        <div class="card card-premium p-3 bg-success-subtle border-success-subtle text-success-emphasis">
            <h6 class="text-uppercase fw-semibold mb-1" style="font-size: 0.75rem;">Menor Preço Atual</h6>
            <h3 class="mb-0 fw-bold"><?= $minPrice ? 'R$ ' . number_format($minPrice, 2, ',', '.') : 'Sem dados' ?></h3>
        </div>
    </div>
    <!-- Maior Preço -->
    <div class="col-md-3">
        <div class="card card-premium p-3 bg-danger-subtle border-danger-subtle text-danger-emphasis">
            <h6 class="text-uppercase fw-semibold mb-1" style="font-size: 0.75rem;">Maior Preço Atual</h6>
            <h3 class="mb-0 fw-bold"><?= $maxPrice ? 'R$ ' . number_format($maxPrice, 2, ',', '.') : 'Sem dados' ?></h3>
        </div>
    </div>
    <!-- Média -->
    <div class="col-md-3">
        <div class="card card-premium p-3 bg-info-subtle border-info-subtle text-info-emphasis">
            <h6 class="text-uppercase fw-semibold mb-1" style="font-size: 0.75rem;">Preço Médio</h6>
            <h3 class="mb-0 fw-bold"><?= $avgPrice ? 'R$ ' . number_format($avgPrice, 2, ',', '.') : 'Sem dados' ?></h3>
        </div>
    </div>
    <!-- Diferença/Oscilação -->
    <div class="col-md-3">
        <div class="card card-premium p-3 bg-warning-subtle border-warning-subtle text-warning-emphasis">
            <h6 class="text-uppercase fw-semibold mb-1" style="font-size: 0.75rem;">Variação (Mínimo vs Máximo)</h6>
            <h3 class="mb-0 fw-bold"><?= $minPrice ? '+' . number_format($variation, 2, ',', '.') . '%' : '0%' ?></h3>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Preço por Supermercado -->
    <div class="col-md-5">
        <div class="card card-premium p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-shop text-primary me-2"></i>Preços Atuais por Mercado</h5>
            <div class="list-group list-group-flush">
                <?php foreach ($latestPrices as $lp): ?>
                    <div class="list-group-item bg-transparent px-0 py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shop text-muted fs-4"></i>
                            <div>
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($lp['market_name']) ?></h6>
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    <?= $lp['collected_at'] ? 'Coleta em: ' . date('d/m/Y H:i', strtotime($lp['collected_at'])) : 'Sem coleta registrada' ?>
                                </small>
                            </div>
                        </div>
                        <div class="text-end d-flex align-items-center gap-3">
                            <div>
                                <?php if ($lp['price'] !== null): ?>
                                    <span class="fs-5 fw-bold <?= $lp['price'] == $minPrice ? 'text-success' : 'text-main' ?>">
                                        R$ <?= number_format($lp['price'], 2, ',', '.') ?>
                                    </span>
                                    <?php if ($lp['is_promotion']): ?>
                                        <div class="badge bg-danger small d-block">PROMOÇÃO (-<?= round($lp['discount_percentage']) ?>%)</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted small">Sem preço</span>
                                <?php endif; ?>
                            </div>
                            <a href="<?= htmlspecialchars($lp['search_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary btn-modern" title="Ver produto no site do mercado">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Gráfico de Evolução dos Preços -->
    <div class="col-md-7">
        <div class="card card-premium p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="bi bi-graph-up text-primary me-2"></i>Histórico e Evolução de Preços (30 dias)</h5>
            <div style="height: 350px;">
                <canvas id="productHistoryChart" data-product-id="<?= $product['id'] ?>"></canvas>
            </div>
        </div>
    </div>
</div>
