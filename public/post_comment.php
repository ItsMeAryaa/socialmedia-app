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
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] ? (int)$_POST['parent_id'] : null;

if (!$post_id || $content === '') {
  echo json_encode(['error' => 'Data_tidak_valid']);
  exit;
}

// Simpan komentar
$query = "INSERT INTO comments (post_id, user_id, content, parent_id) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "iisi", $post_id, $user_id, $content, $parent_id);
mysqli_stmt_execute($stmt);
$comment_id = mysqli_insert_id($koneksi);

// Ambil data profil user
$query = "SELECT p.name, p.photo FROM users u JOIN profiles p ON u.id = p.user_id WHERE u.id = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Kirim respons JSON
echo json_encode([
  'success' => true,
  'comment' => [
    'id' => $comment_id,
    'name' => $user['name'],
    'photo' => $user['photo'] ?? 'assets/img/default_profile.png',
    'content' => htmlspecialchars($content),
    'created_at' => date('d M H:i'),
    'parent_id' => $parent_id
  ]
]);
