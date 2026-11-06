<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../admin/config/database.php';

// Supaya error SQL bisa ditangkap dengan try/catch
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* ==========================================
   FOTO PRODUK
   Foto disimpan di folder img/produk/ (root website), path-nya
   disimpan di kolom produk.gambar supaya dibaca index.php (katalog).
   ========================================== */
define('DIR_UPLOAD_PRODUK', __DIR__ . '/../img/produk/');
define('URL_UPLOAD_PRODUK', 'img/produk/');

// Tambah kolom `gambar` ke tabel produk otomatis jika belum ada
function pastikanKolomGambar($conn)
{
  try {
    $r = mysqli_query($conn, "SHOW COLUMNS FROM produk LIKE 'gambar'");
    if ($r && mysqli_num_rows($r) === 0) {
      mysqli_query($conn, "ALTER TABLE produk ADD COLUMN gambar VARCHAR(255) NULL DEFAULT NULL");
    }
  } catch (mysqli_sql_exception $e) {
    // abaikan; error akan muncul saat simpan produk
  }
}
pastikanKolomGambar($conn);

// Validasi + simpan foto. Return path (img/produk/xxx.jpg), atau null jika tidak ada file / error.
function simpanGambarProduk($field, &$error)
{
  $error = null;
  if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
    return null;
  }
  $f = $_FILES[$field];
  if ($f['error'] !== UPLOAD_ERR_OK) {
    $error = 'Upload foto gagal (kode error ' . (int) $f['error'] . '). Coba file yang lebih kecil.';
    return null;
  }
  if ($f['size'] > 2 * 1024 * 1024) {
    $error = 'Ukuran foto maksimal 2 MB.';
    return null;
  }
  $info = @getimagesize($f['tmp_name']);
  $ekstensi = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
  if (!$info || !isset($ekstensi[$info['mime']])) {
    $error = 'Format foto harus JPG, PNG, atau WEBP.';
    return null;
  }
  if (!is_dir(DIR_UPLOAD_PRODUK)) {
    @mkdir(DIR_UPLOAD_PRODUK, 0755, true);
  }
  if (!is_dir(DIR_UPLOAD_PRODUK) || !is_writable(DIR_UPLOAD_PRODUK)) {
    $error = 'Folder img/produk/ tidak bisa ditulis. Periksa izin folder.';
    return null;
  }
  // Cegah eksekusi script di folder upload
  $ht = DIR_UPLOAD_PRODUK . '.htaccess';
  if (!is_file($ht)) {
    @file_put_contents($ht, "<FilesMatch \"\\.(php|phtml|php5|phar)$\">\n  Require all denied\n</FilesMatch>\n");
  }
  $nama = 'produk_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ekstensi[$info['mime']];
  if (!move_uploaded_file($f['tmp_name'], DIR_UPLOAD_PRODUK . $nama)) {
    $error = 'Foto gagal disimpan ke folder img/produk/.';
    return null;
  }
  return URL_UPLOAD_PRODUK . $nama;
}

// Hapus file foto lama (hanya yang ada di img/produk/, gambar bawaan template tidak disentuh)
function hapusFileGambar($path)
{
  if ($path && strpos($path, URL_UPLOAD_PRODUK) === 0 && strpos($path, '..') === false) {
    $full = __DIR__ . '/../' . $path;
    if (is_file($full)) {
      @unlink($full);
    }
  }
}

/* ==========================================
   HELPER
   ========================================== */
function setFlash($type, $message)
{
  $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function redirectKembali()
{
  header('Location: kelola-produk.php');
  exit;
}

// Ambil & bersihkan input dari form (dipakai tambah & update)
function ambilInputProduk()
{
  return [
    'nama_produk' => trim($_POST['nama_produk'] ?? ''),
    'id_kategori' => (int) ($_POST['id_kategori'] ?? 0),
    'harga_beli'  => (int) ($_POST['harga_beli'] ?? 0),
    'harga_jual'  => (int) ($_POST['harga_jual'] ?? 0),
    'stok'        => (int) ($_POST['stok'] ?? 0),
    'satuan'      => trim($_POST['satuan'] ?? ''),
    'merk'        => trim($_POST['merk'] ?? ''),
  ];
}

// Validasi sederhana, return pesan error atau null jika valid
function validasiProduk($d)
{
  if ($d['nama_produk'] === '') return 'Nama produk wajib diisi.';
  if ($d['id_kategori'] <= 0)   return 'Silakan pilih kategori.';
  if ($d['harga_beli'] < 0 || $d['harga_jual'] < 0) return 'Harga tidak boleh negatif.';
  if ($d['stok'] < 0)           return 'Stok tidak boleh negatif.';
  if ($d['satuan'] === '')      return 'Satuan wajib diisi (contoh: pcs).';
  if ($d['merk'] === '')        return 'Merk wajib diisi.';
  return null;
}

/* ==========================================
   PROSES: TAMBAH PRODUK
   ========================================== */
if (isset($_POST['tambah'])) {
  $d = ambilInputProduk();
  $error = validasiProduk($d);

  if ($error) {
    setFlash('danger', $error);
    redirectKembali();
  }

  // Foto produk (opsional)
  $gambar = simpanGambarProduk('gambar', $errGambar);
  if ($errGambar) {
    setFlash('danger', $errGambar);
    redirectKembali();
  }

  try {
    $stmt = mysqli_prepare(
      $conn,
      "INSERT INTO produk (nama_produk, harga_beli, harga_jual, stok, satuan, merk, id_kategori, gambar)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param(
      $stmt,
      "siiissis",
      $d['nama_produk'],
      $d['harga_beli'],
      $d['harga_jual'],
      $d['stok'],
      $d['satuan'],
      $d['merk'],
      $d['id_kategori'],
      $gambar
    );
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    setFlash('success', 'Produk berhasil ditambahkan.');
  } catch (mysqli_sql_exception $e) {
    hapusFileGambar($gambar); // batalkan foto jika data gagal disimpan
    setFlash('danger', 'Gagal menambah produk: ' . $e->getMessage());
  }
  redirectKembali();
}

/* ==========================================
   PROSES: UPDATE PRODUK
   ========================================== */
if (isset($_POST['update'])) {
  $id = (int) ($_POST['id_produk'] ?? 0);
  $d = ambilInputProduk();
  $error = validasiProduk($d);

  if ($id <= 0) {
    $error = 'ID produk tidak valid.';
  }

  if ($error) {
    setFlash('danger', $error);
    redirectKembali();
  }

  // Foto baru (opsional). Jika kosong, foto lama dipertahankan.
  $gambarBaru = simpanGambarProduk('gambar', $errGambar);
  if ($errGambar) {
    setFlash('danger', $errGambar);
    redirectKembali();
  }

  try {
    // Ambil foto lama (untuk dihapus jika diganti)
    $gambarLama = null;
    $qLama = mysqli_prepare($conn, "SELECT gambar FROM produk WHERE id_produk = ?");
    mysqli_stmt_bind_param($qLama, "i", $id);
    mysqli_stmt_execute($qLama);
    mysqli_stmt_bind_result($qLama, $gambarLama);
    mysqli_stmt_fetch($qLama);
    mysqli_stmt_close($qLama);

    if ($gambarBaru !== null) {
      $stmt = mysqli_prepare(
        $conn,
        "UPDATE produk
         SET nama_produk = ?, harga_beli = ?, harga_jual = ?, stok = ?,
             satuan = ?, merk = ?, id_kategori = ?, gambar = ?
         WHERE id_produk = ?"
      );
      mysqli_stmt_bind_param(
        $stmt,
        "siiissisi",
        $d['nama_produk'],
        $d['harga_beli'],
        $d['harga_jual'],
        $d['stok'],
        $d['satuan'],
        $d['merk'],
        $d['id_kategori'],
        $gambarBaru,
        $id
      );
    } else {
      $stmt = mysqli_prepare(
        $conn,
        "UPDATE produk
         SET nama_produk = ?, harga_beli = ?, harga_jual = ?, stok = ?,
             satuan = ?, merk = ?, id_kategori = ?
         WHERE id_produk = ?"
      );
      mysqli_stmt_bind_param(
        $stmt,
        "siiissii",
        $d['nama_produk'],
        $d['harga_beli'],
        $d['harga_jual'],
        $d['stok'],
        $d['satuan'],
        $d['merk'],
        $d['id_kategori'],
        $id
      );
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    if ($gambarBaru !== null) {
      hapusFileGambar($gambarLama);
    }
    setFlash('success', 'Produk berhasil diperbarui.');
  } catch (mysqli_sql_exception $e) {
    if ($gambarBaru !== null) {
      hapusFileGambar($gambarBaru);
    }
    setFlash('danger', 'Gagal memperbarui produk: ' . $e->getMessage());
  }
  redirectKembali();
}

/* ==========================================
   PROSES: HAPUS PRODUK
   ========================================== */
if (isset($_POST['hapus'])) {
  $id = (int) ($_POST['id_produk'] ?? 0);

  if ($id <= 0) {
    setFlash('danger', 'ID produk tidak valid.');
    redirectKembali();
  }

  try {
    $gambarHapus = null;
    $qG = mysqli_prepare($conn, "SELECT gambar FROM produk WHERE id_produk = ?");
    mysqli_stmt_bind_param($qG, "i", $id);
    mysqli_stmt_execute($qG);
    mysqli_stmt_bind_result($qG, $gambarHapus);
    mysqli_stmt_fetch($qG);
    mysqli_stmt_close($qG);

    $stmt = mysqli_prepare($conn, "DELETE FROM produk WHERE id_produk = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    hapusFileGambar($gambarHapus);
    setFlash('success', 'Produk berhasil dihapus.');
  } catch (mysqli_sql_exception $e) {
    // Biasanya karena produk sudah dipakai di tabel lain (foreign key)
    setFlash('danger', 'Produk tidak bisa dihapus karena masih dipakai data lain (misalnya pesanan).');
  }
  redirectKembali();
}

/* ==========================================
   AMBIL DATA UNTUK TAMPILAN
   ========================================== */
// Kategori disimpan ke array supaya bisa dipakai di form tambah & modal edit
$daftarKategori = [];
$qKategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
while ($row = mysqli_fetch_assoc($qKategori)) {
  $daftarKategori[] = $row;
}

// Daftar produk + nama kategori
$qProduk = mysqli_query(
  $conn,
  "SELECT p.*, k.nama_kategori
   FROM produk p
   LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
   ORDER BY p.id_produk ASC"
);
$totalProduk = mysqli_num_rows($qProduk);

// Ambil flash message (sekali tampil)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Produk - Dashboard Admin</title>

  <!-- SEO Optimization -->
  <meta name="description" content="Kelola Produk - Radja Game">
  <meta name="author" content="Spark Admin Team">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="../img/logo.png">

  <!-- Local Third-Party Libraries (100% Offline Compatible) -->
  <link rel="stylesheet" href="assets/libs/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/libs/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/libs/apexcharts/apexcharts.css">
  <link rel="stylesheet" href="assets/libs/flatpickr/flatpickr.min.css">

  <!-- Main Design System & Custom Stylesheet -->
  <link rel="stylesheet" href="assets/css/main.css">
</head>

<body>

  <!-- ==========================================
         START: Sidebar Component
         ========================================== -->
  <div class="sidebar-wrapper" id="sidebar">
    <a href="#" class="sidebar-brand">
      <i class="bi bi-asterisk"></i>
      <span>Dashboard Admin</span>
    </a>

    <div class="sidebar-menu-section">
      <ul class="sidebar-menu-list">
        <li class="sidebar-menu-item">
          <a href="index.php" class="sidebar-menu-link" id="menu-uiforms" title="Dashboard Admin">
            <i class="bi bi-input-cursor-text"></i>
            <span>Dashboard Admin</span>
          </a>
        </li>
        <li class="sidebar-menu-item">
          <a href="kelola-produk.php" class="sidebar-menu-link active" id="menu-basictables" title="Kelola Produk">
            <i class="bi bi-table"></i>
            <span>Kelola Produk</span>
          </a>
        </li>
        <li class="sidebar-menu-item">
          <a href="lihat-pesanan.php" class="sidebar-menu-link" id="menu-uiforms" title="Lihat Pesanan">
            <i class="bi bi-input-cursor-text"></i>
            <span>Lihat Pesanan</span>
          </a>
        </li>
        <li class="sidebar-menu-item">
            <a href="transaksi.php" class="sidebar-menu-link" id="menu-uiforms" title="">
              <i class="bi bi-input-cursor-text"></i>
              <span>Transaksi</span>
            </a>
          </li>
              <li class="sidebar-menu-item">
            <a href="supplier.php" class="sidebar-menu-link" id="menu-uiforms" title="">
              <i class="bi bi-input-cursor-text"></i>
              <span>Supplier</span>
            </a>
          </li>
      </ul>
    </div>
  </div>
  <!-- END: Sidebar Component -->


  <!-- ==========================================
         START: Main Content Area
         ========================================== -->
  <div class="main-wrapper">

    <!-- START: Top Navbar Component -->
    <header class="navbar-custom">
      <div class="navbar-left">
        <button class="btn-desktop-toggle d-none d-xl-flex align-items-center justify-content-center me-3"
          id="desktop-sidebar-toggle" aria-label="Minimize Sidebar">
          <i class="bi bi-chevron-bar-left"></i>
        </button>
        <button class="sidebar-toggle-btn me-2" id="sidebar-toggle" aria-label="Toggle Navigation">
          <i class="bi bi-list"></i>
        </button>
      </div>
      <br>
      <div class="navbar-actions">
        <!-- Profile Dropdown -->
        <div class="dropdown ms-2">
          <button class="navbar-profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
            aria-expanded="false" id="profile-dropdown">
            <img src="assets/images/avatar.png" alt="Profile Image" class="navbar-profile-img">
            <span class="navbar-profile-name d-none d-md-inline">Administrator</span>
            <i class="bi bi-chevron-down navbar-profile-caret"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile" aria-labelledby="profile-dropdown">
            <li class="dropdown-header">Welcome !</li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item text-danger" href="page-login.php"><i class="bi bi-box-arrow-right"></i>
                Logout</a></li>
          </ul>
        </div>
      </div>
    </header>
    <!-- END: Top Navbar Component -->

    <!-- START: Page Header Banner -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Radja Game Produk</h1>
      </div>
    </div>
    <!-- END: Page Header Banner -->

    <!-- Notifikasi (berhasil / gagal) -->
    <?php if ($flash) { ?>
      <div class="alert alert-<?= htmlspecialchars($flash['type']); ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php } ?>

    <!-- ==========================================
           START: LIST PRODUK
           ========================================== -->
    <div class="table-card-custom">
      <div class="table-header-control">
        <h5 class="mb-0">Daftar Produk</h5>
      </div>

      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Id Produk</th>
              <th>Foto</th>
              <th>Nama Produk</th>
              <th>Kategori</th>
              <th>Harga Beli</th>
              <th>Harga Jual</th>
              <th>Stok</th>
              <th>Satuan</th>
              <th>Merk</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($totalProduk > 0) { ?>
              <?php while ($p = mysqli_fetch_assoc($qProduk)) { ?>
                <tr>
                  <td class="table-order-id">#<?= (int) $p['id_produk']; ?></td>
                  <td>
                    <?php if (!empty($p['gambar'])) { ?>
                      <img src="../<?= htmlspecialchars($p['gambar'], ENT_QUOTES); ?>" alt="Foto produk"
                        style="width:48px;height:48px;object-fit:cover;border-radius:8px;">
                    <?php } else { ?>
                      -
                    <?php } ?>
                  </td>
                  <td class="table-product-name"><?= htmlspecialchars($p['nama_produk']); ?></td>
                  <td><?= htmlspecialchars($p['nama_kategori'] ?? '-'); ?></td>
                  <td class="table-amount">Rp <?= number_format($p['harga_beli'], 0, ',', '.'); ?></td>
                  <td class="table-amount">Rp <?= number_format($p['harga_jual'], 0, ',', '.'); ?></td>
                  <td><?= (int) $p['stok']; ?></td>
                  <td><?= htmlspecialchars($p['satuan']); ?></td>
                  <td><?= htmlspecialchars($p['merk']); ?></td>
                  <td>
                    <div class="d-flex justify-content-center gap-1">
                      <!-- Tombol Edit: data dikirim lewat atribut data-* ke modal -->
                      <a href="#" class="table-btn-action btn-edit" title="Edit produk"
                        data-bs-toggle="modal" data-bs-target="#modalEdit"
                        data-id="<?= (int) $p['id_produk']; ?>"
                        data-nama="<?= htmlspecialchars($p['nama_produk'], ENT_QUOTES); ?>"
                        data-kategori="<?= (int) $p['id_kategori']; ?>"
                        data-hargabeli="<?= (int) $p['harga_beli']; ?>"
                        data-hargajual="<?= (int) $p['harga_jual']; ?>"
                        data-stok="<?= (int) $p['stok']; ?>"
                        data-satuan="<?= htmlspecialchars($p['satuan'], ENT_QUOTES); ?>"
                        data-merk="<?= htmlspecialchars($p['merk'], ENT_QUOTES); ?>"
                        data-gambar="<?= htmlspecialchars($p['gambar'] ?? '', ENT_QUOTES); ?>">
                        <i class="bi bi-pencil"></i>
                      </a>

                      <!-- Tombol Hapus -->
                      <form method="POST" class="d-inline"
                        onsubmit="return confirm('Yakin ingin menghapus produk <?= htmlspecialchars(addslashes($p['nama_produk']), ENT_QUOTES); ?>?');">
                        <input type="hidden" name="id_produk" value="<?= (int) $p['id_produk']; ?>">
                        <button type="submit" name="hapus" class="table-btn-action delete border-0" title="Hapus produk">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php } ?>
            <?php } else { ?>
              <tr>
                <td colspan="10" class="text-center py-4">Belum ada produk. Silakan tambah produk di bawah.</td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <div class="table-footer-control">
        <span class="table-pagination-info">Total: <?= $totalProduk; ?> produk</span>
      </div>
    </div>
    <!-- END: LIST PRODUK -->

    <!-- ==========================================
           START: FORM TAMBAH PRODUK
           ========================================== -->
    <div class="row g-4 mb-4 mt-1">
      <div class="col-12">
        <div class="card border-light shadow-sm p-4">
          <h2 class="h4 mb-3">Tambah Produk</h2>
          <form method="POST" enctype="multipart/form-data">
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label for="nama_produk" class="form-label">Nama Produk</label>
                <input type="text" class="form-control" id="nama_produk" name="nama_produk"
                  placeholder="Masukkan nama produk" required>
              </div>

              <div class="col-12 col-md-6">
                <label for="kategori" class="form-label">Kategori</label>
                <select class="form-select" name="id_kategori" id="kategori" required>
                  <option value="">-- Pilih Kategori --</option>
                  <?php foreach ($daftarKategori as $k) { ?>
                    <option value="<?= (int) $k['id_kategori']; ?>">
                      <?= htmlspecialchars($k['nama_kategori']); ?>
                    </option>
                  <?php } ?>
                </select>
              </div>

              <div class="col-12 col-md-6">
                <label for="harga_beli" class="form-label">Harga Beli</label>
                <input type="number" class="form-control" id="harga_beli" name="harga_beli" min="0"
                  placeholder="Masukkan harga beli" required>
              </div>

              <div class="col-12 col-md-6">
                <label for="harga_jual" class="form-label">Harga Jual</label>
                <input type="number" class="form-control" id="harga_jual" name="harga_jual" min="0"
                  placeholder="Masukkan harga jual" required>
              </div>

              <div class="col-12 col-md-4">
                <label for="stok" class="form-label">Stok</label>
                <input type="number" class="form-control" id="stok" name="stok" min="0"
                  placeholder="Masukkan stok" required>
              </div>

              <div class="col-12 col-md-4">
                <label for="satuan" class="form-label">Satuan</label>
                <input type="text" class="form-control" id="satuan" name="satuan"
                  placeholder="Contoh: pcs" value="pcs" required>
              </div>

              <div class="col-12 col-md-4">
                <label for="merk" class="form-label">Merk</label>
                <input type="text" class="form-control" id="merk" name="merk"
                  placeholder="Masukkan merk" required>
              </div>

              <div class="col-12 col-md-6">
                <label for="gambar" class="form-label">Foto Produk</label>
                <input type="file" class="form-control" id="gambar" name="gambar"
                  accept="image/jpeg,image/png,image/webp"
                  onchange="previewFoto(this, 'preview-tambah')">
                <div class="form-text">JPG, PNG, atau WEBP. Maksimal 2 MB. Foto ini tampil di katalog website.</div>
              </div>

              <div class="col-12 col-md-6 d-flex align-items-end">
                <img id="preview-tambah" src="" alt="Preview foto"
                  style="display:none;width:96px;height:96px;object-fit:cover;border-radius:10px;">
              </div>
            </div>

            <button type="submit" name="tambah" class="btn btn-success mt-4">
              <i class="bi bi-plus-lg"></i> Tambah
            </button>
          </form>
        </div>
      </div>
    </div>
    <!-- END: FORM TAMBAH PRODUK -->

    <!-- ==========================================
           START: MODAL EDIT PRODUK
           ========================================== -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <form method="POST" enctype="multipart/form-data">
            <div class="modal-header">
              <h5 class="modal-title" id="modalEditLabel">Edit Produk</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="id_produk" id="edit-id">

              <div class="row g-3">
                <div class="col-12 col-md-6">
                  <label for="edit-nama" class="form-label">Nama Produk</label>
                  <input type="text" class="form-control" id="edit-nama" name="nama_produk" required>
                </div>

                <div class="col-12 col-md-6">
                  <label for="edit-kategori" class="form-label">Kategori</label>
                  <select class="form-select" name="id_kategori" id="edit-kategori" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($daftarKategori as $k) { ?>
                      <option value="<?= (int) $k['id_kategori']; ?>">
                        <?= htmlspecialchars($k['nama_kategori']); ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>

                <div class="col-12 col-md-6">
                  <label for="edit-harga-beli" class="form-label">Harga Beli</label>
                  <input type="number" class="form-control" id="edit-harga-beli" name="harga_beli" min="0" required>
                </div>

                <div class="col-12 col-md-6">
                  <label for="edit-harga-jual" class="form-label">Harga Jual</label>
                  <input type="number" class="form-control" id="edit-harga-jual" name="harga_jual" min="0" required>
                </div>

                <div class="col-12 col-md-4">
                  <label for="edit-stok" class="form-label">Stok</label>
                  <input type="number" class="form-control" id="edit-stok" name="stok" min="0" required>
                </div>

                <div class="col-12 col-md-4">
                  <label for="edit-satuan" class="form-label">Satuan</label>
                  <input type="text" class="form-control" id="edit-satuan" name="satuan" required>
                </div>

                <div class="col-12 col-md-4">
                  <label for="edit-merk" class="form-label">Merk</label>
                  <input type="text" class="form-control" id="edit-merk" name="merk" required>
                </div>

                <div class="col-12 col-md-8">
                  <label for="edit-gambar" class="form-label">Ganti Foto Produk</label>
                  <input type="file" class="form-control" id="edit-gambar" name="gambar"
                    accept="image/jpeg,image/png,image/webp"
                    onchange="previewFoto(this, 'edit-preview')">
                  <div class="form-text">Kosongkan jika tidak ingin mengganti foto. JPG, PNG, atau WEBP, maks. 2 MB.</div>
                </div>

                <div class="col-12 col-md-4 d-flex align-items-end">
                  <img id="edit-preview" src="" alt="Foto produk"
                    style="display:none;width:96px;height:96px;object-fit:cover;border-radius:10px;">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" name="update" class="btn btn-success">Simpan Perubahan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- END: MODAL EDIT PRODUK -->

    <!-- START: Footer Component -->
    <footer class="footer-custom">
      <div class="footer-left">
        <span class="footer-logo">
          <i class="bi bi-asterisk"></i> Spark Admin
        </span>
        <span class="footer-separator">|</span>
        <span class="footer-copy">&copy; 2026 Made with <i class="bi bi-heart-fill text-danger footer-heart"></i> by
          <a href="https://sparkadminpro.gumroad.com/" target="_blank">Spark Admin</a> • Distributed by
          <a href="https://www.themewagon.com/" target="_blank">ThemeWagon</a>
        </span>
      </div>
      <div class="footer-right">
        <ul class="footer-links">
          <li><a href="#" class="footer-link">Overview</a></li>
          <li><a href="#" class="footer-link">Statistics</a></li>
          <li><a href="#" class="footer-link">Help & Documentation</a></li>
          <li><a href="#" class="footer-link">Status <span class="status-dot"></span></a></li>
        </ul>
      </div>
    </footer>
    <!-- END: Footer Component -->

  </div>
  <!-- END: Main Content Area -->

  <!-- Local Third-Party Libraries Script dependencies -->
  <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/libs/apexcharts/apexcharts.min.js"></script>
  <script src="assets/libs/flatpickr/flatpickr.min.js"></script>

  <!-- Local dashboard interactions controller -->
  <script src="assets/js/dashboard.js"></script>

  <!-- Isi form modal edit dengan data produk yang dipilih -->
  <script>
    // Preview foto yang baru dipilih
    function previewFoto(input, idPreview) {
      var img = document.getElementById(idPreview);
      if (input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
        img.style.display = 'block';
      }
    }

    document.querySelectorAll('.btn-edit').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var d = btn.dataset;
        document.getElementById('edit-id').value = d.id;
        document.getElementById('edit-nama').value = d.nama;
        document.getElementById('edit-kategori').value = d.kategori;
        document.getElementById('edit-harga-beli').value = d.hargabeli;
        document.getElementById('edit-harga-jual').value = d.hargajual;
        document.getElementById('edit-stok').value = d.stok;
        document.getElementById('edit-satuan').value = d.satuan;
        document.getElementById('edit-merk').value = d.merk;

        // Tampilkan foto saat ini & kosongkan input file
        var prev = document.getElementById('edit-preview');
        document.getElementById('edit-gambar').value = '';
        if (d.gambar) {
          prev.src = '../' + d.gambar;
          prev.style.display = 'block';
        } else {
          prev.removeAttribute('src');
          prev.style.display = 'none';
        }
      });
    });
  </script>
</body>

</html>