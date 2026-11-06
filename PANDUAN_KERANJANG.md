# Panduan Fitur Keranjang Belanja & Checkout — Radja Game

## File yang Ditambahkan / Diubah

| File | Keterangan |
|------|------------|
| `database_setup.sql` | **BARU** — Buat tabel `produk`, `pesanan`, `detail_pesanan` + seed 6 produk katalog |
| `config/database.php` | **BARU** — Koneksi DB bersama untuk frontend |
| `cart_action.php` | **BARU** — API session (add / update / remove) |
| `keranjang.php` | **BARU** — Halaman keranjang + form checkout + konfirmasi |
| `index.php` | **DIUBAH** — Session, badge keranjang di navbar, `data-produk-id` pada kartu, JS Add to Cart |
| `admin/config/database.php` | **DIUBAH** — Charset utf8mb4 |

Fitur lama (admin login, dashboard, halaman statis) **tidak diubah** secara fungsional.

---

## Langkah Instalasi

### 1. Jalankan SQL

Buka phpMyAdmin (atau MySQL CLI), pilih database `radja_game`, lalu import / jalankan isi file:

```
database_setup.sql
```

Ini akan:
- Membuat tabel `produk`, `pesanan`, `detail_pesanan` (jika belum ada)
- Mengisi 6 produk sesuai katalog di `index.php` (hanya jika tabel produk masih kosong)

### 2. Pastikan struktur folder

```
umkm/
├── config/database.php
├── cart_action.php
├── keranjang.php
├── index.php
├── database_setup.sql
├── css/ ...
├── js/ ...
├── img/ ...
└── admin/
```

### 3. Koneksi database

Default di `config/database.php` dan `admin/config/database.php`:

```php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "radja_game";
```

Sesuaikan jika environment Anda berbeda.

---

## Cara Kerja Fitur

1. **Tambah ke keranjang**  
   Klik kartu produk → modal detail → tombol **Add to Cart**.  
   Data disimpan di `$_SESSION['cart']` (key = ID produk, value = qty).  
   Harga & stok selalu diambil ulang dari tabel `produk`.

2. **Badge navbar**  
   Ikon **Keranjang** di pojok kanan navbar menampilkan jumlah item dan mengarah ke `keranjang.php`.

3. **Halaman keranjang** (`keranjang.php`)  
   - Daftar produk (gambar, nama, harga, qty, subtotal)  
   - Tombol + / − untuk ubah kuantitas  
   - Hapus item  
   - Ringkasan subtotal + total  
   - Form checkout: nama, telepon, alamat, catatan, metode (Transfer / COD)

4. **Checkout**  
   - Validasi server-side  
   - Cek stok & harga terbaru dari DB  
   - Transaksi MySQL: insert `pesanan` + `detail_pesanan` + kurangi stok  
   - Keranjang dikosongkan  
   - Tampil nomor pesanan (format `RG-YYYYMMDD-XXXXXX`) dan instruksi pembayaran

---

## Keamanan yang Diterapkan

- Prepared statements (semua query)
- CSRF token pada aksi keranjang & checkout
- Harga / total dihitung di server (bukan dari browser)
- Stok dicek sebelum dan saat transaksi
- `mysqli_begin_transaction` + rollback jika gagal
- Output di-escape dengan `htmlspecialchars`

---

## Pengujian Cepat

1. Import `database_setup.sql`
2. Buka `index.php` → klik produk → **Add to Cart**
3. Pastikan badge keranjang bertambah
4. Buka **Keranjang** → ubah qty, hapus item
5. Isi form checkout → submit
6. Cek tabel `pesanan` & `detail_pesanan` di database
7. Pastikan stok produk berkurang
8. Coba checkout dengan qty > stok → harus ditolak

---

## Catatan

- Belum ada login pembeli (guest checkout via session).
- Metode pembayaran: **Transfer Bank** dan **COD** (sesuai permintaan — tanpa gateway palsu).
- Admin dapat melihat pesanan melalui halaman `admin/lihat-pesanan.php` (saat ini masih template; bisa dihubungkan ke tabel `pesanan` di tahap berikutnya).
- Jika ingin menambah produk baru, isi lewat SQL / admin, lalu pastikan ID sesuai jika ingin dipasang di kartu katalog hardcoded.
