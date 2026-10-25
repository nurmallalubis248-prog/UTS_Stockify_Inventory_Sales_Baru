-- =====================================================================
-- Contoh query SQL setara dengan logika di aplikasi (untuk belajar / demo)
-- =====================================================================
USE inventory_sales;

-- 1) PENJUALAN: satu transaksi atomik dengan penguncian baris
--    (setara TransactionService::createSale + DB::transaction + lockForUpdate)
START TRANSACTION;

  -- Kunci baris produk agar penjualan bersamaan menunggu giliran
  SELECT id, stock, selling_price FROM products WHERE id = 1 FOR UPDATE;

  -- Aplikasi memeriksa: stok >= jumlah. Jika tidak, ROLLBACK (lihat bawah).
  INSERT INTO sales (user_id, sale_number, total, status, sold_at, created_at, updated_at)
  VALUES (2, 'SL-CONTOH-0001', 0, 'completed', NOW(), NOW(), NOW());
  SET @sale_id = LAST_INSERT_ID();

  -- Harga diambil dari tabel products, bukan dari input browser
  INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal, created_at, updated_at)
  SELECT @sale_id, p.id, 3, p.selling_price, p.selling_price * 3, NOW(), NOW()
  FROM products p WHERE p.id = 1 AND p.stock >= 3;

  -- Kurangi stok hanya jika masih cukup; hasilnya 0 baris berarti gagal
  UPDATE products SET stock = stock - 3 WHERE id = 1 AND stock >= 3;
  -- ROW_COUNT() = 0  ->  ROLLBACK;  selain itu lanjut:

  UPDATE sales
  SET total = (SELECT COALESCE(SUM(subtotal), 0) FROM sale_items WHERE sale_id = @sale_id)
  WHERE id = @sale_id;

COMMIT;   -- ganti dengan ROLLBACK; bila ada langkah yang gagal

-- 2) PEMBELIAN: stok bertambah
START TRANSACTION;
  INSERT INTO purchases (supplier_id, user_id, purchase_number, total, status, purchased_at, created_at, updated_at)
  VALUES (1, 2, 'PO-CONTOH-0001', 0, 'confirmed', NOW(), NOW(), NOW());
  SET @po_id = LAST_INSERT_ID();

  INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_cost, subtotal, created_at, updated_at)
  VALUES (@po_id, 4, 20, 10500, 20 * 10500, NOW(), NOW());

  UPDATE products SET stock = stock + 20 WHERE id = 4;
  UPDATE purchases SET total = 20 * 10500 WHERE id = @po_id;
COMMIT;

-- 3) PEMBATALAN PENJUALAN: kembalikan stok, tandai dibatalkan
START TRANSACTION;
  SELECT status FROM sales WHERE id = @sale_id FOR UPDATE;   -- harus 'completed'

  UPDATE products p
  JOIN sale_items si ON si.product_id = p.id
  SET p.stock = p.stock + si.quantity
  WHERE si.sale_id = @sale_id;

  UPDATE sales SET status = 'cancelled', cancelled_at = NOW()
  WHERE id = @sale_id AND status = 'completed';
COMMIT;

-- 4) DASHBOARD (agregat dari database, bukan angka statis)
SELECT COUNT(*) AS total_produk FROM products WHERE deleted_at IS NULL;

SELECT COUNT(*) AS produk_stok_rendah
FROM products WHERE deleted_at IS NULL AND stock <= minimum_stock;

SELECT COALESCE(SUM(total), 0) AS penjualan_hari_ini
FROM sales WHERE status = 'completed' AND DATE(sold_at) = CURDATE();

SELECT COALESCE(SUM(total), 0) AS penjualan_bulan_ini
FROM sales WHERE status = 'completed'
  AND sold_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01');

SELECT (SELECT COUNT(*) FROM sales) AS jumlah_penjualan,
       (SELECT COUNT(*) FROM purchases) AS jumlah_pembelian;

-- 5) DAFTAR STOK RENDAH
SELECT p.sku, p.name, c.name AS kategori, p.stock, p.minimum_stock
FROM products p
JOIN categories c ON c.id = p.category_id
WHERE p.deleted_at IS NULL AND p.stock <= p.minimum_stock
ORDER BY p.stock;

-- 6) PENCARIAN + FILTER (produk)
SELECT p.sku, p.name, c.name AS kategori, p.selling_price, p.stock
FROM products p
JOIN categories c ON c.id = p.category_id
WHERE p.deleted_at IS NULL
  AND (p.sku LIKE '%beras%' OR p.name LIKE '%beras%')
  AND p.category_id = 1
ORDER BY p.name
LIMIT 10 OFFSET 0;                -- pagination: halaman 2 -> OFFSET 10

-- 7) RIWAYAT PENJUALAN DENGAN FILTER TANGGAL DAN STATUS
SELECT s.sale_number, s.sold_at, u.name AS petugas, s.status, s.total
FROM sales s
JOIN users u ON u.id = s.user_id
WHERE s.status = 'completed'
  AND DATE(s.sold_at) BETWEEN '2026-10-01' AND '2026-10-31'
ORDER BY s.sold_at DESC
LIMIT 10;

-- 8) DETAIL SATU TRANSAKSI
SELECT p.sku, p.name, si.quantity, si.unit_price, si.subtotal
FROM sale_items si
JOIN products p ON p.id = si.product_id
WHERE si.sale_id = 1;

-- 9) VERIFIKASI KONSISTENSI: total header harus sama dengan jumlah item
SELECT s.id, s.total AS total_header, SUM(si.subtotal) AS total_item
FROM sales s JOIN sale_items si ON si.sale_id = s.id
GROUP BY s.id, s.total
HAVING s.total <> SUM(si.subtotal);          -- hasil kosong = konsisten
