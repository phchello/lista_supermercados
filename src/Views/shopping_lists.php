<div class="d-flex align-items-center justify-content-between mb-4">
    <h3 class="fw-bold mb-0">Minhas Listas de Compras</h3>
    <button type="button" class="btn btn-primary btn-modern d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newListModal">
        <i class="bi bi-plus-lg"></i> Criar Nova Lista
    </button>
</div>

<div class="row g-4">
    <?php if (empty($lists)): ?>
        <div class="col-12 text-center py-5 card card-premium">
            <i class="bi bi-list-task fs-1 text-muted"></i>
            <h5 class="mt-3 fw-bold">Nenhuma lista de compras criada</h5>
            <p class="text-muted">Crie sua primeira lista para começar a comparar preços entre os supermercados.</p>
            <button type="button" class="btn btn-primary btn-modern mt-2" data-bs-toggle="modal" data-bs-target="#newListModal">
                Criar Minha Primeira Lista
            </button>
        </div>
    <?php else: ?>
        <?php foreach ($lists as $list): ?>
            <div class="col-md-4">
                <div class="card card-premium p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div>
                                <h5 class="fw-bold mb-1"><?= htmlspecialchars($list['name']) ?></h5>
                                <span class="text-muted small">Criada em: <?= date('d/m/Y H:i', strtotime($list['created_at'])) ?></span>
                            </div>
                            <span class="badge bg-primary-subtle text-primary fs-6"><?= $list['total_items'] ?> itens</span>
                        </div>
                        
                        <p class="text-muted small mb-0">
                            <strong>Quantidade Total:</strong> <?= number_format($list['total_quantity'], 1, ',', '.') ?> unidades/kg
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-between">
                        <div class="btn-group w-100">
                            <a href="?route=lists/detail&id=<?= $list['id'] ?>" class="btn btn-outline-primary btn-modern w-75">
                                <i class="bi bi-cart-plus"></i> Montar & Comparar
                            </a>
                            <button type="button" class="btn btn-outline-secondary edit-list-btn"
                                    data-id="<?= $list['id'] ?>"
                                    data-name="<?= htmlspecialchars($list['name']) ?>"
                                    data-bs-toggle="modal" data-bs-target="#newListModal"
                                    title="Renomear">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="?route=lists/delete&id=<?= $list['id'] ?>" class="btn btn-outline-danger" 
                               onclick="return confirm('Deseja realmente excluir esta lista de compras?')" 
                               title="Excluir">
                               <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal: Criar / Editar Lista -->
<div class="modal fade" id="newListModal" tabindex="-1" aria-labelledby="newListModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="?route=lists/save" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="newListModalLabel">Nova Lista de Compras</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="modalListId" value="">
                    
                    <div class="mb-3">
                        <label for="modalListName" class="form-label">Nome da Lista <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="modalListName" class="form-control" placeholder="Ex: Compras do Mês, Churrasco Fim de Semana" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Lista</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Configura botões de edição de lista
    const editBtns = document.querySelectorAll('.edit-list-btn');
    const modalTitle = document.getElementById('newListModalLabel');
    const modalListId = document.getElementById('modalListId');
    const modalListName = document.getElementById('modalListName');

    editBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            modalTitle.textContent = 'Editar Lista';
            modalListId.value = this.dataset.id;
            modalListName.value = this.dataset.name;
        });
    });

    // Reseta modal ao fechar / abrir novo
    const newListModal = document.getElementById('newListModal');
    newListModal.addEventListener('hidden.bs.modal', function () {
        modalTitle.textContent = 'Nova Lista de Compras';
        modalListId.value = '';
        modalListName.value = '';
    });
});
</script>
