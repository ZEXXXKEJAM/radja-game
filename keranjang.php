<?php
/**
 * keranjang.php — Keranjang & Checkout
 * Schema: produk, transaksi, detail_penjualan, pembeli
 */
session_start();
require_once __DIR__ . '/config/database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = function_exists('random_bytes')
        ? bin2hex(random_bytes(16))
        : md5(uniqid(mt_rand(), true));
}
$csrf = $_SESSION['csrf_token'];

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}
if (!isset($_SESSION['cart_meta']) || !is_array($_SESSION['cart_meta'])) {
    $_SESSION['cart_meta'] = array();
}

function format_rupiah($n) {
    return 'Rp ' . number_format((float)$n, 0, ',', '.');
}

function cart_count_session() {
    $n = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $q) $n += (int)$q;
    }
    return $n;
}

/**
 * Pastikan kolom status ada di tabel transaksi
 */
function pastikan_kolom_status_transaksi($conn) {
    if (!$conn) return;
    static $done = false;
    if ($done) return;
    $done = true;
    $r = @mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'status'");
    if ($r && mysqli_num_rows($r) === 0) {
        @mysqli_query($conn, "ALTER TABLE transaksi ADD COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Menunggu Konfirmasi'");
    }
}

/**
 * Ambil status pesanan dari DB berdasarkan id_transaksi
 */
function fetch_status_pesanan($conn, $id_transaksi) {
    $id_transaksi = (int)$id_transaksi;
    if (!$conn || $id_transaksi < 1) return 'Menunggu Konfirmasi';
    pastikan_kolom_status_transaksi($conn);
    $stmt = @mysqli_prepare($conn, "SELECT status FROM transaksi WHERE id_transaksi = ? LIMIT 1");
    if (!$stmt) return 'Menunggu Konfirmasi';
    mysqli_stmt_bind_param($stmt, 'i', $id_transaksi);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    if (!$row || empty($row['status'])) return 'Menunggu Konfirmasi';
    return $row['status'];
}

/**
 * Ambil produk dari DB
 */
function fetch_produk($conn, $id) {
    $id = (int)$id;
    if (!$conn || $id < 1) return null;
    $stmt = @mysqli_prepare($conn, "SELECT id_produk, nama_produk, harga_jual, stok, satuan, merk FROM produk WHERE id_produk = ? LIMIT 1");
    if (!$stmt) return null;
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    if (!$row) return null;
    return array(
        'id' => (int)$row['id_produk'],
        'nama' => $row['nama_produk'],
        'harga' => (int)$row['harga_jual'],
        'stok' => (int)$row['stok'],
        'satuan' => $row['satuan'],
        'merk' => $row['merk'],
    );
}

$checkout_success = false;
$order_data = null;
$errors = array();

/* ========== CHECKOUT ========== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    if (!hash_equals($csrf, isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $errors[] = 'Token keamanan tidak valid. Muat ulang halaman.';
    }

    $nama    = trim(isset($_POST['nama']) ? $_POST['nama'] : '');
    $email   = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $alamat  = trim(isset($_POST['alamat']) ? $_POST['alamat'] : '');
    $catatan = trim(isset($_POST['catatan']) ? $_POST['catatan'] : '');
    $metode  = isset($_POST['metode_pembayaran']) ? $_POST['metode_pembayaran'] : '';

    if ($nama === '' || mb_strlen($nama) < 3) $errors[] = 'Nama lengkap wajib diisi (min. 3 karakter).';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    if ($alamat === '' || mb_strlen($alamat) < 10) $errors[] = 'Alamat pengiriman wajib diisi (min. 10 karakter).';
    if (!in_array($metode, array('transfer', 'cod'), true)) $errors[] = 'Pilih metode pembayaran yang valid.';
    if (empty($_SESSION['cart'])) $errors[] = 'Keranjang kosong.';
    if (!$conn) $errors[] = 'Koneksi database gagal.';

    if (empty($errors)) {
        $items = array();
        $total = 0;

        foreach ($_SESSION['cart'] as $pid => $qty) {
            $pid = (int)$pid;
            $qty = (int)$qty;
            $p = fetch_produk($conn, $pid);
            if (!$p) {
                $errors[] = "Produk ID $pid tidak ditemukan.";
                continue;
            }
            if ($qty > $p['stok']) {
                $errors[] = 'Stok "' . $p['nama'] . '" tidak mencukupi (tersedia: ' . $p['stok'] . ').';
                continue;
            }
            $sub = $qty * $p['harga'];
            $total += $sub;
            $items[] = array(
                'produk_id' => $pid,
                'nama_produk' => $p['nama'],
                'harga' => $p['harga'],
                'qty' => $qty,
                'subtotal' => $sub,
            );
        }

        if (empty($errors) && !empty($items)) {
            mysqli_begin_transaction($conn);
            try {
                // 1) Cari / buat pembeli berdasarkan email
                // Schema pembeli: id_pembeli, nama_pembeli, email, alamat, username, password
                // $username = 'guest' . substr(md5(strtolower($email)), 0, 8);
                // $password = password_hash(bin2hex(random_bytes(6)), PASSWORD_DEFAULT);

                $id_pembeli = null;
                $stmt = mysqli_prepare($conn, "SELECT id_pembeli FROM pembeli WHERE email = ? LIMIT 1");
                mysqli_stmt_bind_param($stmt, 's', $email);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($res);
                mysqli_stmt_close($stmt);

                if ($row) {
                    $id_pembeli = (int)$row['id_pembeli'];
                    // Update nama, email, alamat (4 placeholder = 4 variabel: sssi)
                    $stmt = mysqli_prepare($conn, "UPDATE pembeli SET nama_pembeli = ?, email = ?, alamat = ? WHERE id_pembeli = ?");
                    mysqli_stmt_bind_param($stmt, 'sssi', $nama, $email, $alamat, $id_pembeli);
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception('Gagal memperbarui data pembeli: ' . mysqli_error($conn));
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $stmt = mysqli_prepare($conn,
                        "INSERT INTO pembeli (nama_pembeli, email, alamat) VALUES (?, ?, ?)"
                    );
                    mysqli_stmt_bind_param($stmt, 'sss', $nama, $email, $alamat);
                    if (!mysqli_stmt_execute($stmt)) {
                        throw new Exception('Gagal menyimpan data pembeli: ' . mysqli_error($conn));
                    }
                    $id_pembeli = (int)mysqli_insert_id($conn);
                    mysqli_stmt_close($stmt);
                }

                // 2) Insert transaksi
                // id_penjual default = 1 (owner dari dump SQL)
                pastikan_kolom_status_transaksi($conn);
                $id_penjual = 1;
                $tanggal = date('Y-m-d');
                $total_str = (string)$total;
                $status_awal = 'Menunggu Konfirmasi';

                $stmt = mysqli_prepare($conn,
                    "INSERT INTO transaksi (tanggal_penjualan, total_penjualan, id_pembeli, id_penjual, status) VALUES (?, ?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param($stmt, 'ssiis', $tanggal, $total_str, $id_pembeli, $id_penjual, $status_awal);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception('Gagal menyimpan transaksi: ' . mysqli_error($conn));
                }
                $id_transaksi = (int)mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);

                // 3) Insert detail_penjualan + kurangi stok
                $stmtDetail = mysqli_prepare($conn,
                    "INSERT INTO detail_penjualan (jumlah, harga_jual, subtotal, id_transaksi, id_produk) VALUES (?, ?, ?, ?, ?)"
                );
                $stmtStok = mysqli_prepare($conn,
                    "UPDATE produk SET stok = CAST(CAST(stok AS UNSIGNED) - ? AS CHAR) WHERE id_produk = ? AND CAST(stok AS UNSIGNED) >= ?"
                );

                foreach ($items as $it) {
                    $jumlah = $it['qty'];
                    $harga = $it['harga'];
                    $sub = $it['subtotal'];
                    $pid = $it['produk_id'];

                    mysqli_stmt_bind_param($stmtDetail, 'iiiii', $jumlah, $harga, $sub, $id_transaksi, $pid);
                    if (!mysqli_stmt_execute($stmtDetail)) {
                        throw new Exception('Gagal menyimpan detail penjualan.');
                    }

                    mysqli_stmt_bind_param($stmtStok, 'iii', $jumlah, $pid, $jumlah);
                    if (!mysqli_stmt_execute($stmtStok) || mysqli_stmt_affected_rows($stmtStok) === 0) {
                        throw new Exception('Stok produk "' . $it['nama_produk'] . '" tidak mencukupi saat checkout.');
                    }
                }
                mysqli_stmt_close($stmtDetail);
                mysqli_stmt_close($stmtStok);

                mysqli_commit($conn);

                $_SESSION['cart'] = array();
                $_SESSION['cart_meta'] = array();
                $_SESSION['last_order_id'] = $id_transaksi;
                $_SESSION['last_order_data'] = array(
                    'kode' => 'TRX-' . $id_transaksi,
                    'id_transaksi' => $id_transaksi,
                    'nama' => $nama,
                    'email' => $email,
                    'alamat' => $alamat,
                    'catatan' => $catatan,
                    'metode' => $metode,
                    'total' => $total,
                    'items' => $items,
                    'status' => $status_awal,
                );

                $checkout_success = true;
                $order_data = $_SESSION['last_order_data'];
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $errors[] = $e->getMessage();
            }
        }
    }
}

/* ========== TAMPILKAN STATUS PESANAN TERAKHIR (dari session + DB) ========== */
/* Tampilkan status jika baru checkout, atau keranjang kosong + ada order terakhir */
if ($checkout_success && $order_data && !empty($order_data['id_transaksi'])) {
    $order_data['status'] = fetch_status_pesanan($conn, (int)$order_data['id_transaksi']);
} elseif (!$checkout_success && empty($_SESSION['cart']) && !empty($_SESSION['last_order_id']) && !empty($_SESSION['last_order_data'])) {
    $order_data = $_SESSION['last_order_data'];
    $order_data['status'] = fetch_status_pesanan($conn, (int)$_SESSION['last_order_id']);
    $_SESSION['last_order_data']['status'] = $order_data['status'];
    $checkout_success = true;
}

/* ========== LOAD CART ITEMS ========== */
$cart_items = array();
$subtotal_all = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $pid => $qty) {
        $pid = (int)$pid;
        $qty = (int)$qty;
        if ($qty <= 0) continue;

        $p = $conn ? fetch_produk($conn, $pid) : null;

        if ($p) {
            $nama = $p['nama'];
            $harga = $p['harga'];
            $stok = $p['stok'];
        } elseif (!empty($_SESSION['cart_meta'][$pid])) {
            $m = $_SESSION['cart_meta'][$pid];
            $nama = $m['nama'];
            $harga = (int)$m['harga'];
            $stok = (int)$m['stok'];
        } else {
            continue;
        }

        if ($qty > $stok) {
            $qty = $stok;
            $_SESSION['cart'][$pid] = $qty;
            if ($qty <= 0) {
                unset($_SESSION['cart'][$pid]);
                continue;
            }
        }

        $sub = $qty * $harga;
        $subtotal_all += $sub;
        $cart_items[] = array(
            'id' => $pid,
            'nama' => $nama,
            'harga' => $harga,
            'gambar' => 'img/casing.jpg',
            'stok' => $stok,
            'qty' => $qty,
            'subtotal' => $sub,
        );
    }
}

$biaya_kirim = 0;
$total_bayar = $subtotal_all + $biaya_kirim;
$cart_count = cart_count_session();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="img/logo.png">
    <title>Keranjang Belanja - Radja Game</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Poppins:wght@300;400;500;600;700&family=Dancing+Script:wght@700&display=swap" rel="stylesheet"/>
    <link href="css/bootstrap.min.css" rel="stylesheet"/>
    <link href="css/aos.css" rel="stylesheet"/>
    <link rel="stylesheet" href="css/all.min.css"/>
    <link rel="stylesheet" href="css/style.css"/>
    <style>
        .cart-page { padding: 40px 0 80px; background: var(--light); min-height: 70vh; }
        .cart-page .page-title { font-family: "Playfair Display", serif; font-size: 2rem; color: var(--dark); margin-bottom: 8px; }
        .cart-page .page-sub { color: #888; font-size: 0.92rem; margin-bottom: 28px; }
        .cart-card { background: #fff; border-radius: 16px; box-shadow: var(--shadow); overflow: hidden; margin-bottom: 24px; }
        .cart-item { display: flex; align-items: center; gap: 18px; padding: 20px 22px; border-bottom: 1px solid #f0ece6; flex-wrap: wrap; }
        .cart-item:last-child { border-bottom: none; }
        .cart-item img { width: 90px; height: 90px; object-fit: cover; border-radius: 12px; background: #f5f0ea; }
        .cart-item-info { flex: 1; min-width: 160px; }
        .cart-item-info .name { font-weight: 600; color: var(--dark); font-size: 1rem; margin-bottom: 4px; }
        .cart-item-info .unit-price { color: #888; font-size: 0.85rem; }
        .qty-control { display: inline-flex; align-items: center; border: 1.5px solid #e8e0d5; border-radius: 10px; overflow: hidden; }
        .qty-control button { width: 36px; height: 36px; border: none; background: #faf6f1; color: var(--dark); font-size: 1.1rem; font-weight: 600; cursor: pointer; }
        .qty-control button:hover { background: var(--secondary); color: #fff; }
        .qty-control span { min-width: 40px; text-align: center; font-weight: 600; }
        .cart-item-sub { font-weight: 700; color: var(--primary); font-size: 1.05rem; min-width: 110px; text-align: right; }
        .btn-remove { background: none; border: none; color: #c0392b; font-size: 1.15rem; cursor: pointer; padding: 6px; }
        .summary-card { background: #fff; border-radius: 16px; box-shadow: var(--shadow); padding: 28px 24px; position: sticky; top: 100px; }
        .summary-card h4 { font-family: "Playfair Display", serif; font-size: 1.25rem; margin-bottom: 20px; color: var(--dark); }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 0.92rem; color: #555; }
        .summary-row.total { border-top: 1.5px solid #eee; padding-top: 14px; margin-top: 8px; font-weight: 700; font-size: 1.15rem; color: var(--dark); }
        .summary-row.total span:last-child { color: var(--primary); }
        .btn-checkout { width: 100%; background: linear-gradient(135deg, var(--primary), #c01e12); color: #fff; border: none; border-radius: 12px; padding: 14px; font-weight: 700; font-size: 0.98rem; margin-top: 18px; cursor: pointer; font-family: "Poppins", sans-serif; }
        .btn-checkout:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(232, 40, 26, 0.35); }
        .btn-continue { width: 100%; background: transparent; color: var(--secondary); border: 2px solid var(--secondary); border-radius: 12px; padding: 12px; font-weight: 600; font-size: 0.92rem; margin-top: 10px; cursor: pointer; text-align: center; display: block; font-family: "Poppins", sans-serif; text-decoration: none; }
        .btn-continue:hover { background: var(--secondary); color: #fff; }
        .empty-cart { text-align: center; padding: 60px 20px; }
        .empty-cart i { font-size: 4rem; color: #ddd; margin-bottom: 18px; }
        .empty-cart h3 { font-family: "Playfair Display", serif; color: var(--dark); margin-bottom: 10px; }
        .checkout-form .form-label { font-weight: 600; font-size: 0.88rem; color: var(--dark); margin-bottom: 6px; }
        .checkout-form .form-control, .checkout-form .form-select { border-radius: 10px; border: 1.5px solid #e8e0d5; padding: 11px 14px; font-size: 0.92rem; font-family: "Poppins", sans-serif; }
        .success-box { background: #fff; border-radius: 16px; box-shadow: var(--shadow); padding: 40px 30px; text-align: center; max-width: 640px; margin: 0 auto; }
        .success-box .check-icon { width: 72px; height: 72px; background: linear-gradient(135deg, var(--secondary), #14651a); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #fff; font-size: 2rem; }
        .order-code { display: inline-block; background: var(--cream2); color: var(--primary); font-weight: 700; padding: 8px 20px; border-radius: 8px; font-size: 1.15rem; margin: 12px 0 20px; letter-spacing: 1px; }
        .order-detail-table { width: 100%; text-align: left; margin: 20px 0; font-size: 0.9rem; }
        .order-detail-table th, .order-detail-table td { padding: 10px 8px; border-bottom: 1px solid #f0ece6; }
        .nav-cart-badge { position: absolute; top: -6px; right: -8px; background: var(--primary); color: #fff; font-size: 0.65rem; font-weight: 700; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .nav-cta { position: relative; }
        @media (max-width: 767px) {
            .cart-item { gap: 12px; padding: 16px; }
            .cart-item img { width: 70px; height: 70px; }
            .cart-item-sub { min-width: auto; text-align: left; width: 100%; }
            .summary-card { position: static; }
        }
    </style>
</head>
<body>
    <div id="topbar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="top-contact d-flex flex-wrap">
                    <span><i class="fas fa-phone-alt"></i>085731085184</span>
                    <span><i class="fas fa-envelope"></i>radjagame@gmail.com</span>
                    <span><i class="fas fa-map-marker-alt"></i>Warujayeng</span>
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg" id="nav">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <div class="blogo">
                    <div class="bico"><img src="img/logo.png" alt="Radja Game"></div>
                    <div>
                        <div class="bname">Radja<span> Game</span></div>
                        <div class="bsub">Jual beli Handphone dan Aksesoris</div>
                    </div>
                </div>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
                <i class="fas fa-bars" style="color:var(--primary);font-size:1.35rem;"></i>
            </button>
            <div class="collapse navbar-collapse" id="navmenu">
                <ul class="navbar-nav ms-auto me-3">
                    <li class="nav-item"><a class="nav-link" href="index.php#hero">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#katalog">Katalog</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#contact-section">Contact</a></li>
                </ul>
                <div class="d-flex align-items-center gap-1">
                    <a href="keranjang.php" class="nav-link nav-cta" style="position:relative;">
                        <i class="fas fa-shopping-bag me-1"></i>Keranjang
                        <span class="nav-cart-badge" id="navCartBadge" style="display:<?php echo $cart_count > 0 ? 'flex' : 'none'; ?>;"><?php echo (int)$cart_count; ?></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="cart-page">
        <div class="container">
            <?php if ($checkout_success && $order_data): ?>
            <?php
                $status_db = isset($order_data['status']) ? $order_data['status'] : 'Menunggu Konfirmasi';
                $is_disiapkan = (stripos($status_db, 'Disiapkan') !== false || stripos($status_db, 'disiapkan') !== false);
                $status_label = $is_disiapkan ? 'Pesanan Sudah Disiapkan' : 'Menunggu Konfirmasi Admin';
                $status_color = $is_disiapkan ? '#198754' : '#c77c1a';
                $status_bg = $is_disiapkan ? '#e8f5e9' : '#fff8e6';
            ?>
            <div class="success-box" data-aos="fade-up">
                <div class="check-icon"><i class="fas fa-check"></i></div>
                <h2 style="font-family:Playfair Display,serif;color:var(--dark);">Pesanan Berhasil!</h2>
                <p class="text-muted mb-1">Terima kasih, <?php echo htmlspecialchars($order_data['nama']); ?>.</p>
                <div class="order-code"><?php echo htmlspecialchars($order_data['kode']); ?></div>
                <div style="margin:16px 0;padding:12px 18px;border-radius:12px;background:<?php echo $status_bg; ?>;border:1px solid <?php echo $status_color; ?>;">
                    <strong style="color:<?php echo $status_color; ?>;font-size:1rem;">
                        <i class="fas <?php echo $is_disiapkan ? 'fa-box-open' : 'fa-clock'; ?> me-1"></i>
                        Status: <?php echo htmlspecialchars($status_label); ?>
                    </strong>
                </div>
                <table class="order-detail-table">
                    <thead><tr><th>Produk</th><th>Qty</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        <?php foreach ($order_data['items'] as $it): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($it['nama_produk']); ?></td>
                            <td><?php echo (int)$it['qty']; ?></td>
                            <td><?php echo format_rupiah($it['subtotal']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr><th colspan="2">Total</th><th style="color:var(--primary);"><?php echo format_rupiah($order_data['total']); ?></th></tr>
                    </tfoot>
                </table>
                <p class="mb-1" style="font-size:0.88rem;color:#555;"><strong>Metode:</strong> <?php echo $order_data['metode'] === 'transfer' ? 'Transfer Bank' : 'COD'; ?></p>
                <?php if ($order_data['metode'] === 'transfer'): ?>
                <div class="alert alert-info mt-3" style="font-size:0.85rem;border-radius:10px;text-align:left;">
                    Konfirmasi transfer via WhatsApp <a href="https://wa.me/6285731085184" target="_blank">0857-3108-5184</a> dengan nomor <?php echo htmlspecialchars($order_data['kode']); ?>.
                </div>
                <?php endif; ?>
                <a href="keranjang.php" class="btn-checkout" style="display:inline-block;width:auto;padding:12px 32px;text-decoration:none;margin-top:12px;background:#6c757d;">
                    <i class="fas fa-sync-alt me-1"></i> Refresh Status
                </a>
                <a href="index.php#katalog" class="btn-checkout" style="display:inline-block;width:auto;padding:12px 32px;text-decoration:none;margin-top:12px;">
                    <i class="fas fa-shopping-bag me-1"></i> Lanjut Belanja
                </a>
            </div>
            <?php else: ?>
            <h1 class="page-title" data-aos="fade-up">Keranjang Belanja</h1>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" style="border-radius:12px;">
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (empty($cart_items)): ?>
            <div class="cart-card empty-cart" data-aos="fade-up">
                <i class="fas fa-shopping-bag"></i>
                <h3>Keranjang Masih Kosong</h3>
                <p style="color:#999;">Yuk, lihat katalog dan tambahkan produk favoritmu!</p>
                <a href="index.php#katalog" class="btn-checkout" style="display:inline-block;width:auto;padding:12px 32px;text-decoration:none;">
                    <i class="fas fa-th-large me-1"></i> Mulai Belanja
                </a>
            </div>
            <?php else: ?>
            <div class="row g-4">
                <div class="col-lg-8" data-aos="fade-up">
                    <div class="cart-card" id="cartList">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item" data-id="<?php echo (int)$item['id']; ?>" id="cart-item-<?php echo (int)$item['id']; ?>">
                            <img src="<?php echo htmlspecialchars($item['gambar']); ?>" alt="">
                            <div class="cart-item-info">
                                <div class="name"><?php echo htmlspecialchars($item['nama']); ?></div>
                                <div class="unit-price"><?php echo format_rupiah($item['harga']); ?> / item</div>
                            </div>
                            <div class="qty-control">
                                <button type="button" class="btn-qty-minus" data-id="<?php echo (int)$item['id']; ?>">−</button>
                                <span class="qty-val" id="qty-<?php echo (int)$item['id']; ?>"><?php echo (int)$item['qty']; ?></span>
                                <button type="button" class="btn-qty-plus" data-id="<?php echo (int)$item['id']; ?>" data-stok="<?php echo (int)$item['stok']; ?>">+</button>
                            </div>
                            <div class="cart-item-sub" id="sub-<?php echo (int)$item['id']; ?>"><?php echo format_rupiah($item['subtotal']); ?></div>
                            <button type="button" class="btn-remove" data-id="<?php echo (int)$item['id']; ?>" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-lg-4" data-aos="fade-up">
                    <div class="summary-card">
                        <h4>Ringkasan Belanja</h4>
                        <div class="summary-row"><span>Subtotal</span><span id="sumSubtotal"><?php echo format_rupiah($subtotal_all); ?></span></div>
                        <div class="summary-row total"><span>Total</span><span id="sumTotal"><?php echo format_rupiah($total_bayar); ?></span></div>
                        <hr class="my-3">
                        <h4 style="font-size:1.1rem;">Form Pemesanan</h4>
                        <form method="POST" class="checkout-form" id="checkoutForm">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                            <input type="hidden" name="checkout" value="1">
                            <div class="mb-3">
                                <label class="form-label" for="nama">Nama Lengkap <span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="nama" name="nama" required minlength="3" value="<?php echo htmlspecialchars(isset($_POST['nama']) ? $_POST['nama'] : ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="email">Email <span style="color: red;">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="alamat">Alamat <span style="color: red;">*</span></label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required minlength="10"><?php echo htmlspecialchars(isset($_POST['alamat']) ? $_POST['alamat'] : ''); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="catatan">Catatan (opsional)</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="2"><?php echo htmlspecialchars(isset($_POST['catatan']) ? $_POST['catatan'] : ''); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="metode">Metode Pembayaran <span style="color: red;">*</span></label>
                                <select class="form-select" id="metode" name="metode_pembayaran" required>
                                    <option value="">— Pilih —</option>
                                    <!-- <option value="transfer">Transfer Bank</option> -->
                                    <option value="cod">Bayar di Tempat (COD)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-checkout"><i class="fas fa-check-circle me-1"></i> Buat Pesanan</button>
                        </form>
                        <a href="index.php#katalog" class="btn-continue"><i class="fas fa-arrow-left me-1"></i> Lanjut Belanja</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer style="background:#111;color:#aaa;padding:28px 0;text-align:center;font-size:0.85rem;">
        <div class="container">&copy; <?php echo date('Y'); ?> Radja Game</div>
    </footer>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/aos.js"></script>
    <script>
        AOS.init({ duration: 600, once: true });
        var CSRF = <?php echo json_encode($csrf); ?>;

        function formatRupiah(n) {
            return 'Rp ' + Number(n).toLocaleString('id-ID');
        }
        function updateBadge(count) {
            var b = document.getElementById('navCartBadge');
            if (!b) return;
            if (count > 0) { b.textContent = count; b.style.display = 'flex'; }
            else { b.style.display = 'none'; }
        }
        function cartAction(action, produkId, qty) {
            var fd = new FormData();
            fd.append('action', action);
            fd.append('produk_id', produkId);
            fd.append('csrf_token', CSRF);
            if (qty !== undefined) fd.append('qty', qty);
            return fetch('cart_action.php', { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function(r) { return r.json(); });
        }
        document.querySelectorAll('.btn-qty-plus').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.id, stok = parseInt(this.dataset.stok, 10);
                var span = document.getElementById('qty-' + id);
                var qty = parseInt(span.textContent, 10) + 1;
                if (qty > stok) { alert('Stok maksimal: ' + stok); return; }
                cartAction('update', id, qty).then(function(res) {
                    if (res.success) {
                        span.textContent = res.item_qty;
                        document.getElementById('sub-' + id).textContent = formatRupiah(res.subtotal);
                        updateBadge(res.cart_count);
                        recalcTotals();
                    } else alert(res.message || 'Gagal');
                });
            });
        });
        document.querySelectorAll('.btn-qty-minus').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var id = this.dataset.id;
                var span = document.getElementById('qty-' + id);
                var qty = parseInt(span.textContent, 10) - 1;
                if (qty < 1) {
                    if (!confirm('Hapus produk?')) return;
                    cartAction('remove', id).then(function(res) {
                        if (res.success) {
                            var el = document.getElementById('cart-item-' + id);
                            if (el) el.remove();
                            updateBadge(res.cart_count);
                            if (res.cart_count === 0) location.reload();
                            else recalcTotals();
                        }
                    });
                    return;
                }
                cartAction('update', id, qty).then(function(res) {
                    if (res.success) {
                        span.textContent = res.item_qty;
                        document.getElementById('sub-' + id).textContent = formatRupiah(res.subtotal);
                        updateBadge(res.cart_count);
                        recalcTotals();
                    } else alert(res.message || 'Gagal');
                });
            });
        });
        document.querySelectorAll('.btn-remove').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!confirm('Hapus produk dari keranjang?')) return;
                var id = this.dataset.id;
                cartAction('remove', id).then(function(res) {
                    if (res.success) {
                        var el = document.getElementById('cart-item-' + id);
                        if (el) el.remove();
                        updateBadge(res.cart_count);
                        if (res.cart_count === 0) location.reload();
                        else recalcTotals();
                    }
                });
            });
        });
        function recalcTotals() {
            var sub = 0;
            document.querySelectorAll('.cart-item').forEach(function(item) {
                var id = item.dataset.id;
                var qty = parseInt(document.getElementById('qty-' + id).textContent, 10);
                var priceText = item.querySelector('.unit-price').textContent.replace(/[^\d]/g, '');
                var price = parseInt(priceText, 10) || 0;
                var itemSub = qty * price;
                document.getElementById('sub-' + id).textContent = formatRupiah(itemSub);
                sub += itemSub;
            });
            document.getElementById('sumSubtotal').textContent = formatRupiah(sub);
            document.getElementById('sumTotal').textContent = formatRupiah(sub);
        }
    </script>
</body>
</html>