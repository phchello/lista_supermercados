document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------
    // 1. Sidebar Toggles para Celulares
    // -------------------------------------------------------------
    const sidebar = document.querySelector('.app-sidebar');
    const toggleBtn = document.getElementById('sidebarToggleBtn');
    const closeBtn = document.getElementById('sidebarCloseBtn');

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.add('show');
        });
    }

    if (closeBtn && sidebar) {
        closeBtn.addEventListener('click', () => {
            sidebar.classList.remove('show');
        });
    }

    // -------------------------------------------------------------
    // 2. Alternador de Tema Escuro / Claro
    // -------------------------------------------------------------
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const themeToggleIcon = document.getElementById('themeToggleIcon');
    const themeToggleText = document.getElementById('themeToggleText');
    const htmlElem = document.documentElement;

    // Carrega tema salvo
    const savedTheme = localStorage.getItem('theme') || 'light';
    htmlElem.setAttribute('data-bs-theme', savedTheme);
    updateThemeUI(savedTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function () {
            const currentTheme = htmlElem.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            htmlElem.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeUI(newTheme);
        });
    }

    function updateThemeUI(theme) {
        if (!themeToggleIcon) return;
        if (theme === 'dark') {
            themeToggleIcon.className = 'bi bi-sun-fill';
            themeToggleText.textContent = 'Tema Claro';
        } else {
            themeToggleIcon.className = 'bi bi-moon-fill';
            themeToggleText.textContent = 'Tema Escuro';
        }
    }

    // -------------------------------------------------------------
    // 3. Autocomplete de Produtos (Listas de Compras)
    // -------------------------------------------------------------
    const searchInput = document.getElementById('productSearchInput');
    const productIdInput = document.getElementById('productIdInput');
    const suggestionsContainer = document.getElementById('autocompleteSuggestions');

    if (searchInput && suggestionsContainer) {
        searchInput.addEventListener('input', function () {
            const query = this.value.trim();
            if (query.length < 2) {
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.style.display = 'none';
                return;
            }

            fetch(`?route=api/products&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(products => {
                    suggestionsContainer.innerHTML = '';
                    if (products.length === 0) {
                        const noResult = document.createElement('div');
                        noResult.className = 'suggestion-item text-muted';
                        noResult.textContent = 'Nenhum produto cadastrado com este nome';
                        suggestionsContainer.appendChild(noResult);
                    } else {
                        products.forEach(p => {
                            const item = document.createElement('div');
                            item.className = 'suggestion-item';
                            item.innerHTML = `<strong>${p.name}</strong> <span class="badge bg-secondary ms-2">${p.brand || 'Sem Marca'}</span>`;
                            item.addEventListener('click', function () {
                                searchInput.value = p.name;
                                productIdInput.value = p.id;
                                suggestionsContainer.innerHTML = '';
                                suggestionsContainer.style.display = 'none';
                            });
                            suggestionsContainer.appendChild(item);
                        });
                    }
                    suggestionsContainer.style.display = 'block';
                });
        });

        // Fecha sugestões se clicar fora
        document.addEventListener('click', function (e) {
            if (e.target !== searchInput) {
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.style.display = 'none';
            }
        });
    }

    // -------------------------------------------------------------
    // 4. Gráfico Histórico de Preços do Produto (Chart.js)
    // -------------------------------------------------------------
    const chartCanvas = document.getElementById('productHistoryChart');
    if (chartCanvas) {
        const productId = chartCanvas.dataset.productId;
        
        fetch(`?route=api/price-history&id=${productId}`)
            .then(response => response.json())
            .then(data => {
                new Chart(chartCanvas, {
                    type: 'line',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            y: {
                                title: {
                                    display: true,
                                    text: 'Preço (R$)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Data de Coleta'
                                }
                            }
                        }
                    }
                });
            });
    }

    // -------------------------------------------------------------
    // 5. OCR Local no Frontend (Tesseract.js)
    // -------------------------------------------------------------
    const receiptImageInput = document.getElementById('receiptImageInput');
    const ocrStatus = document.getElementById('ocrStatus');
    const ocrProgressBar = document.getElementById('ocrProgressBar');
    const ocrTextResult = document.getElementById('ocrTextResult');
    const processOcrTextBtn = document.getElementById('processOcrTextBtn');
    const ocrPreviewTableBody = document.getElementById('ocrPreviewTableBody');
    const ocrImportSection = document.getElementById('ocrImportSection');
    const saveImportedBtn = document.getElementById('saveImportedBtn');

    if (receiptImageInput) {
        receiptImageInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            // Mostra elementos de progresso
            ocrStatus.style.display = 'block';
            ocrProgressBar.style.width = '0%';
            ocrProgressBar.textContent = '0%';
            ocrStatus.querySelector('span').textContent = 'Carregando Tesseract OCR...';

            Tesseract.recognize(
                file,
                'por', // Português
                {
                    logger: m => {
                        if (m.status === 'recognizing text') {
                            const progress = Math.round(m.progress * 100);
                            ocrProgressBar.style.width = progress + '%';
                            ocrProgressBar.textContent = progress + '%';
                            ocrStatus.querySelector('span').textContent = 'Escaneando Cupom Fiscal...';
                        }
                    }
                }
            ).then(({ data: { text } }) => {
                ocrStatus.querySelector('span').textContent = 'Escaneamento concluído!';
                ocrProgressBar.className = 'progress-bar bg-success';
                ocrTextResult.value = text;
                ocrTextResult.style.display = 'block';
                processOcrTextBtn.style.display = 'inline-block';
            }).catch(err => {
                ocrStatus.querySelector('span').textContent = 'Erro ao ler imagem: ' + err.message;
                ocrProgressBar.className = 'progress-bar bg-danger';
            });
        });
    }

    if (processOcrTextBtn) {
        processOcrTextBtn.addEventListener('click', function () {
            const text = ocrTextResult.value;
            if (!text.trim()) return;

            fetch('?route=api/process-ocr', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ text: text })
            })
            .then(res => res.json())
            .then(data => {
                ocrPreviewTableBody.innerHTML = '';
                if (data.items && data.items.length > 0) {
                    data.items.forEach((item, index) => {
                        const tr = document.createElement('tr');
                        tr.dataset.productId = item.product_id || 0;
                        tr.innerHTML = `
                            <td>
                                <input type="text" class="form-control form-control-sm item-name" value="${item.name}">
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm item-qty" style="width: 70px;" value="${item.quantity}">
                            </td>
                            <td>
                                <input type="number" step="0.01" class="form-control form-control-sm item-price" style="width: 90px;" value="${item.unit_price}">
                            </td>
                            <td>
                                ${item.is_new ? 
                                    '<span class="badge bg-warning">Novo Produto</span>' : 
                                    `<span class="badge bg-success">Associado a: ${item.suggested_name}</span>`
                                }
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-import-row-btn">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        `;
                        
                        // Handler para excluir linha da importação
                        tr.querySelector('.remove-import-row-btn').addEventListener('click', function() {
                            tr.remove();
                        });

                        ocrPreviewTableBody.appendChild(tr);
                    });

                    ocrImportSection.style.display = 'block';
                } else {
                    alert('Nenhum item detectado no cupom. Tente ajustar o texto manualmente na caixa de texto e clique em reprocessar.');
                }
            });
        });
    }

    if (saveImportedBtn) {
        saveImportedBtn.addEventListener('click', function () {
            const marketId = document.getElementById('ocrMarketSelect').value;
            const date = document.getElementById('ocrDateInput').value;

            if (!marketId) {
                alert('Selecione o Supermercado onde realizou a compra.');
                return;
            }

            const rows = ocrPreviewTableBody.querySelectorAll('tr');
            const items = [];

            rows.forEach(row => {
                const name = row.querySelector('.item-name').value.trim();
                const qty = parseFloat(row.querySelector('.item-qty').value);
                const price = parseFloat(row.querySelector('.item-price').value);
                const prodId = parseInt(row.dataset.productId);

                if (name && price > 0) {
                    items.push({
                        product_id: prodId,
                        name: name,
                        quantity: qty,
                        unit_price: price
                    });
                }
            });

            if (items.length === 0) {
                alert('Nenhum item válido para importar.');
                return;
            }

            saveImportedBtn.disabled = true;
            saveImportedBtn.textContent = 'Importando...';

            fetch('?route=api/upload-xml', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    market_id: marketId,
                    date: date,
                    items: items
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = '?route=dashboard';
                } else {
                    alert('Erro na importação: ' + data.error);
                    saveImportedBtn.disabled = false;
                    saveImportedBtn.textContent = 'Salvar e Registrar Preços';
                }
            });
        });
    }
});
