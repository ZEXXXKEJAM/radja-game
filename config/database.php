<?php
/**
 * Koneksi database radja_game
 */
if (!isset($conn) || !$conn) {
    $conn = null;
    if (function_exists('mysqli_connect')) {
        $conn = @mysqli_connect('localhost', 'root', '', 'radja_game');
        if ($conn) {
            @mysqli_set_charset($conn, 'utf8mb4');
        } else {
            $conn = null;
        }
    }
}
