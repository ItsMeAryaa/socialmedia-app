<?php
// Koneksi PDO ke database MySQL

$DB_HOST = 'localhost';
$DB_NAME = 'db_socialmedia';
$DB_USER = 'root';       // Ganti jika user DB kamu berbeda
$DB_PASS = '';           // Ganti jika password DB kamu tidak kosong

$koneksi = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if(!$koneksi) {
  echo "Koneksi Database Gagal...!!!". mysqli_connect_error();
}

// Set charset
mysqli_set_charset($koneksi, "utf8mb4");
