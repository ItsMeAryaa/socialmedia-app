<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

require_once __DIR__ . '/../config/database.php';

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : null;
$user_id = $_SESSION['user_id'];

if (!$post_id) {
  header('Location: timeline.php?error=Post_tidak_ditemukan');
  exit;
}

// Pastikan postingan milik user yang login
$stmt = mysqli_prepare($koneksi, "SELECT * FROM posts WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $post_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$post = mysqli_fetch_assoc($result);

if (!$post) {
  header('Location: index.php?error=Kamu_tidak_bisa_menghapus_postingan_ini!');
  exit;
}

// Ambil dan hapus file terkait dari sistem
$stmt2 = mysqli_prepare($koneksi, "SELECT file_path FROM post_files WHERE post_id = ?");
mysqli_stmt_bind_param($stmt2, "i", $post_id);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);

while ($file = mysqli_fetch_assoc($result2)) {
  $path = __DIR__ . '/' . $file['file_path'];
  if (is_file($path)) {
    unlink($path);
  }
}

// Hapus postingan (dengan asumsi foreign key ON DELETE CASCADE aktif)
$stmt = mysqli_prepare($koneksi, "DELETE FROM posts WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $post_id, $user_id);
mysqli_stmt_execute($stmt);

header('Location: index.php?msg=Postingan_berhasil_dihapus');
exit;
