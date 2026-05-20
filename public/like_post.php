<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

require_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user_id'];
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

if (!$post_id) {
  echo json_encode(['error' => 'Post not found']);
  exit;
}

// Cek apakah sudah like
$stmt = mysqli_prepare($koneksi, "SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $user_id, $post_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$liked = mysqli_fetch_assoc($result);

if ($liked) {
  // Hapus like
  $stmt = mysqli_prepare($koneksi, "DELETE FROM likes WHERE user_id = ? AND post_id = ?");
  mysqli_stmt_bind_param($stmt, "ii", $user_id, $post_id);
  mysqli_stmt_execute($stmt);
  $status = 'unliked';
} else {
  // Tambah like
  $stmt = mysqli_prepare($koneksi, "INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
  mysqli_stmt_bind_param($stmt, "ii", $user_id, $post_id);
  mysqli_stmt_execute($stmt);
  $status = 'liked';
}

// Hitung jumlah like terbaru
$stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM likes WHERE post_id = ?");
mysqli_stmt_bind_param($stmt, "i", $post_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$likeCount = (int)($row['total'] ?? 0);

// Kirim response JSON
echo json_encode([
  'status' => $status,
  'likeCount' => $likeCount
]);
exit;
