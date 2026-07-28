<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
    <div class="mb-3 mb-md-0">
        <a href="?route=lists" class="btn btn-sm btn-outline-secondary btn-modern mb-3"><i class="bi bi-arrow-left"></i> Voltar para Listas</a>
        <h3 class="fw-bold mb-1"><?= htmlspecialchars($list['name']) ?></h3>
        <p class="text-muted mb-0">Gerencie os itens da sua lista e compare onde é mais barato comprar.</p>
    </div>
    
    <!-- Ações de Relatório e Conclusão -->
    <div class="d-flex flex-wrap gap-2">
        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-modern dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-download me-1"></i> Exportar Relatório
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="?route=lists/detail&id=<?= $list['id'] ?>&export=csv"><i class="bi bi-filetype-csv me-2"></i>Exportar CSV</a></li>
                <li><a class="dropdown-item" href="?route=lists/detail&id=<?= $list['id'] ?>&export=xls"><i class="bi bi-filetype-xls me-2"></i>Exportar Excel (.xls)</a></li>
                <li><button class="dropdown-item" onclick="window.print()"><i class="bi bi-printer me-2"></i>Imprimir Lista</button></li>
            </ul>
        </div>
        
        <?php if (!empty($optimization['single_market_comparison'])): ?>
            <button type="button" class="btn btn-success btn-modern" data-bs-toggle="modal" data-bs-target="#savePurchaseModal">
                <i class="bi bi-check2-circle me-1"></i> Concluir Compra
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <!-- Esquerda: Adicionar Item e Gerenciar Itens -->
    <div class="col-md-5">
        <!-- Card Adicionar Item -->
        <div class="card card-premium p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle text-primary me-2"></i>Adicionar Item na Lista</h5>
            <form action="?route=lists/add-item" method="POST">
                <input type="hidden" name="list_id" value="<?= $list['id'] ?>">
                <input type="hidden" name="product_id" id="productIdInput" value="">
                
                <div class="mb-3 autocomplete-container">
                    <label for="productSearchInput" class="form-label">Nome do Produto</label>
                    <input type="text" id="productSearchInput" class="form-control" placeholder="Digite para buscar ou criar..." autocomplete="off" required>
                    <div class="autocomplete-suggestions" id="autocompleteSuggestions" style="display: none;"></div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label for="quantityInput" class="form-label">Quantidade</label>
                        <input type="number" name="quantity" id="quantityInput" class="form-control" value="1" step="0.1" min="0.1" required>
                    </div>
                    <div class="col-6">
                        <label for="obsInput" class="form-label">Observação</label>
                        <input type="text" name="observation" id="obsInput" class="form-control" placeholder="Ex: Marca preferida">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-modern">Adicionar Produto</button>
            </form>
            <div class="mt-2 text-center text-muted small">
                Não encontrou o produto? Cadastre-o primeiro na página de <a href="?route=products">Produtos</a>.
            </div>
        </div>

        <!-- Lista de Itens Cadastrados (Gerenciamento) -->
        <div class="card card-premium p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-list-stars text-primary me-2"></i>Itens Atuais (<?= count($optimization['split_comparison']['items']) ?>)</h5>
            
            <?php if (empty($optimization['split_comparison']['items'])): ?>
                <p class="text-muted text-center py-4">Adicione produtos acima para começar.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Qtd</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($optimization['split_comparison']['items'] as $item): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($item['product_name']) ?></div>
                                        <?php if (!empty($item['observation'])): ?>
                                            <span class="text-muted small italic" style="font-size: 0.75rem;">Obs: <?= htmlspecialchars($item['observation']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center fw-bold"><?= number_format($item['quantity'], 1, ',', '.') ?></td>
                                    <td class="text-end">
                                        <a href="?route=lists/remove-item&list_id=<?= $list['id'] ?>&product_id=<?= $item['product_id'] ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Remover item da lista?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Direita: Inteligência de Compras e Comparativos -->
    <div class="col-md-7">
        <!-- Navegação de Comparação -->
        <ul class="nav nav-pills mb-3 gap-2" id="pills-tab" role="tablist">
            <li class="nav-item-pill" role="presentation">
                <button class="btn btn-outline-primary btn-modern active" id="pills-single-tab" data-bs-toggle="pill" data-bs-target="#pills-single" type="button" role="tab" aria-controls="pills-single" aria-selected="true">
                    <i class="bi bi-buildings me-1"></i> Comparações por Mercado
                </button>
            </li>
            <li class="nav-item-pill" role="presentation">
                <button class="btn btn-outline-primary btn-modern" id="pills-split-tab" data-bs-toggle="pill" data-bs-target="#pills-split" type="button" role="tab" aria-controls="pills-split" aria-selected="false">
                    <i class="bi bi-lightning-charge me-1"></i> Inteligência: Roteiro Otimizado
                </button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            <!-- ABA 1: Mercado Único -->
            <div class="tab-pane fade show active" id="pills-single" role="tabpanel" aria-labelledby="pills-single-tab">
                <div class="card card-premium p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-trophy text-warning me-2"></i>Ranking: Comprar tudo no mesmo lugar</h5>
                    
                    <?php if (empty($optimization['single_market_comparison'])): ?>
                        <p class="text-muted text-center py-4">Nenhum preço coletado para os produtos cadastrados.</p>
                    <?php else: ?>
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Posição</th>
                                        <th>Supermercado</th>
                                        <th>Itens Encontrados</th>
                                        <th>Valor Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $rank = 1;
                                    $cheapestTotal = $optimization['single_market_comparison'][0]['total_cost'];
                                    foreach ($optimization['single_market_comparison'] as $mc): 
                                    ?>
                                        <tr class="<?= $rank === 1 ? 'table-success-subtle fw-bold' : '' ?>">
                                            <td>
                                                <?php if ($rank === 1): ?>
                                                    <span class="badge bg-success"><i class="bi bi-check-lg"></i> 1º Melhor</span>
                                                <?php else: ?>
                                                    <span class="text-muted ms-2"><?= $rank ?>º</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($mc['market_name']) ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?= $mc['items_found'] ?> de <?= $optimization['total_items'] ?></span>
                                                <?php if ($mc['items_missing'] > 0): ?>
                                                    <span class="badge bg-warning-subtle text-warning-emphasis" title="Usado preço médio de outros mercados para comparação"><?= $mc['items_missing'] ?> estimados</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="<?= $rank === 1 ? 'text-success' : 'text-main' ?>">
                                                    R$ <?= number_format($mc['total_cost'], 2, ',', '.') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php 
                                    $rank++;
                                    endforeach; 
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Detalhamento de cada mercado -->
                        <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-1"></i>Detalhamento dos preços em cada mercado</h6>
                        <div class="accordion" id="accordionMarkets">
                            <?php foreach ($optimization['single_market_comparison'] as $mc): ?>
                                <div class="accordion-item bg-transparent border-color-style mb-2">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed py-2 px-3 bg-transparent text-main" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMarket-<?= $mc['market_id'] ?>" aria-expanded="false">
                                            <div class="d-flex align-items-center justify-content-between w-100 pe-3">
                                                <span><?= htmlspecialchars($mc['market_name']) ?></span>
                                                <span class="fw-semibold">R$ <?= number_format($mc['total_cost'], 2, ',', '.') ?></span>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="collapseMarket-<?= $mc['market_id'] ?>" class="accordion-collapse collapse" data-bs-parent="#accordionMarkets">
                                        <div class="accordion-body p-0">
                                            <table class="table table-sm table-striped mb-0 text-muted" style="font-size: 0.85rem;">
                                                <thead>
                                                    <tr class="table-light">
                                                        <th class="ps-3">Produto</th>
                                                        <th>Qtd</th>
                                                        <th>Preço Unitário</th>
                                                        <th class="pe-3 text-end">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($mc['items_detail'] as $det): ?>
                                                        <tr>
                                                            <td class="ps-3">
                                                                <?= htmlspecialchars($det['product_name']) ?>
                                                                <?php if ($det['estimated']): ?>
                                                                    <span class="text-warning font-monospace" style="font-size: 0.7rem;">(estimado)</span>
                                                                <?php endif; ?>
                                                                <?php if ($det['is_promotion']): ?>
                                                                    <span class="badge bg-danger p-1" style="font-size: 0.65rem;">PROMO</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= $det['quantity'] ?></td>
                                                            <td>R$ <?= number_format($det['unit_price'], 2, ',', '.') ?></td>
                                                            <td class="pe-3 text-end">R$ <?= number_format($det['total_price'], 2, ',', '.') ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ABA 2: Compra Dividida (Inteligência de Compra) -->
            <div class="tab-pane fade" id="pills-split" role="tabpanel" aria-labelledby="pills-split-tab">
                <div class="card card-premium p-4">
                    <div class="text-center p-3 mb-4 rounded bg-success-subtle border-success text-success-emphasis">
                        <h6 class="text-uppercase fw-semibold mb-1" style="font-size: 0.75rem;">Custo Dividindo por Menor Preço</h6>
                        <h2 class="fw-bold mb-1">R$ <?= number_format($optimization['split_comparison']['total_cost'], 2, ',', '.') ?></h2>
                        
                        <?php if ($optimization['split_comparison']['potential_savings_vs_cheapest'] > 0): ?>
                            <div class="fs-6 fw-semibold">
                                <i class="bi bi-arrow-down-circle-fill me-1"></i>
                                Economia de <strong>R$ <?= number_format($optimization['split_comparison']['potential_savings_vs_cheapest'], 2, ',', '.') ?></strong> 
                                em relação a comprar tudo no mercado mais barato!
                            </div>
                        <?php else: ?>
                            <div class="small">
                                Comprar dividido resulta no mesmo valor que comprar no mercado único mais barato.
                            </div>
                        <?php endif; ?>
                    </div>

                    <h5 class="fw-bold mb-3"><i class="bi bi-map text-primary me-2"></i>Roteiro de Compras Otimizado</h5>
                    <div class="shopping-route">
                        <?php if (empty($optimization['split_comparison']['by_market'])): ?>
                            <p class="text-muted text-center py-3">Adicione itens na lista para traçar a melhor rota.</p>
                        <?php else: ?>
                            <?php foreach ($optimization['split_comparison']['by_market'] as $mId => $mGroup): ?>
                                <div class="step-item">
                                    <div class="step-marker"></div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="fw-bold mb-0 text-gradient fs-6"><?= htmlspecialchars($mGroup['market_name']) ?></h6>
                                        <span class="badge bg-primary">Subtotal: R$ <?= number_format($mGroup['total_cost'], 2, ',', '.') ?></span>
                                    </div>
                                    
                                    <ul class="list-group list-group-flush mb-2" style="font-size: 0.9rem;">
                                        <?php foreach ($mGroup['items'] as $item): ?>
                                            <li class="list-group-item bg-transparent py-2 px-0 d-flex justify-content-between text-muted">
                                                <span>
                                                    <strong><?= $item['quantity'] ?>x</strong> <?= htmlspecialchars($item['product_name']) ?>
                                                    <?php if ($item['is_promotion']): ?>
                                                        <span class="badge bg-danger p-1 ms-1" style="font-size: 0.65rem;">PROMO</span>
                                                    <?php endif; ?>
                                                </span>
                                                <span>R$ <?= number_format($item['total_price'], 2, ',', '.') ?> <span class="small" style="font-size: 0.75rem;">(R$ <?= number_format($item['unit_price'], 2, ',', '.') ?> un)</span></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Registrar / Concluir Compra -->
<div class="modal fade" id="savePurchaseModal" tabindex="-1" aria-labelledby="savePurchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="?route=lists/save-purchase" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="savePurchaseModalLabel">Registrar Compra Efetuada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="list_id" value="<?= $list['id'] ?>">
                    
                    <?php 
                    // Tenta adivinhar dados padrões do mercado mais barato
                    $bestMarketId = !empty($optimization['single_market_comparison']) ? $optimization['single_market_comparison'][0]['market_id'] : '';
                    $bestMarketCost = !empty($optimization['single_market_comparison']) ? $optimization['single_market_comparison'][0]['total_cost'] : 0.0;
                    $worstMarketCost = !empty($optimization['single_market_comparison']) ? end($optimization['single_market_comparison'])['total_cost'] : 0.0;
                    $defaultSavings = $worstMarketCost - $bestMarketCost;
                    ?>

                    <div class="mb-3">
                        <label for="purchaseMarket" class="form-label">Supermercado Onde Comprou <span class="text-danger">*</span></label>
                        <select name="market_id" id="purchaseMarket" class="form-select" required>
                            <option value="">Selecione o Mercado...</option>
                            <?php foreach ($markets as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= $m['id'] == $bestMarketId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="purchaseDate" class="form-label">Data da Compra</label>
                        <input type="date" name="purchase_date" id="purchaseDate" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="totalVal" class="form-label">Valor Total Pago (R$)</label>
                            <input type="number" name="total_value" id="totalVal" class="form-control" step="0.01" value="<?= round($bestMarketCost, 2) ?>" required>
                        </div>
                        <div class="col-6">
                            <label for="totalSavings" class="form-label">Economia Gerada (R$)</label>
                            <input type="number" name="savings" id="totalSavings" class="form-control" step="0.01" value="<?= round($defaultSavings, 2) ?>">
                        </div>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-1"></i> Ao salvar, os produtos da sua lista serão gravados em seu histórico de compras pessoal. A lista não será excluída, permitindo usá-la novamente no futuro.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-success">Registrar Compra</button>
                </div>
            </form>
        </div>
    </div>
</div>
