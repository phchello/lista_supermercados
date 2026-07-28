<div class="mb-4">
    <a href="?route=lists/history" class="btn btn-sm btn-outline-secondary btn-modern mb-3"><i class="bi bi-arrow-left"></i> Voltar para o Histórico</a>
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h3 class="fw-bold mb-1">Cupom da Compra #<?= $purchase['id'] ?></h3>
            <p class="text-muted mb-0">Registrado em <?= date('d/m/Y', strtotime($purchase['purchase_date'])) ?></p>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Resumo do Cupom -->
    <div class="col-md-4">
        <div class="card card-premium p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Resumo da Transação</h5>
            
            <div class="mb-3">
                <span class="text-muted small d-block">Supermercado</span>
                <span class="fw-bold fs-5 text-gradient"><i class="bi bi-shop me-1"></i><?= htmlspecialchars($purchase['market_name']) ?></span>
            </div>

            <div class="mb-3">
                <span class="text-muted small d-block">Data do Registro</span>
                <span class="fw-semibold"><?= date('d/m/Y H:i', strtotime($purchase['created_at'])) ?></span>
            </div>

            <div class="mb-3">
                <span class="text-muted small d-block">Total Pago</span>
                <span class="fw-bold fs-4 text-main">R$ <?= number_format($purchase['total_value'], 2, ',', '.') ?></span>
            </div>

            <div class="mb-3">
                <span class="text-muted small d-block">Economia Declarada</span>
                <span class="badge bg-success fs-6">R$ <?= number_format($purchase['savings'], 2, ',', '.') ?> economizados</span>
            </div>
        </div>
    </div>

    <!-- Tabela de Itens Comprados -->
    <div class="col-md-8">
        <div class="card card-premium p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-cart text-primary me-2"></i>Itens do Cupom</h5>
            
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Marca</th>
                            <th>Categoria</th>
                            <th class="text-center">Quantidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">Nenhum item registrado para esta compra.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <span class="fw-semibold"><?= htmlspecialchars($item['product_name'] ?? $item['name'] ?? 'Produto') ?></span>
                                        <?php if (!empty($item['observation'])): ?>
                                            <div class="text-muted small italic">Obs: <?= htmlspecialchars($item['observation']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis"><?= htmlspecialchars($item['brand_name'] ?? 'Sem Marca') ?></span>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?= htmlspecialchars($item['category_name'] ?? 'Sem Categoria') ?></span>
                                    </td>
                                    <td class="text-center fw-bold">
                                        <?= number_format($item['quantity'], 1, ',', '.') ?>
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
