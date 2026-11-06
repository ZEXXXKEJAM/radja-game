<?php
/**
 * Koneksi database admin (sama dengan frontend)
 */
$conn = mysqli_connect("localhost", "root", "", "radja_game");
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
?>
