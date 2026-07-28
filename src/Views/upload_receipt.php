<div class="mb-4">
    <h3 class="fw-bold mb-1">Importar Cupom Fiscal</h3>
    <p class="text-muted mb-0">Adicione novos preços ao sistema de forma rápida enviando o XML do cupom ou tirando uma foto.</p>
</div>

<div class="row g-4">
    <!-- Esquerda: Envio de XML ou Imagem -->
    <div class="col-md-5">
        <!-- Método 1: XML NFC-e -->
        <div class="card card-premium p-4 mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-filetype-xml text-primary me-2"></i>Importar XML NFC-e</h5>
            <p class="text-muted small">Carregue o arquivo XML da Nota Fiscal de Consumidor Eletrônica. O sistema irá ler o EAN, nomes exatos, quantidades e valores originais automaticamente.</p>
            
            <form action="?route=receipt/process" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="xmlFileInput" class="form-label">Selecione o arquivo XML (.xml)</label>
                    <input type="file" name="xml_file" id="xmlFileInput" class="form-control" accept=".xml" required>
                </div>
                <button type="submit" class="btn btn-primary btn-modern w-100"><i class="bi bi-upload me-1"></i> Carregar e Atualizar</button>
            </form>
        </div>

        <!-- Método 2: Imagem OCR -->
        <div class="card card-premium p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-camera text-primary me-2"></i>Escanear Foto do Cupom (OCR)</h5>
            <p class="text-muted small">Tire uma foto nítida do cupom fiscal ou carregue um PDF. O processamento OCR ocorrerá localmente no seu computador de forma privada e segura.</p>
            
            <div class="mb-3">
                <label for="receiptImageInput" class="form-label">Selecione ou Tire Foto do Cupom</label>
                <input type="file" id="receiptImageInput" class="form-control" accept="image/*,application/pdf">
            </div>

            <!-- Progresso do OCR -->
            <div id="ocrStatus" class="mb-3" style="display: none;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="small text-muted">Aguardando arquivo...</span>
                </div>
                <div class="progress" style="height: 15px;">
                    <div id="ocrProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;">0%</div>
                </div>
            </div>

            <!-- Caixa de Texto do OCR para Ajuste Fino -->
            <div class="mb-3">
                <textarea id="ocrTextResult" class="form-control font-monospace small" rows="8" style="display: none; font-size: 0.75rem;" placeholder="O texto extraído aparecerá aqui. Ajuste-o se necessário para corrigir erros do scanner."></textarea>
            </div>

            <button type="button" id="processOcrTextBtn" class="btn btn-outline-primary btn-modern w-100" style="display: none;">
                <i class="bi bi-magic me-1"></i> Analisar Itens do Texto
            </button>
        </div>
    </div>

    <!-- Direita: Pré-visualização e Edição de Itens Encontrados pelo OCR -->
    <div class="col-md-7">
        <div id="ocrImportSection" class="card card-premium p-4 h-100" style="display: none;">
            <h5 class="fw-bold mb-3"><i class="bi bi-card-checklist text-success me-2"></i>Conferência de Itens Detectados</h5>
            <p class="text-muted small">Verifique se o robô identificou corretamente os produtos e valores. Você pode corrigir os nomes e preços unitários diretamente na tabela abaixo antes de salvar.</p>
            
            <div class="row g-3 mb-4 p-3 bg-body-tertiary rounded-3 border">
                <div class="col-md-7">
                    <label for="ocrMarketSelect" class="form-label fw-bold small">Supermercado da Compra <span class="text-danger">*</span></label>
                    <select id="ocrMarketSelect" class="form-select form-select-sm" required>
                        <option value="">Selecione o Mercado...</option>
                        <?php foreach ($markets as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="ocrDateInput" class="form-label fw-bold small">Data da Compra</label>
                    <input type="date" id="ocrDateInput" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                </div>
            </div>

            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Qtd</th>
                            <th>Preço Unit. (R$)</th>
                            <th>Status</th>
                            <th>Excluir</th>
                        </tr>
                    </thead>
                    <tbody id="ocrPreviewTableBody">
                        <!-- Linhas geradas via JavaScript em app.js -->
                    </tbody>
                </table>
            </div>

            <hr class="my-4">
            
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Produtos novos serão cadastrados automaticamente.</span>
                <button type="button" id="saveImportedBtn" class="btn btn-success btn-modern">
                    <i class="bi bi-cloud-arrow-up me-1"></i> Salvar e Registrar Preços
                </button>
            </div>
        </div>
    </div>
</div>
