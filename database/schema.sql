CREATE DATABASE IF NOT EXISTS lista_supermercados CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lista_supermercados;

-- Mercados
CREATE TABLE IF NOT EXISTS markets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    logo_url VARCHAR(255) NULL,
    website_url VARCHAR(255) NULL,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categorias
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Marcas
CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Produtos
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ean VARCHAR(13) NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    normalized_name VARCHAR(150) NOT NULL,
    brand_id INT NULL,
    category_id INT NULL,
    image_url VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_normalized_name (normalized_name),
    INDEX idx_ean (ean)
);

-- Histórico de Preços
CREATE TABLE IF NOT EXISTS price_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    market_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    is_promotion TINYINT(1) DEFAULT 0,
    discount_percentage DECIMAL(5, 2) DEFAULT 0.00,
    collected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (market_id) REFERENCES markets(id) ON DELETE CASCADE,
    INDEX idx_collected (collected_at),
    INDEX idx_product_market (product_id, market_id)
);

-- Listas de Compras
CREATE TABLE IF NOT EXISTS shopping_lists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Itens da Lista de Compras
CREATE TABLE IF NOT EXISTS shopping_list_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    list_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity DECIMAL(10, 2) DEFAULT 1.00,
    observation VARCHAR(255) NULL,
    FOREIGN KEY (list_id) REFERENCES shopping_lists(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Histórico de Compras Concluídas
CREATE TABLE IF NOT EXISTS purchase_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shopping_list_id INT NULL,
    purchase_date DATE NOT NULL,
    total_value DECIMAL(10, 2) NOT NULL,
    market_id INT NOT NULL,
    savings DECIMAL(10, 2) DEFAULT 0.00,
    items_json TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (market_id) REFERENCES markets(id) ON DELETE CASCADE
);

-- INSERÇÃO DE DADOS INICIAIS (SEED)
INSERT INTO markets (name, logo_url, website_url, active) VALUES
('Tenda Atacado', 'https://images.tendaatacado.com.br/logo.png', 'https://www.tendaatacado.com.br', 1),
('Atacadão', 'https://www.atacadao.com.br/logo.png', 'https://www.atacadao.com.br', 1),
('Assaí Atacadista', 'https://www.assai.com.br/logo.png', 'https://www.assai.com.br', 1),
('Carrefour', 'https://www.carrefour.com.br/logo.png', 'https://www.carrefour.com.br', 1),
('Pão de Açúcar', 'https://www.paodeacucar.com.br/logo.png', 'https://www.paodeacucar.com.br', 1),
('Sonda Supermercados', 'https://www.sonda.com.br/logo.png', 'https://www.sonda.com.br', 1);

INSERT INTO categories (name) VALUES
('Mercearia'),
('Hortifruti'),
('Carnes & Peixes'),
('Laticínios & Frios'),
('Bebidas'),
('Limpeza'),
('Higiene & Perfumaria');

INSERT INTO brands (name) VALUES
('Italac'),
('Piracanjuba'),
('Tio João'),
('Camil'),
('Omo'),
('Ypê'),
('Coca-Cola'),
('Friboi'),
('Qualitá');

-- Inserção de alguns produtos padrão
INSERT INTO products (ean, name, normalized_name, brand_id, category_id, image_url) VALUES
('7891234567890', 'Leite UHT Italac Integral 1L', 'leite uht italac integral 1l', 1, 4, ''),
('7891234567891', 'Leite UHT Piracanjuba Desnatado 1L', 'leite uht piracanjuba desnatado 1l', 2, 4, ''),
('7891234567892', 'Arroz Agulhinha Tipo 1 Tio João 5kg', 'arroz agulhinha tipo 1 tio joao 5kg', 3, 1, ''),
('7891234567893', 'Feijão Carioca Tipo 1 Camil 1kg', 'feijao carioca tipo 1 camil 1kg', 4, 1, ''),
('7891234567894', 'Lava Roupas Líquido Omo Proteção 3L', 'lava roupas liquido omo protecao 3l', 5, 6, ''),
('7891234567895', 'Detergente Líquido Ypê Neutro 500ml', 'detergente liquido ype neutro 500ml', 6, 6, ''),
('7891234567896', 'Refrigerante Coca-Cola Sem Açúcar 2L', 'refrigerante coca cola sem acucar 2l', 7, 5, ''),
('7891234567897', 'Alcatra Bovina Friboi Pedaço kg', 'alcatra bovina friboi pedaco kg', 8, 3, '');

-- Histórico de preços para simular a variação e gráficos (últimos 3 dias)
-- Leite Italac
INSERT INTO price_history (product_id, market_id, price, is_promotion, discount_percentage, collected_at) VALUES
(1, 1, 4.49, 0, 0.00, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 2, 4.39, 1, 10.00, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 3, 4.59, 0, 0.00, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 4, 4.99, 0, 0.00, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 5, 5.29, 0, 0.00, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 6, 4.89, 0, 0.00, DATE_SUB(NOW(), INTERVAL 3 DAY)),

(1, 1, 4.45, 0, 0.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 2, 4.39, 0, 0.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 3, 4.49, 0, 0.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 4, 4.89, 0, 0.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 5, 5.19, 0, 0.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 6, 4.79, 0, 0.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),

(1, 1, 4.39, 1, 5.00, NOW()),
(1, 2, 4.45, 0, 0.00, NOW()),
(1, 3, 4.29, 1, 12.00, NOW()),
(1, 4, 4.79, 0, 0.00, NOW()),
(1, 5, 4.99, 1, 15.00, NOW()),
(1, 6, 4.69, 0, 0.00, NOW());

-- Arroz Tio João
INSERT INTO price_history (product_id, market_id, price, is_promotion, discount_percentage, collected_at) VALUES
(3, 1, 26.90, 0, 0.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 2, 25.99, 0, 0.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 3, 26.50, 0, 0.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 4, 28.90, 0, 0.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 5, 29.90, 0, 0.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),

(3, 1, 25.90, 1, 8.00, NOW()),
(3, 2, 25.49, 1, 5.00, NOW()),
(3, 3, 26.20, 0, 0.00, NOW()),
(3, 4, 27.90, 0, 0.00, NOW()),
(3, 5, 28.50, 1, 10.00, NOW());

-- Feijão Camil
INSERT INTO price_history (product_id, market_id, price, is_promotion, discount_percentage, collected_at) VALUES
(4, 1, 8.49, 0, 0.00, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 2, 7.99, 1, 10.00, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 3, 8.29, 0, 0.00, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 4, 8.99, 0, 0.00, DATE_SUB(NOW(), INTERVAL 1 DAY)),

(4, 1, 8.39, 0, 0.00, NOW()),
(4, 2, 7.89, 1, 12.00, NOW()),
(4, 3, 8.19, 0, 0.00, NOW()),
(4, 4, 8.89, 0, 0.00, NOW());

-- Lava Roupas Omo
INSERT INTO price_history (product_id, market_id, price, is_promotion, discount_percentage, collected_at) VALUES
(5, 1, 38.90, 0, 0.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, 2, 37.90, 0, 0.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, 4, 41.90, 0, 0.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5, 5, 43.90, 0, 0.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),

(5, 1, 36.90, 1, 8.00, NOW()),
(5, 2, 37.50, 0, 0.00, NOW()),
(5, 4, 39.90, 1, 10.00, NOW()),
(5, 5, 42.50, 0, 0.00, NOW());
