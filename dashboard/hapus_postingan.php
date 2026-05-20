<?php
include '../config/database.php';

$id = $_GET['id'] ?? 0;

$hapus = mysqli_query($koneksi, "DELETE FROM posts WHERE id = $id");

if ($hapus) {
  header("Location: data_postingan.php?pesan=berhasilhapus");
} else {
  header("Location: data_postingan.php?pesan=gagalhapus");
}
exit;
