<div class="mb-4">
    <h3 class="fw-bold mb-1">Histórico de Compras Realizadas</h3>
    <p class="text-muted mb-0">Confira o histórico de todas as listas que você comprou e acompanhe a economia acumulada.</p>
</div>

<div class="card card-premium p-4">
    <?php if (empty($purchases)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-calendar-x fs-1"></i>
            <h5 class="mt-3 fw-bold">Nenhuma compra registrada</h5>
            <p>Conclua uma compra de suas listas de compras para começar a alimentar este histórico.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Lista de Origem</th>
                        <th>Supermercado</th>
                        <th>Valor Total Pago</th>
                        <th>Economia Gerada</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $p): ?>
                        <tr>
                            <td>
                                <span class="fw-semibold"><?= date('d/m/Y', strtotime($p['purchase_date'])) ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars($p['list_name'] ?? 'Compra Direta / Avulsa') ?>
                            </td>
                            <td>
                                <i class="bi bi-shop text-muted me-2"></i>
                                <?= htmlspecialchars($p['market_name']) ?>
                            </td>
                            <td>
                                <span class="fw-bold text-main">R$ <?= number_format($p['total_value'], 2, ',', '.') ?></span>
                            </td>
                            <td>
                                <?php if ($p['savings'] > 0): ?>
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="bi bi-chevron-double-down me-1"></i>R$ <?= number_format($p['savings'], 2, ',', '.') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="?route=lists/history-detail&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary btn-modern">
                                    <i class="bi bi-eye"></i> Detalhes
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
