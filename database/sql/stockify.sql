-- =====================================================================
-- Stockify — Inventory & Sales Management System
-- Skema + data demo untuk MySQL 8 / MariaDB 10.6+
--
-- Setara dengan migration Laravel di database/migrations dan
-- database/seeders. Pilih SALAH SATU: migration ATAU berkas SQL ini.
-- Jika memakai SQL ini, set di .env: SESSION_DRIVER=file, CACHE_STORE=file,
-- QUEUE_CONNECTION=sync (tabel sessions/cache Laravel tidak dibuat di sini).
--
-- Import:  mysql -u root -p < database/sql/stockify.sql
-- Akun demo: admin@example.com / staff@example.com / user@example.com, kata sandi: password
-- =====================================================================

CREATE DATABASE IF NOT EXISTS inventory_sales
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE inventory_sales;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS sale_items, sales, purchase_items, purchases,
                     products, suppliers, categories, users;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------- USERS ----------
CREATE TABLE users (
  id                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name              VARCHAR(255) NOT NULL,
  email             VARCHAR(255) NOT NULL,
  email_verified_at TIMESTAMP NULL,
  password          VARCHAR(255) NOT NULL,           -- bcrypt hash
  role              VARCHAR(20)  NOT NULL DEFAULT 'staff',
  remember_token    VARCHAR(100) NULL,
  created_at        TIMESTAMP NULL,
  updated_at        TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY users_email_unique (email),
  KEY users_role_index (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- MASTER DATA ----------
CREATE TABLE categories (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name        VARCHAR(100) NOT NULL,
  description VARCHAR(255) NULL,
  created_at  TIMESTAMP NULL,
  updated_at  TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY categories_name_unique (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE suppliers (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(150) NOT NULL,
  phone      VARCHAR(30)  NULL,
  email      VARCHAR(255) NULL,
  address    VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY suppliers_name_index (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id    BIGINT UNSIGNED NOT NULL,
  sku            VARCHAR(50)  NOT NULL,
  name           VARCHAR(150) NOT NULL,
  purchase_price DECIMAL(12,2) NOT NULL,
  selling_price  DECIMAL(12,2) NOT NULL,
  stock          INT UNSIGNED NOT NULL DEFAULT 0,
  minimum_stock  INT UNSIGNED NOT NULL DEFAULT 0,
  deleted_at     TIMESTAMP NULL,                      -- soft delete
  created_at     TIMESTAMP NULL,
  updated_at     TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY products_sku_unique (sku),                -- SKU unik dijaga database
  KEY products_name_index (name),
  KEY products_stock_index (stock),
  CONSTRAINT products_category_id_foreign FOREIGN KEY (category_id)
    REFERENCES categories (id) ON DELETE RESTRICT,
  CONSTRAINT products_prices_non_negative CHECK (purchase_price >= 0 AND selling_price >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- PEMBELIAN (STOK MASUK) ----------
CREATE TABLE purchases (
  id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  supplier_id     BIGINT UNSIGNED NOT NULL,
  user_id         BIGINT UNSIGNED NOT NULL,
  purchase_number VARCHAR(40) NOT NULL,
  total           DECIMAL(14,2) NOT NULL DEFAULT 0,
  status          VARCHAR(20) NOT NULL DEFAULT 'confirmed',   -- confirmed | cancelled
  purchased_at    TIMESTAMP NOT NULL,
  cancelled_at    TIMESTAMP NULL,
  notes           TEXT NULL,
  created_at      TIMESTAMP NULL,
  updated_at      TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY purchases_purchase_number_unique (purchase_number),
  KEY purchases_status_index (status),
  KEY purchases_purchased_at_index (purchased_at),
  CONSTRAINT purchases_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES suppliers (id) ON DELETE RESTRICT,
  CONSTRAINT purchases_user_id_foreign     FOREIGN KEY (user_id)     REFERENCES users (id)     ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE purchase_items (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  purchase_id BIGINT UNSIGNED NOT NULL,
  product_id  BIGINT UNSIGNED NOT NULL,
  quantity    INT UNSIGNED NOT NULL,
  unit_cost   DECIMAL(12,2) NOT NULL,
  subtotal    DECIMAL(14,2) NOT NULL,
  created_at  TIMESTAMP NULL,
  updated_at  TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY purchase_items_purchase_id_index (purchase_id),
  KEY purchase_items_product_id_index (product_id),
  CONSTRAINT purchase_items_purchase_id_foreign FOREIGN KEY (purchase_id) REFERENCES purchases (id) ON DELETE CASCADE,
  CONSTRAINT purchase_items_product_id_foreign  FOREIGN KEY (product_id)  REFERENCES products (id)  ON DELETE RESTRICT,
  CONSTRAINT purchase_items_quantity_positive CHECK (quantity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- PENJUALAN (STOK KELUAR) ----------
CREATE TABLE sales (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id       BIGINT UNSIGNED NOT NULL,
  sale_number   VARCHAR(40) NOT NULL,
  customer_name VARCHAR(150) NULL,
  total         DECIMAL(14,2) NOT NULL DEFAULT 0,
  status        VARCHAR(20) NOT NULL DEFAULT 'completed',     -- completed | cancelled
  sold_at       TIMESTAMP NOT NULL,
  cancelled_at  TIMESTAMP NULL,
  created_at    TIMESTAMP NULL,
  updated_at    TIMESTAMP NULL,
  PRIMARY KEY (id),
  UNIQUE KEY sales_sale_number_unique (sale_number),
  KEY sales_status_index (status),
  KEY sales_sold_at_index (sold_at),
  CONSTRAINT sales_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sale_items (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  sale_id    BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  quantity   INT UNSIGNED NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  subtotal   DECIMAL(14,2) NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  PRIMARY KEY (id),
  KEY sale_items_sale_id_index (sale_id),
  KEY sale_items_product_id_index (product_id),
  CONSTRAINT sale_items_sale_id_foreign    FOREIGN KEY (sale_id)    REFERENCES sales (id)    ON DELETE CASCADE,
  CONSTRAINT sale_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT,
  CONSTRAINT sale_items_quantity_positive CHECK (quantity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DATA DEMO (stok sudah mencerminkan transaksi contoh di bawah)
-- =====================================================================

-- Kata sandi ketiga akun: "password" (hash bcrypt)
INSERT INTO users (id, name, email, password, role, created_at, updated_at) VALUES
 (1, 'Admin Stockify', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW(), NOW()),
 (2, 'Staff Gudang',   'staff@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', NOW(), NOW()),
 (3, 'User Demo',      'user@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', NOW(), NOW());

INSERT INTO categories (id, name, description, created_at, updated_at) VALUES
 (1, 'Bahan Pokok', 'Beras, gula, minyak, dan kebutuhan dapur utama', NOW(), NOW()),
 (2, 'Minuman', 'Air mineral, teh, dan kopi', NOW(), NOW()),
 (3, 'Bumbu & Mi Instan', 'Bumbu dapur dan mi instan', NOW(), NOW()),
 (4, 'Kebersihan Rumah Tangga', 'Sabun, deterjen, dan tisu', NOW(), NOW());

INSERT INTO suppliers (id, name, phone, email, address, created_at, updated_at) VALUES
 (1, 'PT Sumber Pangan Nusantara', '0812-3456-0001', 'sales@sumberpangan.example', 'Jl. Industri No. 12, Medan', NOW(), NOW()),
 (2, 'CV Tirta Segar Abadi', '0812-3456-0002', 'order@tirtasegar.example', 'Jl. Sudirman No. 45, Lhokseumawe', NOW(), NOW()),
 (3, 'UD Berkah Makmur', '0812-3456-0003', 'berkah@makmur.example', 'Jl. Pasar Baru No. 8, Banda Aceh', NOW(), NOW()),
 (4, 'PT Bersih Sejahtera', '0812-3456-0004', 'cs@bersihsejahtera.example', 'Jl. Gatot Subroto No. 21, Medan', NOW(), NOW());

INSERT INTO products (id, category_id, sku, name, purchase_price, selling_price, stock, minimum_stock, created_at, updated_at) VALUES
 (1, 1, 'BRS-001', 'Beras Premium 5 kg',        62000, 69000,  50, 10, NOW(), NOW()),
 (2, 1, 'GLA-001', 'Gula Pasir 1 kg',           14500, 16500,  78, 15, NOW(), NOW()),
 (3, 1, 'MYK-001', 'Minyak Goreng 2 L',         33000, 37000,  29, 10, NOW(), NOW()),
 (4, 1, 'TPG-001', 'Tepung Terigu 1 kg',        10500, 12500,   8, 10, NOW(), NOW()),
 (5, 2, 'MNM-001', 'Air Mineral 600 ml (dus)',  38000, 44000,  66, 12, NOW(), NOW()),
 (6, 2, 'TEH-001', 'Teh Celup isi 25',           6500,  8000,  60, 20, NOW(), NOW()),
 (7, 2, 'KPI-001', 'Kopi Bubuk 200 g',          18000, 22000,   4,  8, NOW(), NOW()),
 (8, 3, 'MIE-001', 'Mi Instan Goreng',           2800,  3500, 160, 60, NOW(), NOW()),
 (9, 3, 'KCP-001', 'Kecap Manis 275 ml',        11500, 14000,  45, 12, NOW(), NOW()),
 (10,3, 'GRM-001', 'Garam Halus 250 g',          2500,  3500,  55, 15, NOW(), NOW()),
 (11,4, 'SBN-001', 'Sabun Cuci Piring 800 ml',  14000, 17500,  25, 10, NOW(), NOW()),
 (12,4, 'DTR-001', 'Deterjen Bubuk 1 kg',       21000, 25500,  25,  8, NOW(), NOW()),
 (13,4, 'TSU-001', 'Tisu Gulung isi 4',         12000, 15000,   3, 10, NOW(), NOW());

INSERT INTO purchases (id, supplier_id, user_id, purchase_number, total, status, purchased_at, notes, created_at, updated_at) VALUES
 (1, 1, 2, 'PO-20261019-AAAA1', 1675000, 'confirmed', NOW(), 'Restock bahan pokok mingguan', NOW(), NOW()),
 (2, 2, 1, 'PO-20261019-AAAA2',  912000, 'confirmed', NOW(), NULL, NOW(), NOW());

INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_cost, subtotal, created_at, updated_at) VALUES
 (1, 1, 20, 62000, 1240000, NOW(), NOW()),
 (1, 2, 30, 14500,  435000, NOW(), NOW()),
 (2, 5, 24, 38000,  912000, NOW(), NOW());

INSERT INTO sales (id, user_id, sale_number, customer_name, total, status, sold_at, created_at, updated_at) VALUES
 (1, 2, 'SL-20261019-BBBB1', 'Toko Maju Jaya',  1028000, 'completed', NOW(), NOW(), NOW()),
 (2, 2, 'SL-20261019-BBBB2', 'Warung Bu Siti',   302000, 'completed', NOW(), NOW(), NOW()),
 (3, 1, 'SL-20261019-BBBB3', NULL,               439500, 'completed', NOW(), NOW(), NOW());

INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal, created_at, updated_at) VALUES
 (1, 1, 10, 69000, 690000, NOW(), NOW()),
 (1, 2, 12, 16500, 198000, NOW(), NOW()),
 (1, 8, 40,  3500, 140000, NOW(), NOW()),
 (2, 3,  6, 37000, 222000, NOW(), NOW()),
 (2, 6, 10,  8000,  80000, NOW(), NOW()),
 (3, 5,  8, 44000, 352000, NOW(), NOW()),
 (3, 11, 5, 17500,  87500, NOW(), NOW());
