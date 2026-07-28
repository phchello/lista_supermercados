<div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0">Produtos Cadastrados</h3>
    <button type="button" class="btn btn-primary btn-modern d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newProductModal">
        <i class="bi bi-plus-lg"></i> Novo Produto
    </button>
</div>

<!-- Filtros -->
<div class="card card-premium p-3 mb-4">
    <form method="GET" class="row g-3">
        <input type="hidden" name="route" value="products">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Buscar por produto, EAN, marca ou categoria..." value="<?= htmlspecialchars($filters['search']) ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="brand_id" class="form-select">
                <option value="">Todas as Marcas</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?= $brand['id'] ?>" <?= $filters['brand_id'] == $brand['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($brand['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="category_id" class="form-select">
                <option value="">Todas as Categorias</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $filters['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-1 d-grid">
            <button type="submit" class="btn btn-outline-secondary">Filtrar</button>
        </div>
    </form>
</div>

<!-- Tabela de Produtos -->
<div class="card card-premium p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>EAN</th>
                    <th>Marca</th>
                    <th>Categoria</th>
                    <th>Menor Preço</th>
                    <th>Preço Médio</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Nenhum produto encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $prod): ?>
                        <tr>
                            <td>
                                <a href="?route=products/detail&id=<?= $prod['id'] ?>" class="fw-semibold text-decoration-none text-main">
                                    <?= htmlspecialchars($prod['name']) ?>
                                </a>
                            </td>
                            <td>
                                <span class="text-muted font-monospace small"><?= $prod['ean'] ?: 'N/D' ?></span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis"><?= htmlspecialchars($prod['brand_name'] ?? 'Sem Marca') ?></span>
                            </td>
                            <td>
                                <span class="text-muted"><?= htmlspecialchars($prod['category_name'] ?? 'Sem Categoria') ?></span>
                            </td>
                            <td>
                                <?php if ($prod['cheapest_price'] !== null): ?>
                                    <span class="fw-bold text-success">R$ <?= number_format($prod['cheapest_price'], 2, ',', '.') ?></span>
                                    <div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-shop"></i> <?= htmlspecialchars($prod['cheapest_market']) ?></div>
                                <?php else: ?>
                                    <span class="text-muted small">Sem preço</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $prod['avg_price'] !== null ? 'R$ ' . number_format($prod['avg_price'], 2, ',', '.') : 'N/D' ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="?route=products/detail&id=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Histórico e detalhes">
                                        <i class="bi bi-graph-up"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-product-btn" 
                                            data-id="<?= $prod['id'] ?>" 
                                            data-name="<?= htmlspecialchars($prod['name']) ?>" 
                                            data-ean="<?= htmlspecialchars($prod['ean'] ?? '') ?>" 
                                            data-brand="<?= htmlspecialchars($prod['brand_name'] ?? '') ?>" 
                                            data-category="<?= htmlspecialchars($prod['category_name'] ?? '') ?>" 
                                            data-bs-toggle="modal" data-bs-target="#newProductModal">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="?route=products/delete&id=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deseja realmente excluir este produto? Todo o histórico de preços relacionado será deletado.')" title="Excluir produto">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Cadastro / Edição de Produto -->
<div class="modal fade" id="newProductModal" tabindex="-1" aria-labelledby="newProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="?route=products/save" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="newProductModalLabel">Novo Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="modalProductId" value="">
                    
                    <div class="mb-3">
                        <label for="modalProductName" class="form-label">Nome do Produto <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="modalProductName" class="form-control" placeholder="Ex: Leite Italac Integral 1L" required>
                    </div>

                    <div class="mb-3">
                        <label for="modalProductEan" class="form-label">Código de Barras (EAN)</label>
                        <input type="text" name="ean" id="modalProductEan" class="form-control" placeholder="Ex: 7891234567890">
                    </div>

                    <div class="mb-3">
                        <label for="modalProductBrand" class="form-label">Marca</label>
                        <input type="text" name="brand_name" id="modalProductBrand" class="form-control" placeholder="Ex: Italac (Criada automaticamente se não existir)" list="brandsList">
                        <datalist id="brandsList">
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?= htmlspecialchars($brand['name']) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="mb-3">
                        <label for="modalProductCategory" class="form-label">Categoria</label>
                        <input type="text" name="category_name" id="modalProductCategory" class="form-control" placeholder="Ex: Higiene, Laticínios" list="categoriesList">
                        <datalist id="categoriesList">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['name']) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Produto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Configura botões de edição de produto
    const editBtns = document.querySelectorAll('.edit-product-btn');
    const modalTitle = document.getElementById('newProductModalLabel');
    const modalProductId = document.getElementById('modalProductId');
    const modalProductName = document.getElementById('modalProductName');
    const modalProductEan = document.getElementById('modalProductEan');
    const modalProductBrand = document.getElementById('modalProductBrand');
    const modalProductCategory = document.getElementById('modalProductCategory');

    editBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            modalTitle.textContent = 'Editar Produto';
            modalProductId.value = this.dataset.id;
            modalProductName.value = this.dataset.name;
            modalProductEan.value = this.dataset.ean;
            modalProductBrand.value = this.dataset.brand;
            modalProductCategory.value = this.dataset.category;
        });
    });

    // Reseta modal ao fechar / abrir novo
    const newProductModal = document.getElementById('newProductModal');
    newProductModal.addEventListener('hidden.bs.modal', function () {
        modalTitle.textContent = 'Novo Produto';
        modalProductId.value = '';
        modalProductName.value = '';
        modalProductEan.value = '';
        modalProductBrand.value = '';
        modalProductCategory.value = '';
    });
});
</script>
