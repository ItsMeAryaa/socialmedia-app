<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

require_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user_id'];
$comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;

if (!$comment_id) {
  echo json_encode(['error' => 'Comment not found']);
  exit;
}

// Cek apakah sudah like
$stmt = mysqli_prepare($koneksi, "SELECT id FROM comment_likes WHERE comment_id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $comment_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$liked = mysqli_fetch_assoc($result);

if ($liked) {
  // Hapus like
  $stmt = mysqli_prepare($koneksi, "DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?");
  mysqli_stmt_bind_param($stmt, "ii", $comment_id, $user_id);
  mysqli_stmt_execute($stmt);
  $status = 'unliked';
} else {
  // Tambah like
  $stmt = mysqli_prepare($koneksi, "INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)");
  mysqli_stmt_bind_param($stmt, "ii", $comment_id, $user_id);
  mysqli_stmt_execute($stmt);
  $status = 'liked';
}

// Hitung jumlah like terbaru
$stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM comment_likes WHERE comment_id = ?");
mysqli_stmt_bind_param($stmt, "i", $comment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$likeCount = (int)($row['total'] ?? 0);

echo json_encode(['status' => $status, 'likeCount' => $likeCount]);
