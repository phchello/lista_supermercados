<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
    <div class="mb-3 mb-md-0">
        <a href="?route=lists" class="btn btn-sm btn-outline-secondary btn-modern mb-3"><i class="bi bi-arrow-left"></i> Voltar para Listas</a>
        <h3 class="fw-bold mb-1"><?= htmlspecialchars($list['name']) ?></h3>
        <p class="text-muted mb-0">Selecione itens genéricos e deixe o sistema escolher a melhor marca por preço e seu gosto.</p>
    </div>
    
    <!-- Ações de Relatório e Conclusão -->
    <div class="d-flex flex-wrap gap-2" id="listActionHeader">
        <button type="button" class="btn btn-outline-primary btn-modern" data-bs-toggle="modal" data-bs-target="#brandPreferencesModal">
            <i class="bi bi-star-fill me-1 text-warning"></i> Gosto por Marcas
        </button>

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
        
        <button type="button" class="btn btn-success btn-modern" id="concludePurchaseBtn" data-bs-toggle="modal" data-bs-target="#savePurchaseModal" style="display: none;">
            <i class="bi bi-check2-circle me-1"></i> Concluir Compra
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Esquerda: Buscar e Marcar Produtos -->
    <div class="col-md-5">
        <div class="card card-premium p-4 h-100 d-flex flex-column">
            <h5 class="fw-bold mb-3"><i class="bi bi-search text-primary me-2"></i>Buscar & Marcar Produtos</h5>
            
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="ajaxProductSearch" class="form-control" placeholder="Digite para buscar produtos...">
                </div>
                <div class="form-text">Busque produtos comuns (ex: arroz, leite) para marcar.</div>
            </div>

            <!-- Container da Lista de Seleção de Produtos -->
            <div class="product-selector-list flex-grow-1 overflow-y-auto pe-1" id="productSelectorContainer" style="max-height: 550px;">
                <!-- Injetado dinamicamente via JS -->
            </div>
        </div>
    </div>

    <!-- Direita: Sua Lista Atual e Comparações de Preço -->
    <div class="col-md-7">
        <!-- Abas de Visualização -->
        <ul class="nav nav-pills mb-3 gap-2" id="pills-tab" role="tablist">
            <li class="nav-item-pill" role="presentation">
                <button class="btn btn-outline-primary btn-modern active" id="pills-items-tab" data-bs-toggle="pill" data-bs-target="#pills-items" type="button" role="tab">
                    <i class="bi bi-cart"></i> Carrinho (<span id="cartCount">0</span>)
                </button>
            </li>
            <li class="nav-item-pill" role="presentation">
                <button class="btn btn-outline-primary btn-modern" id="pills-single-tab" data-bs-toggle="pill" data-bs-target="#pills-single" type="button" role="tab">
                    <i class="bi bi-buildings"></i> Comparativo por Mercado
                </button>
            </li>
            <li class="nav-item-pill" role="presentation">
                <button class="btn btn-outline-primary btn-modern" id="pills-split-tab" data-bs-toggle="pill" data-bs-target="#pills-split" type="button" role="tab">
                    <i class="bi bi-lightning-charge"></i> Roteiro Otimizado
                </button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            <!-- ABA 1: Itens na Lista -->
            <div class="tab-pane fade show active" id="pills-items" role="tabpanel">
                <div class="card card-premium p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-basket text-primary me-2"></i>Produtos Adicionados</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center" style="width: 150px;">Quantidade</th>
                                    <th class="text-end">Excluir</th>
                                </tr>
                            </thead>
                            <tbody id="cartTableBody">
                                <!-- Injetado dinamicamente via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ABA 2: Comparativo de Mercado Único -->
            <div class="tab-pane fade" id="pills-single" role="tabpanel">
                <div class="card card-premium p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-trophy text-warning me-2"></i>Ranking: Comprar tudo no mesmo mercado</h5>
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
                            <tbody id="rankingTableBody">
                                <!-- Injetado dinamicamente via JS -->
                            </tbody>
                        </table>
                    </div>

                    <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-1"></i>Preços Detalhados por Mercado</h6>
                    <div class="accordion" id="accordionMarkets">
                        <!-- Injetado dinamicamente via JS -->
                    </div>
                </div>
            </div>

            <!-- ABA 3: Roteiro Otimizado (Compra Dividida) -->
            <div class="tab-pane fade" id="pills-split" role="tabpanel">
                <div class="card card-premium p-4">
                    <div class="text-center p-3 mb-4 rounded bg-success-subtle border-success text-success-emphasis" id="splitSummaryCard">
                        <!-- Injetado dinamicamente via JS -->
                    </div>

                    <h5 class="fw-bold mb-3"><i class="bi bi-map text-primary me-2"></i>Roteiro de Compras</h5>
                    <div class="shopping-route" id="shoppingRouteContainer">
                        <!-- Injetado dinamicamente via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Preferências de Marcas -->
<div class="modal fade" id="brandPreferencesModal" tabindex="-1" aria-labelledby="brandPreferencesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="brandPreferencesModalLabel">
                    <i class="bi bi-star-fill text-warning me-1"></i> O quanto você gosta de cada marca?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    Defina sua preferência para cada marca. O otimizador prioriza as marcas que você gosta mais e evita as marcas que você gosta menos, mesmo que haja pequenas variações de preço.
                </p>
                <div class="list-group list-group-flush">
                    <?php foreach ($brands as $b): ?>
                        <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0 py-2 border-color-style">
                            <span class="fw-semibold text-main"><?= htmlspecialchars($b['name']) ?></span>
                            <select class="form-select form-select-sm brand-pref-select" style="width: 150px;" data-brand-id="<?= $b['id'] ?>">
                                <option value="5" <?= ($b['preference'] == 5) ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ Adoro</option>
                                <option value="4" <?= ($b['preference'] == 4) ? 'selected' : '' ?>>⭐⭐⭐⭐ Gosto</option>
                                <option value="3" <?= ($b['preference'] == 3) ? 'selected' : '' ?>>⭐⭐⭐ Neutro</option>
                                <option value="2" <?= ($b['preference'] == 2) ? 'selected' : '' ?>>⭐⭐ Não Gosto</option>
                                <option value="1" <?= ($b['preference'] == 1) ? 'selected' : '' ?>>⭐ Evito</option>
                            </select>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-modern" data-bs-dismiss="modal">Confirmar e Recalcular</button>
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
                    
                    <div class="mb-3">
                        <label for="purchaseMarket" class="form-label">Supermercado Onde Comprou <span class="text-danger">*</span></label>
                        <select name="market_id" id="purchaseMarket" class="form-select" required>
                            <option value="">Selecione o Mercado...</option>
                            <?php foreach ($markets as $m): ?>
                                <option value="<?= $m['id'] ?>">
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
                            <input type="number" name="total_value" id="totalVal" class="form-control" step="0.01" value="0.00" required>
                        </div>
                        <div class="col-6">
                            <label for="totalSavings" class="form-label">Economia Gerada (R$)</label>
                            <input type="number" name="savings" id="totalSavings" class="form-control" step="0.01" value="0.00">
                        </div>
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle me-1"></i> Ao salvar, a lista de compras atual será salva como um registro histórico.
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

<!-- Lógica JS Exclusiva para Construção Dinâmica da Lista -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const listId = <?= $list['id'] ?>;
    
    // Todos os produtos genéricos da base (sem marca) carregados na memória
    const dbProducts = <?= json_encode($allProducts) ?>;
    
    // Estado da Lista de Compras Atual (Itens Adicionados)
    let currentListItems = [];
    let optimizationData = null;

    // Elementos do DOM
    const searchInput = document.getElementById('ajaxProductSearch');
    const selectorContainer = document.getElementById('productSelectorContainer');
    const cartTableBody = document.getElementById('cartTableBody');
    const rankingTableBody = document.getElementById('rankingTableBody');
    const accordionMarkets = document.getElementById('accordionMarkets');
    const splitSummaryCard = document.getElementById('splitSummaryCard');
    const shoppingRouteContainer = document.getElementById('shoppingRouteContainer');
    const cartCountSpan = document.getElementById('cartCount');
    const concludeBtn = document.getElementById('concludePurchaseBtn');

    // Inicializa carregando a lista
    fetchListItems();

    // Evento de Digitação (Busca em Tempo Real na memória)
    searchInput.addEventListener('input', function () {
        renderProductSelector();
    });

    // Escuta alteração nas preferências de marca
    document.querySelectorAll('.brand-pref-select').forEach(select => {
        select.addEventListener('change', function () {
            const brandId = this.dataset.brandId;
            const preference = this.value;
            
            fetch('?route=api/brands/update-preference', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    brand_id: brandId,
                    preference: preference
                })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    // Recarrega itens para re-otimizar os cálculos com a nova preferência
                    fetchListItems();
                }
            });
        });
    });

    // Busca itens atuais da lista
    function fetchListItems() {
        fetch(`?route=api/lists/items&list_id=${listId}`)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    optimizationData = res.optimization;
                    currentListItems = res.optimization.split_comparison.items || [];
                    updateUI();
                }
            });
    }

    // Atualiza toda a Interface com base no estado atual da lista
    function updateUI() {
        // Atualiza contadores
        cartCountSpan.textContent = currentListItems.length;
        
        if (currentListItems.length > 0) {
            concludeBtn.style.display = 'inline-block';
        } else {
            concludeBtn.style.display = 'none';
        }

        renderProductSelector();
        renderCartTable();
        renderRankingTable();
        renderShoppingRoute();
        updateModalInputs();
    }

    // Renderiza a lista de seleção de produtos à esquerda
    function renderProductSelector() {
        const query = searchInput.value.toLowerCase().trim();
        let filtered = [];

        if (query === '') {
            // Se a busca estiver vazia, exibe 15 produtos genéricos de exemplos
            filtered = dbProducts.slice(0, 15);
        } else {
            // Caso contrário, filtra na memória
            filtered = dbProducts.filter(p => 
                p.name.toLowerCase().includes(query) || 
                (p.category_name && p.category_name.toLowerCase().includes(query)) ||
                (p.brands && p.brands.some(b => b && b.toLowerCase().includes(query)))
            ).slice(0, 30);
        }

        selectorContainer.innerHTML = '';

        if (filtered.length === 0) {
            selectorContainer.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-emoji-frown fs-2"></i>
                    <p class="mt-2 mb-0">Nenhum produto encontrado</p>
                </div>
            `;
            return;
        }

        filtered.forEach(product => {
            // Verifica se este produto genérico (qualquer um dos IDs de marca associados) já está na lista
            const addedItem = currentListItems.find(item => product.product_ids.includes(parseInt(item.product_id)));
            const isAdded = !!addedItem;
            const currentQty = addedItem ? parseFloat(addedItem.quantity) : 1.0;

            const div = document.createElement('div');
            div.className = `p-3 border rounded-3 mb-2 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 ${isAdded ? 'bg-primary-subtle border-primary-subtle' : 'bg-body-tertiary'}`;
            
            // Lista as marcas compatíveis
            const brandsList = product.brands.length > 0 ? product.brands.join(', ') : 'Marcas diversas';

            div.innerHTML = `
                <div>
                    <h6 class="fw-bold mb-0 text-main">${product.name}</h6>
                    <span class="text-muted small d-block mb-1" style="font-size: 0.75rem; line-height: 1.1;">
                        Marcas compatíveis: <strong>${brandsList}</strong>
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 0.65rem;">
                        ${product.category_name || 'Sem Categoria'}
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2 justify-content-end">
                    <!-- Seletor de Quantidade -->
                    <div class="input-group input-group-sm" style="width: 100px;">
                        <button class="btn btn-outline-secondary btn-qty-minus" type="button" data-id="${product.id}">-</button>
                        <input type="number" class="form-control text-center input-qty" value="${currentQty}" min="0.1" step="0.1" data-id="${product.id}">
                        <button class="btn btn-outline-secondary btn-qty-plus" type="button" data-id="${product.id}">+</button>
                    </div>

                    <!-- Botão de Ação -->
                    ${isAdded ? 
                        `<button class="btn btn-sm btn-danger btn-remove-item" data-id="${addedItem.product_id}">
                            <i class="bi text-main bi-dash-circle"></i> Remover
                         </button>` : 
                        `<button class="btn btn-sm btn-primary btn-add-item" data-id="${product.id}">
                            <i class="bi text-main bi-plus-circle"></i> Adicionar
                         </button>`
                    }
                </div>
            `;

            // Eventos dos botões de controle de quantidade da esquerda
            const qtyInput = div.querySelector('.input-qty');
            
            div.querySelector('.btn-qty-minus').addEventListener('click', () => {
                let val = Math.max(0.1, parseFloat(qtyInput.value) - 1.0);
                qtyInput.value = val.toFixed(1);
                if (isAdded) {
                    updateItemQuantity(addedItem.product_id, val);
                }
            });

            div.querySelector('.btn-qty-plus').addEventListener('click', () => {
                let val = parseFloat(qtyInput.value) + 1.0;
                qtyInput.value = val.toFixed(1);
                if (isAdded) {
                    updateItemQuantity(addedItem.product_id, val);
                }
            });

            qtyInput.addEventListener('change', () => {
                let val = Math.max(0.1, parseFloat(qtyInput.value) || 1.0);
                qtyInput.value = val.toFixed(1);
                if (isAdded) {
                    updateItemQuantity(addedItem.product_id, val);
                }
            });

            // Ações de Adicionar / Remover
            const addBtn = div.querySelector('.btn-add-item');
            if (addBtn) {
                addBtn.addEventListener('click', () => {
                    const qty = parseFloat(qtyInput.value);
                    addItem(product.id, qty);
                });
            }

            const removeBtn = div.querySelector('.btn-remove-item');
            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    removeItem(addedItem.product_id);
                });
            }

            selectorContainer.appendChild(div);
        });
    }

    // Renderiza a tabela de Itens Adicionados (Carrinho) à direita
    function renderCartTable() {
        cartTableBody.innerHTML = '';
        
        if (currentListItems.length === 0) {
            cartTableBody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center py-4 text-muted">Nenhum produto adicionado à lista. Marque itens na lista ao lado.</td>
                </tr>
            `;
            return;
        }

        currentListItems.forEach(item => {
            // Encontra o produto genérico correspondente para mostrar o nome genérico limpo
            const genProduct = dbProducts.find(p => p.product_ids.includes(parseInt(item.product_id)));
            const displayName = genProduct ? genProduct.name : item.product_name;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="fw-bold">${displayName}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">
                        ${item.category_name || 'Sem Categoria'}
                    </span>
                </td>
                <td class="text-center">
                    <div class="input-group input-group-sm m-auto" style="width: 100px;">
                        <button class="btn btn-outline-secondary btn-cart-minus" type="button">-</button>
                        <input type="number" class="form-control text-center input-cart-qty" value="${parseFloat(item.quantity)}" min="0.1" step="0.1">
                        <button class="btn btn-outline-secondary btn-cart-plus" type="button">+</button>
                    </div>
                </td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-danger btn-cart-delete" type="button">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;

            const qtyInput = tr.querySelector('.input-cart-qty');

            tr.querySelector('.btn-cart-minus').addEventListener('click', () => {
                let val = Math.max(0.1, parseFloat(qtyInput.value) - 1.0);
                qtyInput.value = val.toFixed(1);
                updateItemQuantity(item.product_id, val);
            });

            tr.querySelector('.btn-cart-plus').addEventListener('click', () => {
                let val = parseFloat(qtyInput.value) + 1.0;
                qtyInput.value = val.toFixed(1);
                updateItemQuantity(item.product_id, val);
            });

            qtyInput.addEventListener('change', () => {
                let val = Math.max(0.1, parseFloat(qtyInput.value) || 1.0);
                qtyInput.value = val.toFixed(1);
                updateItemQuantity(item.product_id, val);
            });

            tr.querySelector('.btn-cart-delete').addEventListener('click', () => {
                removeItem(item.product_id);
            });

            cartTableBody.appendChild(tr);
        });
    }

    // Renderiza a aba de Comparativos por Mercado
    function renderRankingTable() {
        rankingTableBody.innerHTML = '';
        accordionMarkets.innerHTML = '';

        if (!optimizationData || !optimizationData.single_market_comparison || optimizationData.single_market_comparison.length === 0) {
            rankingTableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-3 text-muted">Aguardando itens para gerar ranking...</td>
                </tr>
            `;
            return;
        }

        let rank = 1;
        optimizationData.single_market_comparison.forEach(mc => {
            const tr = document.createElement('tr');
            tr.className = rank === 1 ? 'table-success-subtle fw-bold' : '';
            tr.innerHTML = `
                <td>
                    ${rank === 1 ? 
                        '<span class="badge bg-success"><i class="bi bi-check-lg"></i> 1º Melhor</span>' : 
                        `<span class="text-muted ms-2">${rank}º</span>`
                    }
                </td>
                <td>${mc.market_name}</td>
                <td>
                    <span class="badge bg-secondary">${mc.items_found} de ${optimizationData.total_items}</span>
                    ${mc.items_missing > 0 ? 
                        `<span class="badge bg-warning-subtle text-warning-emphasis" title="Média de outros mercados usada para itens sem preço">+${mc.items_missing} estimados</span>` : 
                        ''
                    }
                </td>
                <td>
                    <span class="${rank === 1 ? 'text-success' : 'text-main'}">
                        R$ ${parseFloat(mc.total_cost).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                    </span>
                </td>
            `;
            rankingTableBody.appendChild(tr);

            // Accordion detail item
            const accId = `collapseMarket-${mc.market_id}`;
            const accItem = document.createElement('div');
            accItem.className = 'accordion-item bg-transparent border-color-style mb-2';
            
            let detailsRows = '';
            mc.items_detail.forEach(det => {
                // Desenha estrelas da nota da marca
                const stars = '⭐'.repeat(det.preference_score || 3);
                
                detailsRows += `
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold">${det.product_name}</div>
                            <span class="text-muted small d-block" style="font-size:0.7rem;">
                                Marca: ${det.brand_name} (${stars})
                                ${det.estimated ? '<span class="text-warning font-monospace">(estimado)</span>' : ''}
                                ${det.is_promotion ? '<span class="badge bg-danger p-1 ms-1" style="font-size: 0.6rem;">PROMO</span>' : ''}
                            </span>
                        </td>
                        <td>${parseFloat(det.quantity).toLocaleString('pt-BR')}</td>
                        <td>R$ ${parseFloat(det.unit_price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td class="pe-3 text-end">R$ ${parseFloat(det.total_price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    </tr>
                `;
            });

            accItem.innerHTML = `
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed py-2 px-3 bg-transparent text-main" type="button" data-bs-toggle="collapse" data-bs-target="#${accId}" aria-expanded="false">
                        <div class="d-flex align-items-center justify-content-between w-100 pe-3">
                            <span>${mc.market_name}</span>
                            <span class="fw-semibold">R$ ${parseFloat(mc.total_cost).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                        </div>
                    </button>
                </h2>
                <div id="${accId}" class="accordion-collapse collapse" data-bs-parent="#accordionMarkets">
                    <div class="accordion-body p-0">
                        <table class="table table-sm table-striped mb-0 text-muted" style="font-size: 0.85rem;">
                            <thead>
                                <tr class="table-light">
                                    <th class="ps-3">Produto Selecionado</th>
                                    <th>Qtd</th>
                                    <th>Preço Unitário</th>
                                    <th class="pe-3 text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${detailsRows}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            accordionMarkets.appendChild(accItem);

            rank++;
        });
    }

    // Renderiza a aba de Roteiro Otimizado (Compra Dividida)
    function renderShoppingRoute() {
        splitSummaryCard.innerHTML = '';
        shoppingRouteContainer.innerHTML = '';

        if (!optimizationData || !optimizationData.split_comparison || !optimizationData.split_comparison.by_market) {
            splitSummaryCard.innerHTML = 'Aguardando produtos para criar roteiro otimizado...';
            return;
        }

        const split = optimizationData.split_comparison;
        
        // Renderiza card de resumo superior
        splitSummaryCard.innerHTML = `
            <h6 class="text-uppercase fw-semibold mb-1" style="font-size: 0.75rem;">Custo Dividindo por Menor Preço (Ajustado por Gosto)</h6>
            <h2 class="fw-bold mb-1">R$ ${parseFloat(split.total_cost).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</h2>
            ${split.potential_savings_vs_cheapest > 0 ? 
                `<div class="fs-6 fw-semibold">
                    <i class="bi bi-arrow-down-circle-fill me-1"></i>
                    Economia de <strong>R$ ${parseFloat(split.potential_savings_vs_cheapest).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong> 
                    em relação a comprar tudo no mercado mais barato!
                 </div>` : 
                `<div class="small">Comprar dividido dá o mesmo valor que comprar no melhor mercado único.</div>`
            }
        `;

        // Renderiza roteiro agrupado por mercado
        const marketKeys = Object.keys(split.by_market);
        
        if (marketKeys.length === 0) {
            shoppingRouteContainer.innerHTML = '<p class="text-muted text-center py-3">Roteiro vazio.</p>';
            return;
        }

        marketKeys.forEach(mId => {
            const mGroup = split.by_market[mId];
            const stepDiv = document.createElement('div');
            stepDiv.className = 'step-item';
            
            let listItemsHtml = '';
            mGroup.items.forEach(item => {
                const stars = '⭐'.repeat(item.preference_score || 3);
                
                listItemsHtml += `
                    <li class="list-group-item bg-transparent py-2 px-0 d-flex justify-content-between align-items-center text-muted">
                        <div>
                            <strong>${parseFloat(item.quantity).toLocaleString('pt-BR')}x</strong> ${item.product_name}
                            <div class="small text-muted" style="font-size: 0.75rem;">
                                Marca: ${item.brand_name} (${stars})
                                ${item.is_promotion ? '<span class="badge bg-danger p-1 ms-1" style="font-size: 0.6rem;">PROMO</span>' : ''}
                            </div>
                        </div>
                        <span class="text-end">
                            R$ ${parseFloat(item.total_price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} 
                            <div class="small" style="font-size: 0.7rem;">(R$ ${parseFloat(item.unit_price).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} un)</div>
                        </span>
                    </li>
                `;
            });

            stepDiv.innerHTML = `
                <div class="step-marker"></div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold mb-0 text-gradient fs-6">${mGroup.market_name}</h6>
                    <span class="badge bg-primary">Subtotal: R$ ${parseFloat(mGroup.total_cost).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                </div>
                <ul class="list-group list-group-flush mb-2" style="font-size: 0.9rem;">
                    ${listItemsHtml}
                </ul>
            `;
            shoppingRouteContainer.appendChild(stepDiv);
        });
    }

    // Auxiliar: Atualiza valores automáticos nos campos do Modal de Conclusão de Compra
    function updateModalInputs() {
        if (!optimizationData || !optimizationData.single_market_comparison || optimizationData.single_market_comparison.length === 0) return;

        const bestMarket = optimizationData.single_market_comparison[0];
        const worstMarket = optimizationData.single_market_comparison[optimizationData.single_market_comparison.length - 1];
        
        // Seleciona o mercado mais barato por padrão no dropdown
        const marketSelect = document.getElementById('purchaseMarket');
        if (marketSelect) {
            marketSelect.value = bestMarket.market_id;
        }

        // Preenche valor total e economia
        const totalVal = document.getElementById('totalVal');
        if (totalVal) {
            totalVal.value = parseFloat(bestMarket.total_cost).toFixed(2);
        }

        const totalSavings = document.getElementById('totalSavings');
        if (totalSavings) {
            const savings = parseFloat(worstMarket.total_cost) - parseFloat(bestMarket.total_cost);
            totalSavings.value = Math.max(0, savings).toFixed(2);
        }
    }

    // --- COMUNICAÇÕES AJAX COM A API ---
    
    // 1. Adicionar Item
    function addItem(productId, qty) {
        fetch('?route=api/lists/add-item', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                list_id: listId,
                product_id: productId,
                quantity: qty
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                optimizationData = res.optimization;
                currentListItems = res.optimization.split_comparison.items || [];
                updateUI();
            }
        });
    }

    // 2. Remover Item
    function removeItem(productId) {
        fetch('?route=api/lists/remove-item', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                list_id: listId,
                product_id: productId
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                optimizationData = res.optimization;
                currentListItems = res.optimization.split_comparison.items || [];
                updateUI();
            }
        });
    }

    // 3. Atualizar Quantidade
    function updateItemQuantity(productId, qty) {
        fetch('?route=api/lists/update-quantity', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                list_id: listId,
                product_id: productId,
                quantity: qty
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                optimizationData = res.optimization;
                currentListItems = res.optimization.split_comparison.items || [];
                updateUI();
            }
        });
    }
});
</script>
