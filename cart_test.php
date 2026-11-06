<?php
header('Content-Type: text/plain; charset=utf-8');
echo "=== cart_test ===\n";
echo "PHP: " . PHP_VERSION . "\n";
echo "mysqli: " . (extension_loaded('mysqli') ? 'YES' : 'NO') . "\n";
echo "mysqlnd: " . (function_exists('mysqli_fetch_all') ? 'YES' : 'maybe NO') . "\n";

$conn = @mysqli_connect('localhost', 'root', '', 'radja_game');
if (!$conn) {
    echo "DB connect: FAIL - " . mysqli_connect_error() . "\n";
    exit;
}
echo "DB connect: OK\n";

$res = @mysqli_query($conn, "SELECT id_produk, nama_produk, harga_jual, stok FROM produk LIMIT 5");
if (!$res) {
    echo "SELECT produk: FAIL - " . mysqli_error($conn) . "\n";
    exit;
}
echo "SELECT produk: OK\n";
while ($row = mysqli_fetch_assoc($res)) {
    echo "  #" . $row['id_produk'] . " " . $row['nama_produk'] . " Rp" . $row['harga_jual'] . " stok=" . $row['stok'] . "\n";
}
echo "=== SELESAI ===\n";
