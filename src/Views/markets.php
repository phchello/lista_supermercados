<div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0">Supermercados Monitorados</h3>
    <button type="button" class="btn btn-primary btn-modern d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newMarketModal">
        <i class="bi bi-plus-lg"></i> Novo Mercado
    </button>
</div>

<div class="row g-4">
    <?php foreach ($markets as $m): ?>
        <div class="col-md-4">
            <div class="card card-premium p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-secondary-subtle p-2 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                                <?php if ($m['logo_url']): ?>
                                    <img src="<?= htmlspecialchars($m['logo_url']) ?>" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                <?php else: ?>
                                    <i class="bi bi-shop fs-3 text-secondary"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0"><?= htmlspecialchars($m['name']) ?></h5>
                                <?php if ($m['website_url']): ?>
                                    <a href="<?= htmlspecialchars($m['website_url']) ?>" target="_blank" class="text-muted small text-decoration-none">
                                        <i class="bi bi-link-45deg"></i> Website
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <?php if ($m['active']): ?>
                                <span class="badge bg-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inativo</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="row g-2 text-center my-2">
                        <div class="col-6 border-end">
                            <h6 class="text-muted small mb-1">Produtos Monitorados</h6>
                            <h5 class="fw-bold mb-0"><?= $m['product_count'] ?></h5>
                        </div>
                        <div class="col-6">
                            <h6 class="text-muted small mb-1">Preço Médio Geral</h6>
                            <h5 class="fw-bold mb-0 text-primary"><?= $m['avg_price'] ? 'R$ ' . number_format($m['avg_price'], 2, ',', '.') : 'Sem dados' ?></h5>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-between">
                    <span class="text-muted small">
                        <i class="bi bi-clock me-1"></i>Última atualização:<br>
                        <strong><?= $m['last_update'] ? date('d/m/Y H:i', strtotime($m['last_update'])) : 'Nunca sincronizado' ?></strong>
                    </span>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary edit-market-btn"
                                data-id="<?= $m['id'] ?>"
                                data-name="<?= htmlspecialchars($m['name']) ?>"
                                data-logo="<?= htmlspecialchars($m['logo_url'] ?? '') ?>"
                                data-website="<?= htmlspecialchars($m['website_url'] ?? '') ?>"
                                data-active="<?= $m['active'] ?>"
                                data-bs-toggle="modal" data-bs-target="#newMarketModal">
                            <i class="bi bi-pencil-square"></i> Editar
                        </button>
                        <a href="?route=markets/toggle&id=<?= $m['id'] ?>" class="btn btn-sm <?= $m['active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                            <?= $m['active'] ? '<i class="bi bi-eye-slash"></i> Desativar' : '<i class="bi bi-eye"></i> Ativar' ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal: Cadastro / Edição de Supermercado -->
<div class="modal fade" id="newMarketModal" tabindex="-1" aria-labelledby="newMarketModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="?route=markets/save" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="newMarketModalLabel">Novo Supermercado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="modalMarketId" value="">
                    
                    <div class="mb-3">
                        <label for="modalMarketName" class="form-label">Nome do Supermercado <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="modalMarketName" class="form-control" placeholder="Ex: Tenda Atacado" required>
                    </div>

                    <div class="mb-3">
                        <label for="modalMarketLogo" class="form-label">URL do Logo</label>
                        <input type="text" name="logo_url" id="modalMarketLogo" class="form-control" placeholder="Ex: https://dominio.com/logo.png">
                    </div>

                    <div class="mb-3">
                        <label for="modalMarketWebsite" class="form-label">Website ou Link de API</label>
                        <input type="text" name="website_url" id="modalMarketWebsite" class="form-control" placeholder="Ex: https://www.tendaatacado.com.br">
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="active" id="modalMarketActive" value="1" checked>
                        <label class="form-check-label" for="modalMarketActive">Mercado Ativo (participa das comparações)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Supermercado</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Configura botões de edição de mercado
    const editBtns = document.querySelectorAll('.edit-market-btn');
    const modalTitle = document.getElementById('newMarketModalLabel');
    const modalMarketId = document.getElementById('modalMarketId');
    const modalMarketName = document.getElementById('modalMarketName');
    const modalMarketLogo = document.getElementById('modalMarketLogo');
    const modalMarketWebsite = document.getElementById('modalMarketWebsite');
    const modalMarketActive = document.getElementById('modalMarketActive');

    editBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            modalTitle.textContent = 'Editar Supermercado';
            modalMarketId.value = this.dataset.id;
            modalMarketName.value = this.dataset.name;
            modalMarketLogo.value = this.dataset.logo;
            modalMarketWebsite.value = this.dataset.website;
            modalMarketActive.checked = this.dataset.active == 1;
        });
    });

    // Reseta modal ao fechar / abrir novo
    const newMarketModal = document.getElementById('newMarketModal');
    newMarketModal.addEventListener('hidden.bs.modal', function () {
        modalTitle.textContent = 'Novo Supermercado';
        modalMarketId.value = '';
        modalMarketName.value = '';
        modalMarketLogo.value = '';
        modalMarketWebsite.value = '';
        modalMarketActive.checked = true;
    });
});
</script>
