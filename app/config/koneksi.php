<?php

$host = "localhost"; // Ganti dengan nama host database Anda
$username = "root"; // Ganti dengan username database Anda
$password = ""; // Ganti dengan password database Anda
$database = "db_pln"; // Ganti dengan nama database Anda

// Buat koneksi ke database
$koneksi = mysqli_connect($host, $username, $password, $database);

// Check koneksi
if (mysqli_connect_errno()) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set karakter set koneksi
mysqli_set_charset($koneksi, "utf8");

?>
