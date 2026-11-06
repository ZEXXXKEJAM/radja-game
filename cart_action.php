<?php
/**
 * cart_action.php — keranjang session + SELECT dari tabel produk
 * Kompatibel tanpa mysqlnd (tidak pakai mysqli_stmt_get_result)
 */
header('Content-Type: application/json; charset=utf-8');

// Tangkap semua error menjadi JSON (bukan HTTP 500 kosong)
function cart_json($ok, $msg, $extra = array()) {
    if (!is_array($extra)) $extra = array();
    $payload = array_merge(array(
        'success' => (bool)$ok,
        'message' => (string)$msg,
    ), $extra);
    $json = json_encode($payload);
    if ($json === false) {
        $json = '{"success":false,"message":"json_encode gagal"}';
    }
    echo $json;
    exit;
}

set_exception_handler(function ($e) {
    cart_json(false, 'Exception: ' . $e->getMessage());
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Ubah warning/notice jadi exception agar tertangkap
    if (!(error_reporting() & $errno)) return false;
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    if (!isset($_SESSION['cart_meta']) || !is_array($_SESSION['cart_meta'])) {
        $_SESSION['cart_meta'] = array();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }

    // Koneksi DB
    $conn = mysqli_connect('localhost', 'root', '', 'radja_game');
    if (!$conn) {
        cart_json(false, 'Koneksi database gagal: ' . mysqli_connect_error());
    }
    mysqli_set_charset($conn, 'utf8mb4');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        cart_json(false, 'Gunakan metode POST');
    }

    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    $token  = isset($_POST['csrf_token']) ? (string)$_POST['csrf_token'] : '';
    $sess   = isset($_SESSION['csrf_token']) ? (string)$_SESSION['csrf_token'] : '';

    if ($token === '' || $sess === '' || $token !== $sess) {
        cart_json(false, 'Token keamanan tidak valid. Muat ulang halaman (F5).');
    }

    /**
     * Ambil 1 produk — TANPA mysqli_stmt_get_result (hindari fatal mysqlnd)
     */
    function get_produk($conn, $id) {
        $id = (int)$id;
        if ($id < 1) return null;

        // Prepared statement + bind_result (kompatibel semua driver)
        $sql = "SELECT id_produk, nama_produk, harga_jual, stok, satuan, merk FROM produk WHERE id_produk = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            // Fallback query biasa
            $sql2 = "SELECT id_produk, nama_produk, harga_jual, stok, satuan, merk FROM produk WHERE id_produk = " . $id . " LIMIT 1";
            $res = mysqli_query($conn, $sql2);
            if (!$res) return null;
            $row = mysqli_fetch_assoc($res);
            if (!$row) return null;
            return array(
                'id'     => (int)$row['id_produk'],
                'nama'   => $row['nama_produk'],
                'harga'  => (int)$row['harga_jual'],
                'stok'   => (int)$row['stok'],
                'satuan' => $row['satuan'],
                'merk'   => $row['merk'],
            );
        }

        mysqli_stmt_bind_param($stmt, 'i', $id);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return null;
        }

        // bind_result — tidak butuh mysqlnd
        $id_produk = $nama_produk = $harga_jual = $stok = $satuan = $merk = null;
        mysqli_stmt_bind_result($stmt, $id_produk, $nama_produk, $harga_jual, $stok, $satuan, $merk);
        $found = mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if (!$found) return null;

        return array(
            'id'     => (int)$id_produk,
            'nama'   => $nama_produk,
            'harga'  => (int)$harga_jual,
            'stok'   => (int)$stok,
            'satuan' => $satuan,
            'merk'   => $merk,
        );
    }

    function cart_count() {
        $n = 0;
        foreach ($_SESSION['cart'] as $q) {
            $n += (int)$q;
        }
        return $n;
    }

    if ($action === 'add') {
        $id  = isset($_POST['produk_id']) ? (int)$_POST['produk_id'] : 0;
        $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
        if ($qty < 1) $qty = 1;
        if ($id < 1) cart_json(false, 'Produk tidak valid');

        $p = get_produk($conn, $id);
        if (!$p) {
            cart_json(false, 'Produk tidak ditemukan di database (id_produk=' . $id . '). Pastikan tabel produk terisi.');
        }

        $current = isset($_SESSION['cart'][$id]) ? (int)$_SESSION['cart'][$id] : 0;
        $new_qty = $current + $qty;

        if ($new_qty > $p['stok']) {
            cart_json(false, 'Stok tidak mencukupi. Stok tersedia: ' . $p['stok']);
        }

        $_SESSION['cart'][$id] = $new_qty;
        $_SESSION['cart_meta'][$id] = array(
            'nama'   => $p['nama'],
            'harga'  => $p['harga'],
            'stok'   => $p['stok'],
            'satuan' => $p['satuan'],
            'merk'   => $p['merk'],
        );

        cart_json(true, $p['nama'] . ' ditambahkan ke keranjang', array(
            'cart_count' => cart_count(),
            'item_qty'   => $new_qty,
            'produk_id'  => $id,
        ));
    }

    if ($action === 'update') {
        $id  = isset($_POST['produk_id']) ? (int)$_POST['produk_id'] : 0;
        $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 0;
        if ($id < 1) cart_json(false, 'Produk tidak valid');
        if (!isset($_SESSION['cart'][$id])) cart_json(false, 'Produk tidak ada di keranjang');

        if ($qty <= 0) {
            unset($_SESSION['cart'][$id], $_SESSION['cart_meta'][$id]);
            cart_json(true, 'Produk dihapus', array('cart_count' => cart_count()));
        }

        $p = get_produk($conn, $id);
        if (!$p) {
            unset($_SESSION['cart'][$id], $_SESSION['cart_meta'][$id]);
            cart_json(false, 'Produk tidak tersedia');
        }
        if ($qty > $p['stok']) {
            cart_json(false, 'Stok maksimal: ' . $p['stok']);
        }

        $_SESSION['cart'][$id] = $qty;
        $_SESSION['cart_meta'][$id] = array(
            'nama'   => $p['nama'],
            'harga'  => $p['harga'],
            'stok'   => $p['stok'],
            'satuan' => $p['satuan'],
            'merk'   => $p['merk'],
        );
        cart_json(true, 'Kuantitas diperbarui', array(
            'cart_count' => cart_count(),
            'item_qty'   => $qty,
            'subtotal'   => $qty * $p['harga'],
        ));
    }

    if ($action === 'remove') {
        $id = isset($_POST['produk_id']) ? (int)$_POST['produk_id'] : 0;
        unset($_SESSION['cart'][$id], $_SESSION['cart_meta'][$id]);
        cart_json(true, 'Produk dihapus dari keranjang', array('cart_count' => cart_count()));
    }

    if ($action === 'count') {
        cart_json(true, '', array('cart_count' => cart_count()));
    }

    cart_json(false, 'Aksi tidak dikenal: ' . $action);

} catch (Throwable $e) {
    cart_json(false, 'Error server: ' . $e->getMessage());
} catch (Exception $e) {
    cart_json(false, 'Error server: ' . $e->getMessage());
}
