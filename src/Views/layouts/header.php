<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Comparador de Preços') ?> - Economia Total</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-container">
    <!-- Menu Lateral (Sidebar) -->
    <aside class="app-sidebar">
        <div class="sidebar-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-cart-check-fill logo-icon text-primary fs-3"></i>
                <span class="logo-text fw-bold text-gradient fs-5">MercadoPoupe</span>
            </div>
            <button class="btn btn-sm d-md-none text-light" id="sidebarCloseBtn">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        
        <nav class="sidebar-nav mt-4">
            <a href="?route=dashboard" class="nav-item <?= ($_GET['route'] ?? '') === 'dashboard' || empty($_GET['route']) ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="?route=products" class="nav-item <?= strpos($_GET['route'] ?? '', 'products') === 0 ? 'active' : '' ?>">
                <i class="bi bi-box-seam"></i> Produtos
            </a>
            <a href="?route=markets" class="nav-item <?= strpos($_GET['route'] ?? '', 'markets') === 0 ? 'active' : '' ?>">
                <i class="bi bi-shop"></i> Supermercados
            </a>
            <a href="?route=lists" class="nav-item <?= strpos($_GET['route'] ?? '', 'lists') === 0 && strpos($_GET['route'] ?? '', 'history') === false ? 'active' : '' ?>">
                <i class="bi bi-list-check"></i> Minhas Listas
            </a>
            <a href="?route=lists/history" class="nav-item <?= strpos($_GET['route'] ?? '', 'lists/history') === 0 ? 'active' : '' ?>">
                <i class="bi bi-clock-history"></i> Histórico de Compras
            </a>
            <a href="?route=receipt/upload" class="nav-item <?= strpos($_GET['route'] ?? '', 'receipt/upload') === 0 ? 'active' : '' ?>">
                <i class="bi bi-receipt"></i> Importar Cupom
            </a>
        </nav>
        
        <div class="sidebar-footer mt-auto p-3">
            <!-- Alternador de Tema -->
            <button class="btn w-100 theme-toggle-btn d-flex align-items-center justify-content-center gap-2" id="themeToggleBtn">
                <i class="bi bi-moon-fill" id="themeToggleIcon"></i> 
                <span id="themeToggleText">Tema Escuro</span>
            </button>
        </div>
    </aside>

    <!-- Conteúdo Principal -->
    <main class="app-main-content">
        <!-- Top Header Mobile -->
        <header class="main-header d-flex d-md-none align-items-center justify-content-between p-3 border-bottom mb-4">
            <button class="btn btn-outline-secondary" id="sidebarToggleBtn">
                <i class="bi bi-list"></i>
            </button>
            <span class="fw-bold fs-5">MercadoPoupe</span>
            <div></div> <!-- Spacer -->
        </header>

        <div class="container-fluid py-4">
            <!-- Alertas e Avisos Globais (opcional) -->
            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
