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
$user_id = $_SESSION['user_id'];

if (!$comment_id) {
  echo json_encode(['error' => 'Komentar tidak ditemukan']);
  exit;
}

// Ambil data komentar dan id post
$query = "
  SELECT c.user_id AS commenter_id, c.post_id, p.user_id AS post_owner_id
  FROM comments c
  JOIN posts p ON c.post_id = p.id
  WHERE c.id = ?
";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $comment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cek = mysqli_fetch_assoc($result);

if (!$cek) {
  echo json_encode(['error' => 'Komentar tidak ditemukan']);
  exit;
}

// Hanya commenter atau post owner yang boleh hapus
if ($cek['commenter_id'] != $user_id && $cek['post_owner_id'] != $user_id) {
  echo json_encode(['error' => 'Tidak diizinkan']);
  exit;
}

// Hapus komentar
$stmt = mysqli_prepare($koneksi, "DELETE FROM comments WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $comment_id);
mysqli_stmt_execute($stmt);

echo json_encode(['success' => true]);
exit;
