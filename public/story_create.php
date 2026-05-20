<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$type = $_POST['type'] ?? '';
$content = ($type === 'text') ? ($_POST['text_content'] ?? null) : ($_POST['file_description'] ?? null);
$file_path = null;

// Handle file upload jika bukan tipe text
if ($type !== 'text' && isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
  $targetDir = "assets/uploads/stories/";
  if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

  $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
  $filename = uniqid('story_') . '.' . $ext;
  $fullPath = $targetDir . $filename;

  if (move_uploaded_file($_FILES['file']['tmp_name'], $fullPath)) {
    $file_path = $fullPath;
  }
}

$expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

// Siapkan query insert
$stmt = mysqli_prepare($koneksi, "INSERT INTO stories (user_id, type, content, file_path, expires_at) VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "issss", $user_id, $type, $content, $file_path, $expires_at);
mysqli_stmt_execute($stmt);

header("Location: index.php");
exit;
