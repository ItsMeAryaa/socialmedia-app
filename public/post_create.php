<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

require_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user_id'];
$content = trim($_POST['content'] ?? '');

if ($content === '') {
  header('Location: index.php?error=Isi_postingan_tidak_boleh_kosong!');
  exit;
}

// 1. Simpan postingan
$stmt = mysqli_prepare($koneksi, "INSERT INTO posts (user_id, content) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt, "is", $user_id, $content);
mysqli_stmt_execute($stmt);
$post_id = mysqli_insert_id($koneksi);

// 2. Simpan file jika ada
$allowed_img = ['jpg', 'jpeg', 'png', 'gif'];
$allowed_vid = ['mp4', 'webm'];
$allowed_audio = ['mp3', 'wav', 'ogg'];
$upload_dir = __DIR__ . '/assets/uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

// PROSES FOTO
if (!empty($_FILES['photo_files']['name'][0])) {
  foreach ($_FILES['photo_files']['name'] as $i => $name) {
    if ($_FILES['photo_files']['error'][$i] === UPLOAD_ERR_OK) {
      $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
      if (!in_array($ext, ['jpg','jpeg','png','gif'])) continue;

      $filename = uniqid('post_') . '.' . $ext;
      $filepath = $upload_dir . $filename;
      move_uploaded_file($_FILES['photo_files']['tmp_name'][$i], $filepath);

      $dbpath = 'assets/uploads/' . $filename;
      $stmt = mysqli_prepare($koneksi, "INSERT INTO post_files (post_id, file_path, file_type) VALUES (?, ?, 'image')");
      mysqli_stmt_bind_param($stmt, "is", $post_id, $dbpath);
      mysqli_stmt_execute($stmt);
    }
  }
}

// PROSES VIDEO
if (!empty($_FILES['video_files']['name'][0])) {
  foreach ($_FILES['video_files']['name'] as $i => $name) {
    if ($_FILES['video_files']['error'][$i] === UPLOAD_ERR_OK) {
      $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
      if (!in_array($ext, ['mp4','webm','mov'])) continue;

      $filename = uniqid('post_') . '.' . $ext;
      $filepath = $upload_dir . $filename;
      move_uploaded_file($_FILES['video_files']['tmp_name'][$i], $filepath);

      $dbpath = 'assets/uploads/' . $filename;
      $stmt = mysqli_prepare($koneksi, "INSERT INTO post_files (post_id, file_path, file_type) VALUES (?, ?, 'video')");
      mysqli_stmt_bind_param($stmt, "is", $post_id, $dbpath);
      mysqli_stmt_execute($stmt);
    }
  }
}

header('Location: index.php');
exit;
