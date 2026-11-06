<?php
session_start();
require_once __DIR__ . "/config/database.php";
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION["csrf_token"];
$cart_count = 0;
if (!empty($_SESSION["cart"]) && is_array($_SESSION["cart"])) {
    foreach ($_SESSION["cart"] as $q) {
        $cart_count += (int)$q;
    }
}
// $conn boleh false jika DB belum siap — halaman tetap tampil

/* ------------------------------------------------------------
   KATALOG PRODUK — data diambil dari database (tabel produk + kategori)
   Struktur tabel: produk(id_produk, nama_produk, harga_beli, harga_jual,
   stok, satuan, merk, id_kategori) dan kategori(id_kategori, nama_kategori)
   ------------------------------------------------------------ */
$katalog_produk = array();
if (isset($conn) && $conn) {
    $res_katalog = @mysqli_query(
        $conn,
        "SELECT p.*, k.nama_kategori
         FROM produk p
         LEFT JOIN kategori k ON k.id_kategori = p.id_kategori
         ORDER BY p.id_produk ASC"
    );
    if ($res_katalog) {
        while ($row_katalog = mysqli_fetch_assoc($res_katalog)) {
            $katalog_produk[] = $row_katalog;
        }
        mysqli_free_result($res_katalog);
    }
}

// Gambar produk: memakai gambar yang sudah ada di folder img/ (tabel produk tidak punya kolom gambar)
function katalog_gambar($row) {
    // Foto yang diupload admin (kolom gambar) dipakai jika file-nya ada
    if (!empty($row['gambar']) && is_file(__DIR__ . '/' . $row['gambar'])) {
        return $row['gambar'];
    }
    $nama = strtolower(isset($row['nama_produk']) ? $row['nama_produk'] : '');
    $peta = array(
        'redmi'    => 'img/redmi.jpg',
        'infinix'  => 'img/infinix.jpg',
        'samsung'  => 'img/sam.jpg',
        'casing'   => 'img/casing.jpg',
        'tempered' => 'img/aksehp.jpg',
        'glass'    => 'img/aksehp.jpg',
        'headset'  => 'img/tws.jpg',
        'tws'      => 'img/tws.jpg',
        'earphone' => 'img/tws.jpg',
        'lcd'      => 'img/hp1.jpg',
        'im3'      => 'img/im3.jpg',
        'telkomsel'=> 'img/telkom.jpg',
        'simpati'  => 'img/simpati.jpg',
    );
    foreach ($peta as $kata => $gambar) {
        if (strpos($nama, $kata) !== false) {
            return $gambar;
        }
    }
    $kat = strtolower(isset($row['nama_kategori']) ? $row['nama_kategori'] : '');
    if (strpos($kat, 'kuota') !== false || strpos($kat, 'internet') !== false) return 'img/kuota.jpg';
    if (strpos($kat, 'akses') !== false) return 'img/aksehp.jpg';
    return 'img/hp.jpg';
}

// Nilai data-c untuk filter (sesuai tombol filter yang sudah ada: burgers=Handphone, pizza=Aksesoris, chicken=Kuota Internet)
function katalog_filter($nama_kategori) {
    $kat = strtolower((string)$nama_kategori);
    if (strpos($kat, 'kuota') !== false || strpos($kat, 'internet') !== false) return 'chicken';
    if (strpos($kat, 'akses') !== false) return 'pizza';
    return 'burgers';
}

function katalog_deskripsi($row) {
    if (!empty($row['deskripsi'])) {         // jika suatu saat kolom deskripsi ditambahkan
        return $row['deskripsi'];
    }
    $bagian = array();
    if (!empty($row['merk'])) $bagian[] = 'Merk ' . $row['merk'];
    $bagian[] = 'Stok tersedia: ' . (int)$row['stok'] . ' ' . (!empty($row['satuan']) ? $row['satuan'] : 'pcs');
    return implode('. ', $bagian) . '.';
}
?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="author" content="Sarab">
      <meta name="description" content="Sarab - Fast Food & Restaurant HTML Template">
      <link rel="icon" type="image/png" href="img/logo.png">
      <title>Radja Game - Pusat HP Second Dan Servis HP</title>
      <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Poppins:wght@300;400;500;600;700&family=Dancing+Script:wght@700&display=swap" rel="stylesheet"/>
      <!-- Bootstrap 5.3 -->
      <link href="css/bootstrap.min.css" rel="stylesheet"/>
      <!-- AOS Animate on Scroll -->
      <link href="css/aos.css" rel="stylesheet"/>
      <!-- Swiper -->
      <link href="css/swiper-bundle.min.css" rel="stylesheet"/>
      <!-- all min css -->
      <link rel="stylesheet" href="css/all.min.css"/>
      <!-- magnific CSS -->
      <link rel="stylesheet" href="css/magnific-popup.css"/>
      <!-- Style CSS -->
      <link rel="stylesheet" href="css/style.css" />
         <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token); ?>">
   </head>
   <body>
      <!-- ============================================================
         TOP BAR
         ============================================================ -->
      <div id="topbar">
         <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
               <div class="top-contact d-flex flex-wrap">
                  <span><i class="fas fa-phone-alt"></i>085731085184</span>
                  <span><i class="fas fa-envelope"></i>radjagame@gmail.com</span>
                  <span><i class="fas fa-map-marker-alt"></i>Warujayeng</span>
               </div>
               <div class="d-flex align-items-center gap-3">
                  <!-- <span class="ttag"><i class="fas fa-fire me-1"></i>Free Delivery Today!</span> -->
                  <div class="tsoc">
                     <a href="#"><i class="fab fa-facebook-f"></i></a>
                     <a href="#"><i class="fab fa-instagram"></i></a>
                     <a href="#"><i class="fab fa-tiktok"></i></a>
                     <a href="#"><i class="fab fa-youtube"></i></a>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- ============================================================
         NAVBAR
         ============================================================ -->
      <nav class="navbar navbar-expand-lg" id="nav">
         <div class="container">
            <a class="navbar-brand" href="img/logo.png">
               <div class="blogo">
                  <div class="bico"><img src="img/logo.png" alt=""></div>
                  <div>
                     <div class="bname">Radja<span> Game</span></div>
                     <div class="bsub">Jual beli Handphone dan Aksesoris</div>
                  </div>
               </div>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
            <i class="fas fa-bars" style="color:var(--secondary);font-size:1.35rem;"></i>
            </button>
            <div class="collapse navbar-collapse" id="navmenu">
               <ul class="navbar-nav ms-auto me-3 ">
                  <li class="nav-item"><a class="nav-link active" href="#hero">Home</a></li>
                  <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                  <li class="nav-item"><a class="nav-link" href="#katalog">Katalog</a></li>
                  <!-- <li class="nav-item"><a class="nav-link" href="#chefs">Chefs</a></li> -->
                  <!-- <li class="nav-item"><a class="nav-link" href="#reservation">Reservation</a></li> -->
                  <li class="nav-item"><a class="nav-link" href="#contact-section">Contact</a></li>
               </ul>
               <div class="d-flex align-items-center gap-1">
                  <!-- FIX 1: Search button -->
                  <!-- <button id="navSearchBtn" title="Search"><i class="fas fa-search"></i></button> -->
                  <a href="keranjang.php" class="nav-link nav-cta" style="position:relative;">
                     <i class="fas fa-shopping-bag me-1"></i>Keranjang
                     <span id="navCartBadge" class="nav-cart-badge" style="position:absolute;top:-6px;right:-8px;background:var(--primary);color:#fff;font-size:0.65rem;font-weight:700;width:18px;height:18px;border-radius:50%;display:<?php echo $cart_count > 0 ? 'flex' : 'none'; ?>;align-items:center;justify-content:center;"><?php echo (int)$cart_count; ?></span>
                  </a>
               </div>
            </div>
         </div>
      </nav>
      <!-- ============================================================
         FIX 1   SEARCH OVERLAY POPUP
         ============================================================ -->
      <div id="searchOv">
         <button class="sovclose" id="searchClose"><i class="fas fa-times"></i></button>
         <div class="sovbox">
            <h4>What are you craving today?</h4>
            <div class="sovinput">
               <input type="text" id="searchInput" placeholder="Search burgers, pizza, chicken..." autocomplete="off"/>
               <button><i class="fas fa-search"></i></button>
            </div>
            <!-- Categories inside search box -->
            <div class="sovcats">
               <div class="sovcat active" data-cat="all">
                  <img src="img/menu/1.jpg" alt=""/>All Items
               </div>
               <div class="sovcat" data-cat="burgers">
                  <img src="img/menu/1.jpg" alt=""/>Burgers
               </div>
               <div class="sovcat" data-cat="pizza">
                  <img src="img/menu/2.jpg" alt=""/>Pizza
               </div>
               <div class="sovcat" data-cat="chicken">
                  <img src="img/menu/3.jpg" alt=""/>Chicken
               </div>
               <div class="sovcat" data-cat="wraps">
                  <img src="img/menu/4.jpg" alt=""/>Wraps
               </div>
               <div class="sovcat" data-cat="pasta">
                  <img src="img/menu/5.jpg" alt=""/>Pasta
               </div>
               <div class="sovcat" data-cat="desserts">
                  <img src="img/menu/6.jpg" alt=""/>Desserts
               </div>
            </div>
            <div class="sovtrend">
               <p><i class="fas fa-fire me-1" style="color:var(--secondary);"></i>Trending Searches</p>
               <span class="ttag">Smash Burger</span>
               <span class="ttag">Nashville Chicken</span>
               <span class="ttag">Truffle Pizza</span>
               <span class="ttag">Lava Cake</span>
               <span class="ttag">Loaded Fries</span>
               <span class="ttag">Mango Shake</span>
            </div>
         </div>
      </div>
      <!-- ============================================================
         HERO
         ============================================================ -->
      <section id="hero">
         <div class="hs hs1"></div>
         <div class="hs hs2"></div>
         <div class="hbgtxt">KING</div>
         <div class="container">
            <div class="row align-items-center g-5" style="min-height:88vh;">
               <div class="col-lg-6">
                  <div class="hbadge">
                     <div class="hbi"><i class="fas fa-star"></i></div>
                     <!-- <span>#1 Rated Fast Food Restaurant in New York</span> -->
                  </div>
                  <h1 class="htitle">Pusat HP <br><span class="hl">Second </span><br/>Servis HP</h1>
                  <p class="hdesc">Pusat jual beli HP second berkualitas dengan harga terjangkau serta layanan servis HP profesional, cepat, dan terpercaya.</p>
                  <div class="d-flex flex-wrap gap-3 mb-2">
                     <!-- <a href="#menu" class="btn-red"><i class="fas fa-utensils"></i>Explore Menu</a> -->
                     <!-- FIX 2: Magnific popup video trigger -->
					 <!-- <a href="https://www.youtube.com/watch?v=RXv_uIN6e-Y" class="magnific_popup btn-play popup-youtube"> -->
						<!-- <div class="pico"><i class="fas fa-play"></i></div>
						<span>Watch Our Story</span>
					 </a> -->
                  </div>
                  <div class="hstats d-flex gap-3 flex-wrap mt-4">
                     <div class="hstat"><span class="snum">850<em>+</em></span><small>Happy Customers</small></div>
                     <div class="sdiv"></div>
                     <div class="hstat"><span class="snum">120<em>+</em></span><small>Menu Items</small></div>
                     <div class="sdiv"></div>
                     <div class="hstat"><span class="snum">15<em>+</em></span><small>Expert Chefs</small></div>
                     <div class="sdiv"></div>
                     <div class="hstat"><span class="snum">12<em>yr</em></span><small>Experience</small></div>
                  </div>
               </div>
               <div class="col-lg-6">
                  <div style="position:relative;text-align:center;">
                     <div class="hcircle">
                        <img src="img/hp.jpg" alt="HP">
                     </div>
                     <div class="fcard fc1">
                        <div class="fcoi r"><i class="fas fa-fire"></i></div>
                        <div><span class="fcnum">Warranty</span><span class="fcsm">2 week</span></div>
                     </div>
                     <div class="fcard fc2">
                        <div class="fcoi y"><i class="fas fa-star"></i></div>
                        <div><span class="fcnum">4.9/5</span><span class="fcsm">2k+ reviews</span></div>
                     </div>
                     <div class="fcard fc3">
                        <div class="fcoi g"><i class="fas fa-clock"></i></div>
                        <div><span class="fcnum">1 hari</span><span class="fcsm">Service</span></div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
      <!-- MARQUEE -->
      <div class="mqsec">
         <div class="mqtrack">
            <div class="mqitem"><i class="fas fa-circle"></i>Handphone</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Aksesoris</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Kuota Internet</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Handphone</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Aksesoris</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Kuota Internet</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Handphone</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Aksesoris</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Kuota Internet</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Handphone</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Aksesoris</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Kuota Internet</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Handphone</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Aksesoris</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Kuota Internet</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Handphone</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Aksesoris</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Kuota Internet</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Handphone</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Aksesoris</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Kuota Internet</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Handphone</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Aksesoris</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Kuota Internet</div>
            <!-- <div class="mqitem"><i class="fas fa-circle"></i>Ice Cream Shakes</div>
            <div class="mqitem"><i class="fas fa-circle"></i>Grilled Sandwiches</div> -->
         </div>
      </div>
      <!-- CATEGORY -->
      <!-- <section id="category">
         <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">What We Offer</span>
               <h2 class="stitle"><span>Category</span></h2>
               <div class="sline"></div> -->
               <!-- <p class="sdesc mx-auto" style="max-width:480px;">From sizzling burgers to exotic world cuisines - find your favourite in our menu</p> -->
            <!-- </div>
            <div class="row g-3 justify-content-center">
               <div class="col-6 col-sm-4 col-md-3 col-lg-2" data-aos="zoom-in" data-aos-delay="70">
                  <div class="catcard" data-filter="all">
                     <img class="catimg" src="img/hijau.jpg" alt=""/>
                     <div class="catnm">Handphone</div>
                     <div class="catct">99 items</div>
                  </div>
               </div>
               <div class="col-6 col-sm-4 col-md-3 col-lg-2" data-aos="zoom-in" data-aos-delay="70">
                  <div class="catcard" data-filter="burgers">
                     <img class="catimg" src="img/aksehp.jpg" alt=""/>
                     <div class="catnm">Aksesoris</div>
                     <div class="catct">24 items</div>
                  </div>
               </div>
               <div class="col-6 col-sm-4 col-md-3 col-lg-2" data-aos="zoom-in" data-aos-delay="140">
                  <div class="catcard" data-filter="pizza">
                     <img class="catimg" src="img/kuota.jpg" alt=""/>
                     <div class="catnm">Kuota Internet</div>
                     <div class="catct">18 items</div>
                  </div>
               </div> -->
               <!-- <div class="col-6 col-sm-4 col-md-3 col-lg-2" data-aos="zoom-in" data-aos-delay="210">
                  <div class="catcard" data-filter="chicken">
                     <img class="catimg" src="img/category/4.jpg" alt=""/>
                     <div class="catnm">Fried Chicken</div>
                     <div class="catct">15 items</div>
                  </div>
               </div>
               <div class="col-6 col-sm-4 col-md-3 col-lg-2" data-aos="zoom-in" data-aos-delay="280">
                  <div class="catcard" data-filter="wraps">
                     <img class="catimg" src="img/category/5.jpg" alt=""/>
                     <div class="catnm">Wraps</div>
                     <div class="catct">12 items</div>
                  </div>
               </div>
               <div class="col-6 col-sm-4 col-md-3 col-lg-2" data-aos="zoom-in" data-aos-delay="350">
                  <div class="catcard" data-filter="desserts">
                     <img class="catimg" src="img/category/6.jpg" alt=""/>
                     <div class="catnm">Desserts</div>
                     <div class="catct">20 items</div> -->
                  <!-- </div>
               </div>
            </div>
         </div>
      </section> -->
     
      <!-- ============================================================
         SLIDER PRODUK UNGGULAN
         ============================================================ -->
      <section class="product-section">
         <div class="section-title">
            <p>Produk Unggulan</p>
            <h2>Best Seller</h2>
            <span></span>
         </div>
         <div class="slider-container">
            <button class="slider-btn prev" type="button" aria-label="Produk sebelumnya">&lt;</button>
            <div class="slider-wrapper">
               <div class="product-slider">
                  <div class="product-card">
                     <div class="product-image">
                        <img src="img/redmi.jpg" alt="Redmi Note 17 Pro Max 5G">
                     </div>
                     <div class="product-info">
                        <h3>Redmi Note 17 Pro Max 5G</h3>
                        <div class="price">Rp 8.499.000</div>
                        <!-- <button class="buy-btn" type="button">Beli Sekarang</button> -->
                     </div>
                  </div>
                  <div class="product-card">
                     <div class="product-image">
                        <img src="img/infinix.jpg" alt="Infinix Smart 10">
                     </div>
                     <div class="product-info">
                        <h3>Infinix Smart 10</h3>
                        <div class="price">Rp 1.405.000</div>
                        <!-- <button class="buy-btn" type="button">Beli Sekarang</button> -->
                     </div>
                  </div>
                  <div class="product-card">
                     <div class="product-image">
                        <img src="img/sam.jpg" alt="Samsung Galaxy S23">
                     </div>
                     <div class="product-info">
                        <h3>Samsung Galaxy S23</h3>
                        <div class="price">Rp 13.999.000</div>
                        <!-- <button class="buy-btn" type="button">Beli Sekarang</button> -->
                     </div>
                  </div>
                  <div class="product-card">
                     <div class="product-image">
                        <img src="img/casing.jpg" alt="Casing">
                     </div>
                     <div class="product-info">
                        <h3>Casing</h3>
                        <div class="price">Rp 12.000</div>
                        <!-- <button class="buy-btn" type="button">Beli Sekarang</button> -->
                     </div>
                  </div>
                  <div class="product-card">
                     <div class="product-image">
                        <img src="img/tws.jpg" alt="TWS">
                     </div>
                     <div class="product-info">
                        <h3>TWS</h3>
                        <div class="price">Rp 70.000</div>
                        <!-- <button class="buy-btn" type="button">Beli Sekarang</button> -->
                     </div>
                  </div>
                  <div class="product-card">
                     <div class="product-image">
                        <img src="img/im3.jpg" alt="Kuota Internet IM3 50GB">
                     </div>
                     <div class="product-info">
                        <h3>Kuota IM3 50GB</h3>
                        <div class="price">Rp 120.000</div>
                        <!-- <button class="buy-btn" type="button">Beli Sekarang</button> -->
                     </div>
                  </div>
               </div>
            </div>
            <button class="slider-btn next" type="button" aria-label="Produk berikutnya">&gt;</button>
         </div>
         <div class="slider-dots"></div>
      </section>

       <!-- ABOUT -->
      <section id="about">
         <div class="container">
            <div class="row align-items-center g-5">
               <div class="col-lg-5" data-aos="fade-right">
                  <div class="astack">
                     <div class="aexp"><span class="anum">1</span><small>Years of<br/>Excellence</small></div>
                     <div class="amain"><img src="img/toko.png" alt="Restaurant"/></div>
                     <!-- <div class="asm"><img src="img/about2.jpg" alt=""/></div> -->
                  </div>
               </div>
               <div class="col-lg-7" data-aos="fade-left">
                  <span class="slbl">Our Story</span>
                  <h2 class="stitle text-start">We Invite You to Visit<br/>Our <span>Handphone Store</span></h2>
                  <div class="sline lft"></div>
                  <p class="sdesc mb-4">Radja game Adalah sebuah umkm yang menjual handphone baru maupun bekas dan juga  menjual beberapa aksesoris handphone seperti headseat, casing, temperedglass. Radja  game didirikan pada tanggal 1 agustus 2026 oleh Faza Ibrahim Yusuf Effendi. Arti  nama Radja Game sendiri ialah Radja (penguasa) dan Game (kehidupan seolah permainan) jadi radja game sendiri artinya ialah penguasa kehidupan.</p>
                  <div class="mb-4">
                     <div class="fti">
                        <div class="ftico r"><i class="fas fa-leaf"></i></div>
                        <div>
                           <h6>100% Original</h6>
                           <p>Produk Dijamin Original</p>
                        </div>
                     </div>
                     <div class="fti">
                        <div class="ftico y"><i class="fas fa-award"></i></div>
                        <div>
                           <h6>Garansi 2 Minggu</h6>
                           <p>Dapatkan Garansi 2 Minggu setiap pembelian Handphone</p>
                        </div>
                     </div>
                     <!-- <div class="fti">
                        <div class="ftico g"><i class="fas fa-shipping-fast"></i></div> -->
                        <!-- <div>
                           <h6>Garansi 2 Minggu</h6>
                           <p>Dapatkan Garansi 2 Minggu setiap pembelian Handphone</p>
                        </div> -->
                     </div>
                  </div>
                  <!-- <a href="#menu" class="btn-red"><i class="fas fa-book-open"></i>View Full Menu</a> -->
               </div>
            </div>
         </div>
      </section>
      <!-- ============================================================
         MENU   FIX 3 (filter works) + FIX 4 (plus opens popup)
         ============================================================ -->
      <section id="katalog">
         <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">What's You Want?</span>
               <h2 class="stitle">Our Best <span>Catalog</span></h2>
               <div class="sline"></div>
            </div>
            <!-- FIX 3   filter buttons -->
            <div class="text-center mb-4" data-aos="fade-up">
               <!-- <button class="filtbtn active" data-f="all">All</button>
               <button class="filtbtn" data-f="burgers">Handphone</button>
               <button class="filtbtn" data-f="pizza">Aksesoris</button>
               <button class="filtbtn" data-f="chicken">Kuota Internet</button> -->
               <!-- <button class="filtbtn" data-f="wraps">Wraps</button>
               <button class="filtbtn" data-f="desserts">Desserts</button>
               <button class="filtbtn" data-f="pasta">Pasta</button> -->
            </div>
            <div class="row g-4" id="mgrid">
<?php if (!empty($katalog_produk)) { ?>
               <?php foreach ($katalog_produk as $i => $p) {
                  $id_p     = (int)$p['id_produk'];
                  $nama_p   = (string)$p['nama_produk'];
                  $kat_p    = !empty($p['nama_kategori']) ? (string)$p['nama_kategori'] : 'Lainnya';
                  $harga_p  = 'Rp ' . number_format((float)$p['harga_jual'], 0, ',', '.');
                  $img_p    = katalog_gambar($p);
                  $desc_p   = katalog_deskripsi($p);
                  $filter_p = katalog_filter($kat_p);
                  $delay_p  = ($i % 3) * 80;
               ?>
               <div class="col-sm-6 col-lg-4 mwrap" data-c="<?php echo $filter_p; ?>" data-aos="fade-up" data-aos-delay="<?php echo $delay_p; ?>">
                  <div class="mcard"
                     data-img="<?php echo htmlspecialchars($img_p, ENT_QUOTES, 'UTF-8'); ?>"
                     data-title="<?php echo htmlspecialchars($nama_p, ENT_QUOTES, 'UTF-8'); ?>"
                     data-cat="<?php echo htmlspecialchars($kat_p, ENT_QUOTES, 'UTF-8'); ?>"
                     data-price="<?php echo htmlspecialchars($harga_p, ENT_QUOTES, 'UTF-8'); ?>" data-old=""
                     data-rating="4.8" data-reviews="24"
                     data-cal="-" data-time="-"
                     data-desc="<?php echo htmlspecialchars($desc_p, ENT_QUOTES, 'UTF-8'); ?>"
                     data-tags="<?php echo htmlspecialchars($kat_p, ENT_QUOTES, 'UTF-8'); ?>" data-produk-id="<?php echo $id_p; ?>">
                     <div class="mimg">
                        <img src="<?php echo htmlspecialchars($img_p, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($nama_p, ENT_QUOTES, 'UTF-8'); ?>"/>
                        <!-- <div class="mhrt"><i class="far fa-heart"></i></div> -->
                     </div>
                     <div class="mbody">
                        <div class="mcat"><?php echo htmlspecialchars($kat_p, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="mtit"><?php echo htmlspecialchars($nama_p, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="mdesc"><?php echo htmlspecialchars($desc_p, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="mfoot">
                           <div>
                              <div class="mprice"><?php echo htmlspecialchars($harga_p, ENT_QUOTES, 'UTF-8'); ?></div>
                              <!-- <div class="mstars"><i class="fas fa-star"></i> <span style="color:#bbb;font-size:.7rem;">(24)</span></div> -->
                           </div>
                           <button type="button" class="madd" title="Tambah ke Keranjang" data-produk-id="<?php echo $id_p; ?>" onclick="return addToCartClick(<?php echo $id_p; ?>, this);"><i class="fas fa-plus"></i></button>
                        </div>
                     </div>
                  </div>
               </div>
               <?php } ?>
            <?php } else { ?>
               <div class="col-12 text-center">
                  <p class="sdesc mx-auto">Produk belum tersedia saat ini.</p>
               </div>
            <?php } ?>
            </div>
            <!-- end #mgrid -->
            <!-- <div class="text-center mt-5"><a href="#" class="btn-red"><i class="fas fa-th-large"></i>View Full Menu</a></div> -->
         </div>
      </section>
	  
	  
      <!-- ============================================================
         FIX 4   MENU DETAIL POPUP MODAL
         ============================================================ -->
      <div id="menuPop">
         <div class="mpbox">
            <button class="mpclose" id="mpClose"><i class="fas fa-times"></i></button>
            <div class="mpimg"><img id="mpImg" src="" alt=""/></div>
            <div class="mpbody">
               <div id="mpCat"></div>
               <div id="mpTitle"></div>
               <div id="mpStars"></div>
               <div id="mpDesc"></div>
               <div id="mpPrice"></div>
               <div class="mpmeta" id="mpMeta"></div>
               <div class="mpqty">
                  <button class="mpqbtn" id="mpMinus">-</button>
                  <span class="mpqnum" id="mpQnum">1</span>
                  <button class="mpqbtn" id="mpPlus">+</button>
                  <span style="font-size:.82rem;color:#aaa;margin-left:9px;">portion</span>
               </div>
               <div class="mptags" id="mpTags"></div>
               <button class="mpaddcart" id="mpAddCart"><i class="fas fa-shopping-cart"></i>Add to Cart</button>
            </div>
         </div>
      </div>
      <!-- SPECIAL OFFER -->
      <!-- <section id="special">
         <div class="spbg"></div>
         <div class="container" style="position:relative;z-index:2;">
            <div class="row align-items-center g-5">
               <div class="col-lg-6" data-aos="fade-right">
                  <div class="sptag"><i class="fas fa-bolt me-1"></i>Limited Time Offer</div>
                  <h2 class="sptitle">Get 30% Off<br/>Our Signature<br/><span>Burger</span> Meal</h2>
                  <p class="spdesc">Don't miss our weekend special - grab our award-winning signature burger combo with loaded fries and a premium shake at an unbeatable price.</p>
                  <div class="cdwrap">
                     <div class="cditem"><span class="cdnum" id="cdH">08</span><span class="cdlbl">Hours</span></div>
                     <div class="cditem"><span class="cdnum" id="cdM">45</span><span class="cdlbl">Minutes</span></div>
                     <div class="cditem"><span class="cdnum" id="cdS">30</span><span class="cdlbl">Seconds</span></div>
                  </div>
                  <a href="#menu" class="btn-red"><i class="fas fa-shopping-cart"></i>Grab the Deal</a>
               </div>
               <div class="col-lg-6" data-aos="fade-left">
                  <div class="spimgw">
                     <div class="spglow"></div>
                     <div class="sppbdg"><span class="old">$24.99</span><span class="np">$17.49</span></div>
                     <img src="img/off-img.jpg" alt="Special Burger"/>
                  </div>
               </div>
            </div>
         </div>
      </section>
	   -->
	  
      <!-- ============================================================
         GALLERY   FIX 7 (click opens detail popup)
         ============================================================ -->
      <section id="gallery">
         <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">Product Exhibition</span>
               <h2 class="stitle">Let's Take a Look at<span> Our Products</span></h2>
               <div class="sline"></div>
            </div>
            <div class="ggrid" data-aos="fade-up">
               <div class="gitem"
                  data-gi="0"
                  data-gimg="img/portfolio/work1.jpg"
                  data-gtitle="Gourmet Burgers"
                  data-gdesc="Our award-winning smash burgers, hand-crafted with 100% premium beef, aged cheddar and house-made sauces.">
                  <img src="img/Smart 9 HD.jpg" alt="Handphone"/>
                  <!-- <div class="gover"><span><i class="fas fa-expand-alt"></i> Handphone</span></div> -->
               </div>
               <div class="gitem"
                  data-gi="1"
                  data-gimg="img/portfolio/work2.jpg"
                  data-gtitle="Wood-Fired Pizza"
                  data-gdesc="Authentic Neapolitan-style pizzas fired at 900deg F in our wood-burning stone oven for the perfect char.">
                  <img src="img/head phones.jpg" alt="Headphone"/>
                  <!-- <div class="gover"><span><i class="fas fa-expand-alt"></i> Headphone</span></div> -->
               </div>
               <div class="gitem"
                  data-gi="2"
                  data-gimg="img/portfolio/work3.jpg"
                  data-gtitle="Crispy Fried Chicken"
                  data-gdesc="Double-brined, hand-battered chicken fried to golden perfection using our 15-spice secret blend.">
                  <img src="img/iemm.jpg" alt="Iem"/>
                  <!-- <div class="gover"><span><i class="fas fa-expand-alt"></i> Iem</span></div> -->
               </div>
               <div class="gitem"
                  data-gi="3"
                  data-gimg="img/portfolio/work4.jpg"
                  data-gtitle="Sweet Desserts"
                  data-gdesc="Handcrafted desserts - from molten lava cakes to artisan ice cream sundaes and seasonal pastries.">
                  <img src="img/Samsung A16.jpg" alt="Casing"/>
                  <!-- <div class="gover"><span><i class="fas fa-expand-alt"></i> Casing</span></div> -->
               </div>
               <div class="gitem"
                  data-gi="4"
                  data-gimg="img/portfolio/work5.jpg"
                  data-gtitle="Fresh Wraps &amp; Rolls"
                  data-gdesc="Loaded fresh wraps packed with grilled proteins, crunchy vegetables and our house-made sauces.">
                  <img src="img/telkom.jpg" alt="Kuota"/>
                  <!-- <div class="gover"><span><i class="fas fa-expand-alt"></i> Kuota Internet</span></div> -->
               </div>
            </div>
         </div>
      </section>
      <!-- FIX 7   GALLERY POPUP -->
      <div id="galPop">
         <div class="gpbox">
            <button class="gpclose" id="gpClose"><i class="fas fa-times"></i></button>
            <img id="gpImg" src="" alt=""/>
            <div class="gpcap">
               <h5 id="gpTitle"></h5>
               <p id="gpDesc"></p>
            </div>
            <div class="gpnav">
               <button id="gpPrev"><i class="fas fa-chevron-left me-1"></i>Prev</button>
               <button id="gpNext">Next <i class="fas fa-chevron-right ms-1"></i></button>
            </div>
         </div>
      </div>
	  
	  
      <!-- ============================================================
         HISTORY   FIX 8 (alternating left/right text)
         ============================================================ -->
      <!-- <section id="history">
         <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">Our Journey</span>
               <h2 class="stitle">A History of <span>Restaurant</span></h2>
               <div class="sline"></div>
               <p class="sdesc mx-auto" style="max-width:480px;">From humble beginnings to the city's most beloved restaurant - every chapter written with passion.</p>
            </div>
            <div class="timeline" data-aos="fade-up"> -->
               <!-- ODD ? text on LEFT -->
               <!-- <div class="tli">
                  <div class="tl-left">
                     <div class="tlyear">2012</div>
                     <h5>Evolution of Restaurants</h5>
                     <p>Sarab opens its first 20-seat diner on Flavor Street. Within 3 months, lines stretch around the block every evening as word of our food spreads.</p>
                  </div>
                  <div class="tl-center">
                     <div class="tldot"></div>
                  </div>
                  <div class="tl-right">
                     <div class="tlyear">2012</div>
                     <h5>Evolution of Restaurants</h5>
                     <p>Sarab opens its first 20-seat diner on Flavor Street. Within 3 months, lines stretch around the block every evening as word of our food spreads.</p>
                  </div>
               </div> -->
               <!-- EVEN ? text on RIGHT -->
               <!-- <div class="tli">
                  <div class="tl-left">
                     <div class="tlyear">2015</div>
                     <h5>Fine Dining &amp; The Concept</h5>
                     <p>Expanding the vision - we introduced our signature tasting menu and hired our first Michelin-trained chef, elevating our craft to remarkable new heights.</p>
                  </div>
                  <div class="tl-center">
                     <div class="tldot"></div>
                  </div>
                  <div class="tl-right">
                     <div class="tlyear">2015</div>
                     <h5>Fine Dining &amp; The Concept</h5>
                     <p>Expanding the vision - we introduced our signature tasting menu and hired our first Michelin-trained chef, elevating our craft to remarkable new heights.</p>
                  </div>
               </div> -->
               <!-- ODD ? text on LEFT -->
               <!-- <div class="tli">
                  <div class="tl-left">
                     <div class="tlyear">2019</div>
                     <h5>Modern Fast Food Origins</h5>
                     <p>Launched our signature fast-food line, merging gourmet quality with speed and convenience. Within 6 months we won 3 prestigious culinary awards nationally.</p>
                  </div>
                  <div class="tl-center">
                     <div class="tldot"></div>
                  </div>
                  <div class="tl-right">
                     <div class="tlyear">2019</div>
                     <h5>Modern Fast Food Origins</h5>
                     <p>Launched our signature fast-food line, merging gourmet quality with speed and convenience. Within 6 months we won 3 prestigious culinary awards nationally.</p>
                  </div>
               </div> -->
               <!-- EVEN ? text on RIGHT -->
               <!-- <div class="tli">
                  <div class="tl-left">
                     <div class="tlyear">2026</div>
                     <h5>National Expansion</h5>
                     <p>Now operating in 8 cities across the US with an online delivery platform handling 10,000+ orders weekly - and growing every single day.</p>
                  </div>
                  <div class="tl-center">
                     <div class="tldot"></div>
                  </div>
                  <div class="tl-right">
                     <div class="tlyear">2026</div>
                     <h5>National Expansion</h5>
                     <p>Now operating in 8 cities across the US with an online delivery platform handling 10,000+ orders weekly - and growing every single day.</p>
                  </div>
               </div>
            </div>
         </div>
      </section>
	   -->
	  
      <!-- CHEFS -->
      <section id="chefs">
         <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">Workshop Team</span>
               <h2 class="stitle">Meet Our<span> Team</span></h2>
               <div class="sline"></div>
            </div>
            <div class="row g-4">
               <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="0">
                  <div class="chcard">
                     <div class="chimg">
                        <img src="img/box1 (1).jpeg" alt=""/>
                        <div class="chsoc"><a href="#"><i class="fab fa-instagram"></i></a><a href="#"><i class="fab fa-facebook-f"></i></a><a href="#"><i class="fab fa-twitter"></i></a></div>
                     </div>
                     <div class="chbody">
                        <div class="chnm">-</div>
                        <div class="chrole">-</div>
                        <div class="chexp">-</div>
                     </div>
                  </div>
               </div>
               <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="80">
                  <div class="chcard">
                     <div class="chimg">
                        <img src="img/box2.jpeg" alt=""/>
                        <div class="chsoc"><a href="#"><i class="fab fa-instagram"></i></a><a href="#"><i class="fab fa-facebook-f"></i></a><a href="#"><i class="fab fa-twitter"></i></a></div>
                     </div>
                     <div class="chbody">
                        <div class="chnm">-</div>
                        <div class="chrole">-</div>
                        <div class="chexp">-</div>
                     </div>
                  </div>
               </div>
               <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="160">
                  <div class="chcard">
                     <div class="chimg">
                        <img src="img/box3.jpeg" alt=""/>
                        <div class="chsoc"><a href="#"><i class="fab fa-instagram"></i></a><a href="#"><i class="fab fa-facebook-f"></i></a><a href="#"><i class="fab fa-twitter"></i></a></div>
                     </div>
                     <div class="chbody">
                        <div class="chnm">-</div>
                        <div class="chrole">-</div>
                        <div class="chexp">-</div>
                     </div>
                  </div>
               </div>
               <div class="col-sm-6 col-lg-3" data-aos="fade-up" data-aos-delay="240">
                  <div class="chcard">
                     <div class="chimg">
                        <img src="img/box4.jpeg" alt=""/>
                        <div class="chsoc"><a href="#"><i class="fab fa-instagram"></i></a><a href="#"><i class="fab fa-facebook-f"></i></a><a href="#"><i class="fab fa-twitter"></i></a></div>
                     </div>
                     <div class="chbody">
                        <div class="chnm">-</div>
                        <div class="chrole">-</div>
                        <div class="chexp">-</div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
	  
	  
      <!-- HOURS -->
      <section id="hours">
         <div class="hrsbg"></div>
         <div class="container" style="position:relative;z-index:2;">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl" style="color:#a5d6bc;">Opening Hours</span>
               <h2 class="stitle" style="color:#fff;">We're Open <span style="color:var(--secondary);">For You</span></h2>
               <div class="sline"></div>
            </div>
            <div class="row g-4 align-items-stretch justify-content-center hours-info-row">
               <div class="col-lg-5 col-md-6" data-aos="fade-right">
                  <div class="hrscard">
                     <div class="hrsrow">
                        <span class="hrsday"><i class="fas fa-calendar-day me-2" style="color:var(--secondary);"></i>Monday - Tuesday</span>
                        <div class="d-flex align-items-center gap-2">
                           <div class="hdot off"></div>
                           <span class="hrstime" style="color:#ff6b6b;">Closed</span>
                        </div>
                     </div>
                     <div class="hrsrow">
                        <span class="hrsday"><i class="fas fa-calendar-day me-2" style="color:var(--secondary);"></i>Wednesday - Thursday</span>
                        <div class="d-flex align-items-center gap-2">
                           <div class="hdot on"></div>
                           <span class="hrstime">09:00 AM - 10:00 PM</span>
                        </div>
                     </div>
                     <div class="hrsrow">
                        <span class="hrsday"><i class="fas fa-calendar-day me-2" style="color:var(--secondary);"></i>Friday</span>
                        <div class="d-flex align-items-center gap-2">
                           <div class="hdot on"></div>
                           <span class="hrstime">09:00 AM - 11:00 PM</span>
                        </div>
                     </div>
                     <div class="hrsrow">
                        <span class="hrsday"><i class="fas fa-calendar-day me-2" style="color:var(--secondary);"></i>Saturday</span>
                        <div class="d-flex align-items-center gap-2">
                           <div class="hdot on"></div>
                           <span class="hrstime">10:00 AM - 11:30 PM</span>
                        </div>
                     </div>
                     <div class="hrsrow">
                        <span class="hrsday"><i class="fas fa-calendar-day me-2" style="color:var(--secondary);"></i>Sunday</span>
                        <div class="d-flex align-items-center gap-2">
                           <div class="hdot on"></div>
                           <span class="hrstime">11:00 AM - 09:00 PM</span>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- <div class="col-lg-3" data-aos="zoom-in">
                  <div class="hrscta">
                     <i class="fas fa-truck-fast fa-2x mb-3" style="color:rgba(255,255,255,.8);"></i>
                     <h4>Order Online</h4>
                     <p>Get hot food delivered in 25 minutes</p>
                     <a href="#menu" class="btnw">Order Now ?</a>
                  </div>
               </div> -->
               <div class="col-lg-5 col-md-6" data-aos="fade-left">
                  <div class="hrscard">
                     <h5 style="color:#fff;margin-bottom:18px;font-family:'Poppins',sans-serif;font-size:.95rem;font-weight:700;"><i class="fas fa-map-marker-alt me-2" style="color:var(--secondary);"></i>Find Us</h5>
                     <div class="hrsrow"><span class="hrsday"><i class="fas fa-location-dot me-2" style="color:var(--secondary);"></i>Address</span><span class="hrstime" style="font-size:.8rem;">42 Flavor Street, NY</span></div>
                     <div class="hrsrow"><span class="hrsday"><i class="fas fa-phone me-2" style="color:var(--secondary);"></i>Phone</span><span class="hrstime" style="font-size:.8rem;">+1 (800) 123-4567</span></div>
                     <div class="hrsrow"><span class="hrsday"><i class="fas fa-envelope me-2" style="color:var(--secondary);"></i>Email</span><span class="hrstime" style="font-size:.8rem;">hello@sarabfood.com</span></div>
                     <div class="hrsrow"><span class="hrsday"><i class="fas fa-envelope me-2" style="color:var(--secondary);"></i>Instagram</span><span class="hrstime" style="font-size:.8rem;">@fazaibrahimm</span></div>
                  </div>
               </div>
            </div>
         </div>
      </section>
	  
	  
      <!-- TESTIMONIALS -->
      <!-- <section id="testimonials">
         <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">What People Say</span>
               <h2 class="stitle">Our Customers <span>Feedback</span></h2>
               <div class="sline"></div>
            </div>
            <div class="swiper tesSwiper" data-aos="fade-up">
               <div class="swiper-wrapper">
                  <div class="swiper-slide">
                     <div class="tescard">
                        <div class="tesq">"</div>
                        <div class="tess"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="testxt">Honestly the best burgers I've ever had. The smash burger is incredible - perfectly crispy edges, juicy inside, and those pickles! We come every Friday now.</p>
                        <div class="tesauth">
                           <img src="img/testimonial/1.jpg" alt=""/>
                           <div>
                              <div class="tesnm">Monica Wilber</div>
                              <div class="tesrl">Regular Customer</div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="swiper-slide">
                     <div class="tescard">
                        <div class="tesq">"</div>
                        <div class="tess"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="testxt">Ordered delivery and the food arrived hot and fresh in 22 minutes. Portions are generous. Sarab has become my go-to comfort food spot without question.</p>
                        <div class="tesauth">
                           <img src="img/testimonial/2.jpg" alt=""/>
                           <div>
                              <div class="tesnm">Cameron Fox</div>
                              <div class="tesrl">Food Blogger</div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="swiper-slide">
                     <div class="tescard">
                        <div class="tesq">"</div>
                        <div class="tess"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="testxt">The truffle pasta blew my mind. I didn't expect that quality from a fast food place. Great ambiance, super friendly staff. Highly recommended!</p>
                        <div class="tesauth">
                           <img src="img/testimonial/3.jpg" alt=""/>
                           <div>
                              <div class="tesnm">Priya Sharma</div>
                              <div class="tesrl">Food Enthusiast</div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="swiper-slide">
                     <div class="tescard">
                        <div class="tesq">"</div>
                        <div class="tess"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                        <p class="testxt">Catered our office party of 50 people and everything was flawless. Fresh, delicious, on time and well presented. Nashville chicken was the absolute star!</p>
                        <div class="tesauth">
                           <img src="img/testimonial/4.jpg" alt=""/>
                           <div>
                              <div class="tesnm">David Park</div>
                              <div class="tesrl">Corporate Client</div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="swiper-pagination mt-4" style="position:static;"></div>
            </div>
         </div>
      </section> -->
	  
      <!-- RESERVATION FORM -->
      <!-- <section id="reservation">
         <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">Book a Table</span>
               <h2 class="stitle">Make a <span>Reservation</span></h2>
               <div class="sline"></div>
               <p class="sdesc mx-auto" style="max-width:480px;">Reserve your table for a memorable dining experience. We recommend booking 24 hours in advance for weekend evenings.</p>
            </div>
            <div class="row g-4 align-items-start">
               <div class="col-lg-4" data-aos="fade-right">
                  <div style="background:var(--dark);border-radius:18px;padding:36px;">
                     <h4 style="color:#fff;font-size:1.3rem;margin-bottom:8px;">Contact Info</h4>
                     <p style="color:rgba(255,255,255,.55);font-size:.85rem;margin-bottom:26px;">We're happy to help you plan the perfect dining experience.</p>
                     <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center gap-3">
                           <div style="width:46px;height:46px;border-radius:11px;background:rgba(232,40,26,.2);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1.1rem;flex-shrink:0;"><i class="fas fa-clock"></i></div>
                           <div><strong style="display:block;color:#ccc;font-size:.78rem;text-transform:uppercase;letter-spacing:.8px;">Opening Hours</strong><span style="color:#fff;font-size:.87rem;">Wed - Sun, 9 AM - 11 PM</span></div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                           <div style="width:46px;height:46px;border-radius:11px;background:rgba(232,40,26,.2);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1.1rem;flex-shrink:0;"><i class="fas fa-phone-alt"></i></div>
                           <div><strong style="display:block;color:#ccc;font-size:.78rem;text-transform:uppercase;letter-spacing:.8px;">Call for Booking</strong><span style="color:#fff;font-size:.87rem;">+1 (800) 123-4567</span></div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                           <div style="width:46px;height:46px;border-radius:11px;background:rgba(232,40,26,.2);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1.1rem;flex-shrink:0;"><i class="fas fa-users"></i></div>
                           <div><strong style="display:block;color:#ccc;font-size:.78rem;text-transform:uppercase;letter-spacing:.8px;">Group Dining</strong><span style="color:#fff;font-size:.87rem;">Special menus for 10+ guests</span></div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                           <div style="width:46px;height:46px;border-radius:11px;background:rgba(232,40,26,.2);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1.1rem;flex-shrink:0;"><i class="fas fa-map-marker-alt"></i></div>
                           <div><strong style="display:block;color:#ccc;font-size:.78rem;text-transform:uppercase;letter-spacing:.8px;">Location</strong><span style="color:#fff;font-size:.87rem;">42 Flavor Street, NY</span></div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-lg-8" data-aos="fade-left">
                  <div class="fcard">
                     <div class="row g-3">
                        <div class="col-sm-6"><label class="flbl">Full Name *</label><input type="text" class="fctrl" placeholder="John Doe"/></div>
                        <div class="col-sm-6"><label class="flbl">Phone Number *</label><input type="tel" class="fctrl" placeholder="+1 (800) 000-0000"/></div>
                        <div class="col-sm-6"><label class="flbl">Email Address *</label><input type="email" class="fctrl" placeholder="you@email.com"/></div>
                        <div class="col-sm-6">
                           <label class="flbl">Number of Guests *</label>
                           <select class="fctrl">
                              <option>1 Person</option>
                              <option>2 People</option>
                              <option>3 - 4 People</option>
                              <option>5 - 6 People</option>
                              <option>7 -10 People</option>
                              <option>10+ People</option>
                           </select>
                        </div>
                        <div class="col-sm-6"><label class="flbl">Date *</label><input type="date" class="fctrl"/></div>
                        <div class="col-sm-6">
                           <label class="flbl">Time *</label>
                           <select class="fctrl">
                              <option>09:00 AM</option>
                              <option>10:00 AM</option>
                              <option>11:00 AM</option>
                              <option>12:00 PM</option>
                              <option>01:00 PM</option>
                              <option>02:00 PM</option>
                              <option>06:00 PM</option>
                              <option>07:00 PM</option>
                              <option>08:00 PM</option>
                              <option>09:00 PM</option>
                              <option>10:00 PM</option>
                           </select>
                        </div>
                        <div class="col-12"><label class="flbl">Special Requests</label><textarea class="fctrl" rows="3" placeholder="Allergies, dietary needs, special occasions..."></textarea></div>
                        <div class="col-12"><button class="btn-red w-100 justify-content-center" id="resBtn"><i class="fas fa-calendar-check"></i>Confirm Reservation</button></div>
                     </div>
                     <div class="sucmsg" id="resOk">
                        <i class="fas fa-check-circle"></i>
                        <p>Table reserved! We'll confirm via email shortly.</p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>
	  
      <!-- BLOG -->
      <section id="blog">
         <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">News &amp; Updates</span>
               <h2 class="stitle">Our Latest <span>Blog</span> Posts</h2>
               <div class="sline"></div>
            </div>
            <div class="row g-4">
               <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="0">
                  <div class="blcard">
                     <div class="blimg">
                        <img src="img/hape.jpg" alt=""/>
                        <!-- <div class="bldatebdg"><span class="bd">14</span><span class="bm">Mar</span></div> -->
                     </div>
                     <div class="blbody">
                        <div class="bltag">Handphone</div>
                        <div class="bltit"><a href="#">Handphone: Berbagai jenis hp android</a></div>
                        <!-- <div class="blmeta"><span><i class="fas fa-user"></i>James Writer</span><span><i class="fas fa-comment"></i>24 Comments</span></div>
                        <a href="#" class="blmore">Read More <i class="fas fa-arrow-right"></i></a> -->
                     </div>
                  </div>
               </div>
               <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="80">
                  <div class="blcard">
                     <div class="blimg">
                        <img src="img/akcecoris.png" alt=""/>
                        <!-- <div class="bldatebdg"><span class="bd">28</span><span class="bm">Feb</span></div> -->
                     </div>
                     <div class="blbody">
                        <div class="bltag">Aksesoris</div>
                        <div class="bltit"><a href="#">Aksesoris: Casing, Headphone, Earphone, Cas, Iem</a></div>
                        <!-- <div class="blmeta"><span><i class="fas fa-user"></i>Sarah Grain</span><span><i class="fas fa-comment"></i>18 Comments</span></div>
                        <a href="#" class="blmore">Read More <i class="fas fa-arrow-right"></i></a> -->
                     </div>
                  </div>
               </div>
               <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="160">
                  <div class="blcard">
                     <div class="blimg">
                        <img src="img/simpati.jpg" alt=""/>
                        <!-- <div class="bldatebdg"><span class="bd">05</span><span class="bm">Jan</span></div> -->
                     </div>
                     <div class="blbody">
                        <div class="bltag">Kuota Internet</div>
                        <div class="bltit"><a href="#">Kuota Internet: IM3, Telkomsel, Simpati, Axis, XL</a></div>
                        <!-- <div class="blmeta"><span><i class="fas fa-user"></i>Chef Marcus</span><span><i class="fas fa-comment"></i>32 Comments</span></div>
                        <a href="#" class="blmore">Read More <i class="fas fa-arrow-right"></i></a> -->
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section> 
	  
      <!-- NEWSLETTER -->
      <!-- <section id="newsletter">
         <div class="nlbg"></div>
         <div class="container">
            <div class="nlw text-center" data-aos="zoom-in">
               <span class="slbl" style="color:rgba(255,255,255,.7);">Stay Connected</span>
               <h2 class="mb-3" style="color:#fff;">Subscribe &amp; Get Exclusive <span style="color:var(--secondary);">Deals</span></h2>
               <p class="mb-4" style="color:rgba(255,255,255,.78);">Get 15% off your first order plus early access to new menu items</p>
               <div class="nl-form-wrap">
                  <input class="nlinput" type="email" id="nlEmail" placeholder="Enter your email address..."/>
                  <button class="nlbtn" id="nlBtn"><i class="fas fa-paper-plane me-1"></i>Subscribe</button>
               </div>
               <p style="color:rgba(255,255,255,.45);font-size:.76rem;margin-top:11px;"><i class="fas fa-lock me-1"></i>No spam, unsubscribe anytime.</p>
            </div>
         </div>
      </section> -->
	  
      <!-- ============================================================
         FIX 6   CONTACT FORM
         ============================================================ -->
      <section id="contact-section">
         <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
               <span class="slbl">Get In Touch</span>
               <h2 class="stitle">Contact <span>Us</span></h2>
               <div class="sline"></div>
               <p class="sdesc mx-auto" style="max-width:480px;">Have a question, feedback, or want to plan a special event? We'd love to hear from you.</p>
            </div>
            <div class="row g-4 align-items-stretch justify-content-center contact-layout-row">
               <div class="col-lg-4 col-md-5" data-aos="fade-right">
                  <div class="ctdark">
                     <h4>Let's Talk</h4>
                     <p class="ctsub">We typically respond within 2 hours during business hours.</p>
                     <div class="ctitem">
                        <div class="cticon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="ctinfo"><strong>Address</strong><span>Jl. Warujayeng-Kediri<br/> Pule</span></div>
                     </div>
                     <div class="ctitem">
                        <div class="cticon"><i class="fas fa-phone-alt"></i></div>
                        <div class="ctinfo"><strong>Phone</strong><span>085731085184</span></div>
                     </div>
                     <div class="ctitem">
                        <div class="cticon"><i class="fas fa-envelope"></i></div>
                        <div class="ctinfo"><strong>Email</strong><span>radjagame@gmail.com</span></div>
                     </div>
                     <div class="ctitem">
                        <div class="cticon"><i class="fas fa-clock"></i></div>
                        <div class="ctinfo"><strong>Working Hours</strong><span>Mon - Sun: 9 AM - 10 PM</span></div>
                     </div>
                     <div class="ctsocrow">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                     </div>
                  </div>
               </div>
               <div class="col-lg-8 col-md-7" data-aos="fade-left">
                  <div class="ctmap-wrap">
                      <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4500.252465397142!2d112.01486047555514!3d-7.634090692381639!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e784f001393fe1b%3A0xebd13462dc3dd671!2sRadja%20Game!5e1!3m2!1sen!2sid!4v1793416671917!5m2!1sen!2sid"
                        width="100%"
                        height="520"
                        style="border:0; width:100%; height:520px; min-height:520px; display:block;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"></iframe>
                     <!-- <div class="row g-3"> -->
                       
                        <!-- <div class="col-sm-6"><label class="flbl">Your Name *</label><input type="text" class="fctrl" placeholder="John Doe"/></div>
                        <div class="col-sm-6"><label class="flbl">Email Address *</label><input type="email" class="fctrl" placeholder="you@email.com"/></div>
                        <div class="col-sm-6"><label class="flbl">Phone Number</label><input type="tel" class="fctrl" placeholder="+1 (800) 000-0000"/></div> -->
                        <!-- <div class="col-sm-6"> -->
                           <!-- <label class="flbl">Subject *</label>
                           <select class="fctrl">
                              <option>General Inquiry</option>
                              <option>Catering &amp; Events</option>
                              <option>Feedback</option>
                              <option>Partnership</option>
                              <option>Media &amp; Press</option>
                           </select> -->
                        <!-- </div> -->
                        <!-- <div class="col-12"><label class="flbl">Message *</label><textarea class="fctrl" rows="5" placeholder="Write your message here..."></textarea></div>
                        <div class="col-12"><button class="btn-red" id="ctcBtn"><i class="fas fa-paper-plane"></i>Send Message</button></div> -->
                     <!-- </div>
                     <div class="sucmsg" id="ctcOk">
                        <i class="fas fa-check-circle"></i>
                        <p>Message sent! We'll reply within 2 hours.</p>
                     </div> -->
                  </div>
               </div>
            </div>
         </div>
      </section>
	  
      <!-- FOOTER -->
      <footer>
         <div class="container">
            <div class="row g-5">
               <div class="col-lg-4">
                  <div class="fnm">Radja<span> Game</span></div>
                  <p class="fdesc">Pusat jual beli HP second berkualitas dengan harga terjangkau serta layanan servis HP profesional, cepat, dan terpercaya.</p>
                  <div class="fsoc">
                     <a href="#"><i class="fab fa-facebook-f"></i></a>
                     <a href="#"><i class="fab fa-instagram"></i></a>
                     <a href="#"><i class="fab fa-twitter"></i></a>
                     <a href="#"><i class="fab fa-youtube"></i></a>
                     <a href="#"><i class="fab fa-tiktok"></i></a>
                  </div>
               </div>
               <div class="col-sm-6 col-lg-2">
                  <div class="ftit">Quick Links</div>
                  <ul class="flinks ps-0">
                     <li><a href="#hero"><i class="fas fa-chevron-right"></i>Home</a></li>
                     <li><a href="#about"><i class="fas fa-chevron-right"></i>About</a></li>
                     <li><a href="#menu"><i class="fas fa-chevron-right"></i>Katalog</a></li>
                     <!-- <li><a href="#reservation"><i class="fas fa-chevron-right"></i>Reservation</a></li>
                     <li><a href="#blog"><i class="fas fa-chevron-right"></i>Blog</a></li> -->
                     <li><a href="#contact-section"><i class="fas fa-chevron-right"></i>Contact</a></li>
                  </ul>
               </div>
               <div class="col-sm-6 col-lg-2">
                  <div class="ftit">Our Menu</div>
                  <ul class="flinks ps-0">
                     <li><a href="#menu"><i class="fas fa-chevron-right"></i>Handphone</a></li>
                     <li><a href="#menu"><i class="fas fa-chevron-right"></i>Aksesoris</a></li>
                     <li><a href="#menu"><i class="fas fa-chevron-right"></i>Kuota Internet</a></li>
                     <!-- <li><a href="#menu"><i class="fas fa-chevron-right"></i>Wraps &amp; Rolls</a></li>
                     <li><a href="#menu"><i class="fas fa-chevron-right"></i>Pasta</a></li>
                     <li><a href="#menu"><i class="fas fa-chevron-right"></i>Desserts</a></li> -->
                  </ul>
               </div>
               <div class="col-lg-4">
                  <div class="ftit">Get In Touch</div>
                  <div class="fci">
                     <div class="fciico"><i class="fas fa-map-marker-alt"></i></div>
                     <div class="fciinfo"><strong>Address</strong>Jl. Warujayeng-Kediri Pule</div>
                  </div>
                  <div class="fci">
                     <div class="fciico"><i class="fas fa-phone-alt"></i></div>
                     <div class="fciinfo"><strong>Phone</strong>085731085184</div>
                  </div>
                  <div class="fci">
                     <div class="fciico"><i class="fas fa-envelope"></i></div>
                     <div class="fciinfo"><strong>Email</strong>radjagame@gmail.com</div>
                  </div>
                  <div class="fci">
                     <div class="fciico"><i class="fas fa-clock"></i></div>
                     <div class="fciinfo"><strong>Hours</strong>Mon - Sun: 9 AM - 10 PM</div>
                  </div>
               </div>
            </div>
         </div>
         <div class="fbot">
            <div class="container">
               <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                  <p>&copy 2026 <span>Radja Game</span>. All Rights Reserved by <a target="_blank" class="mx-0 fw-bold text-success" href="https://bestwpware.com/">Group 3 TIF Class A</a>. Made with <span><i class="fas fa-heart"></i></span>  <br>Distributed by <a target="_blank" class="mx-0 fw-bold text-success" href="https://themewagon.com">ThemeWagon</a></p>
                  <div><a href="#">Privacy Policy</a><a href="#">Terms</a><a href="#">Cookies</a></div>
               </div>
            </div>
         </div>
      </footer>
      <!-- Floating cart -->
      <!-- <div class="cartfl"><i class="fas fa-shopping-cart"></i><span>My Cart</span><div class="ccount" id="cartCount">0</div></div> -->
      <!-- Back to top -->
      <button id="btt" onclick="window.scrollTo({top:0,behavior:'smooth'})"><i class="fas fa-chevron-up"></i></button>
    
	<!-- jQuery -->
      <script src="js/jquery-3.7.1.min.js"></script>
      <!-- Bootstrap 5 -->
      <script src="js/bootstrap.bundle.min.js"></script>
      <!-- AOS -->
      <script src="js/aos.js"></script>
      <!-- Swiper -->
      <script src="js/swiper-bundle.min.js"></script>
      <!-- CounterUp -->
      <script src="js/jquery.magnific-popup.min.js"></script>
      <!-- Main js -->
      <script>window.__CSRF_TOKEN = <?php echo json_encode($csrf_token); ?>;</script>
      <script src="js/cart.js"></script>
      <script src="js/main.js"></script>
      <!-- Product slider -->
      <script src="js/script.js"></script>
   


<style>
.nav-cart-badge { line-height: 1; }
.toast-cart {
  position: fixed; bottom: 28px; right: 28px; z-index: 99999;
  background: #1a1a1a; color: #fff; padding: 14px 22px; border-radius: 12px;
  font-size: 0.9rem; font-family: Poppins, sans-serif;
  box-shadow: 0 8px 30px rgba(0,0,0,0.25); display: none;
  align-items: center; gap: 10px; max-width: 340px;
}
.toast-cart.show { display: flex; animation: toastIn 0.35s ease; }
.toast-cart i { color: var(--secondary); font-size: 1.2rem; }
@keyframes toastIn { from { opacity:0; transform: translateY(16px); } to { opacity:1; transform: none; } } */
Feedback tombol + saat berhasil tambah
.madd.added {
  background: linear-gradient(135deg, var(--green), #1a4a35) !important;
  transform: scale(1.1);
}
</style>
<div class="toast-cart" id="toastCart"><i class="fas fa-check-circle"></i><span id="toastCartMsg">Ditambahkan</span></div>
</body>
</html>