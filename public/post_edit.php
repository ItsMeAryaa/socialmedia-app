<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
  $content = trim($_POST['content'] ?? '');
  $user_id = $_SESSION['user_id'];

  if (!$post_id || $content === '') {
    header('Location: index.php?error=Data_tidak_valid');
    exit;
  }

  // Pastikan post memang milik user login
  $stmt = mysqli_prepare($koneksi, "SELECT id FROM posts WHERE id = ? AND user_id = ?");
  mysqli_stmt_bind_param($stmt, "ii", $post_id, $user_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if (!mysqli_fetch_assoc($result)) {
    header('Location: index.php?error=Kamu_tidak_berhak_mengubah_post_ini.');
    exit;
  }

  // Update isi postingan
  $stmt = mysqli_prepare($koneksi, "UPDATE posts SET content = ?, updated_at = NOW() WHERE id = ?");
  mysqli_stmt_bind_param($stmt, "si", $content, $post_id);
  mysqli_stmt_execute($stmt);

  header('Location: index.php?msg=Postingan_berhasil_diubah');
  exit;
}

header('Location: index.php');
exit;
