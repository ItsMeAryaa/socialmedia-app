<?php
session_start();
require_once '../config/database.php';

$id = $_GET['id'] ?? null;

if ($id) {
  $hapus = mysqli_query($koneksi, "DELETE FROM comments WHERE id = $id OR parent_id = $id");

  if ($hapus) {
    header("Location: data_komentar.php?pesan=berhasilhapus");
  } else {
    header("Location: data_komentar.php?pesan=gagalhapus");
  }
  exit;
} else {
  header("Location: data_komentar.php?pesan=gagalhapus");
  exit;
}
