-- ============================================================
-- SQL Setup untuk Fitur Keranjang & Checkout
-- Database: radja_game
-- Jalankan file ini di phpMyAdmin / MySQL CLI
-- ============================================================

USE radja_game;

-- Tabel produk (jika belum ada)
CREATE TABLE IF NOT EXISTS produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    kategori VARCHAR(50) NOT NULL DEFAULT 'Lainnya',
    harga DECIMAL(12,0) NOT NULL,
    harga_lama DECIMAL(12,0) DEFAULT NULL,
    deskripsi TEXT,
    gambar VARCHAR(255) DEFAULT NULL,
    stok INT NOT NULL DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 4.5,
    status ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel pesanan
CREATE TABLE IF NOT EXISTS pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_pesanan VARCHAR(30) NOT NULL UNIQUE,
    nama_pembeli VARCHAR(100) NOT NULL,
    telepon VARCHAR(20) NOT NULL,
    alamat TEXT NOT NULL,
    catatan TEXT DEFAULT NULL,
    metode_pembayaran ENUM('transfer','cod') NOT NULL DEFAULT 'transfer',
    total DECIMAL(12,0) NOT NULL,
    status ENUM('menunggu','diproses','dikirim','selesai','dibatalkan') DEFAULT 'menunggu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel detail pesanan
CREATE TABLE IF NOT EXISTS detail_pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    produk_id INT NOT NULL,
    nama_produk VARCHAR(150) NOT NULL,
    harga DECIMAL(12,0) NOT NULL,
    qty INT NOT NULL,
    subtotal DECIMAL(12,0) NOT NULL,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    FOREIGN KEY (produk_id) REFERENCES produk(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed data produk sesuai katalog di index.php
-- Hanya insert jika tabel masih kosong
INSERT INTO produk (nama, kategori, harga, harga_lama, deskripsi, gambar, stok, rating)
SELECT * FROM (
    SELECT 'Redmi Note 17 Pro Max 5G' AS nama, 'Handphone' AS kategori, 8499000 AS harga, 8600000 AS harga_lama,
           'Smartphone kelas menengah-atas dari Xiaomi yang dirilis di Indonesia dengan fokus pada kapasitas baterai ekstrem dan ketahanan fisik.' AS deskripsi,
           'img/redmi.jpg' AS gambar, 10 AS stok, 4.9 AS rating
    UNION ALL
    SELECT 'Infinix Smart 10', 'Handphone', 1405000, 1500000,
           'Smartphone kelas entry-level yang dirilis resmi di Indonesia dengan harga mulai dari Rp1 jutaan.',
           'img/infinix.jpg', 15, 4.7
    UNION ALL
    SELECT 'Samsung Galaxy S23', 'Handphone', 13999000, 14200000,
           'Flagship Samsung dengan performa tinggi, kamera profesional, dan desain premium.',
           'img/sam.jpg', 8, 4.9
    UNION ALL
    SELECT 'Casing', 'Aksesoris', 12000, NULL,
           'Lapisan pelindung yang dipasang pada bagian belakang dan samping smartphone untuk mencegah goresan, retak, atau kerusakan akibat benturan.',
           'img/casing.jpg', 50, 4.5
    UNION ALL
    SELECT 'TWS', 'Aksesoris', 70000, 75000,
           'Perangkat audio (seperti earbud atau speaker) yang sepenuhnya tanpa kabel.',
           'img/tws.jpg', 30, 4.6
    UNION ALL
    SELECT 'Kuota Internet IM3 50GB 30 hari', 'Kuota Internet', 120000, NULL,
           'Kuota internet IM3 dari Indosat Ooredoo Hutchison terbagi ke dalam beberapa jenis paket utama dengan koneksi 24 jam penuh.',
           'img/im3.jpg', 100, 4.8
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM produk LIMIT 1);

-- Index untuk performa
CREATE INDEX IF NOT EXISTS idx_produk_status ON produk(status);
CREATE INDEX IF NOT EXISTS idx_pesanan_kode ON pesanan(kode_pesanan);
CREATE INDEX IF NOT EXISTS idx_pesanan_status ON pesanan(status);
