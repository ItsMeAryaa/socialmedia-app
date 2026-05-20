<?php
session_start();
require_once '../config/database.php';

$from = $_SESSION['user_id'] ?? null;
$to = $_POST['to_user'] ?? null;
$message = trim($_POST['message'] ?? '');

$filePath = null;

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
  $uploadDir = __DIR__ . '/assets/uploads/messages/';
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
  }

  $filename = time() . '_' . basename($_FILES['file']['name']);
  $targetFile = $uploadDir . $filename;

  // Path untuk disimpan di database
  $filePath = 'assets/uploads/messages/' . $filename;

  // DEBUG LOGGING SEMENTARA
  file_put_contents('debug_upload.txt', "Target: $targetFile\n", FILE_APPEND);

  if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
    // berhasil
  } else {
    // gagal upload, log pesan error
    file_put_contents('debug_upload.txt', "Gagal upload: " . print_r($_FILES, true) . "\n", FILE_APPEND);
    $filePath = null;
  }
}

if ($from && $to && ($message || $filePath)) {
  $stmt = mysqli_prepare($koneksi, "INSERT INTO messages (from_user, to_user, message) VALUES (?, ?, ?)");
  $finalMessage = $message;
  $finalMessage = $message ?: '[file]' . $filePath;

  mysqli_stmt_bind_param($stmt, 'iis', $from, $to, $finalMessage);
  if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
  } else {
    echo json_encode(['success' => false]);
  }
} else {
  echo json_encode(['success' => false]);
}
