<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

require_once __DIR__ . '/../config/database.php';

$comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
$content = trim($_POST['content'] ?? '');
$user_id = $_SESSION['user_id'];

if (!$comment_id || $content === '') {
  echo json_encode(['error' => 'Data tidak valid']);
  exit;
}

// Cek apakah user adalah pemilik komentar
$stmt = mysqli_prepare($koneksi, "SELECT id FROM comments WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $comment_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!mysqli_fetch_assoc($result)) {
  echo json_encode(['error' => 'Tidak diizinkan']);
  exit;
}

// Update isi komentar
$stmt = mysqli_prepare($koneksi, "UPDATE comments SET content = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, "si", $content, $comment_id);
mysqli_stmt_execute($stmt);

echo json_encode(['success' => true, 'new_content' => htmlspecialchars($content)]);
exit;
