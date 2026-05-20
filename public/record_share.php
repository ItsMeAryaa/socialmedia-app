<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../config/database.php';

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$share_type = isset($_POST['share_type']) ? trim($_POST['share_type']) : '';

$response = ['status' => 'error', 'shareCount' => 0];

$allowed_types = ['copy', 'whatsapp', 'facebook', 'twitter'];

if ($post_id > 0 && in_array($share_type, $allowed_types)) {
  $insert_stmt = mysqli_prepare($koneksi, "INSERT INTO post_shares (post_id, share_type, share_time) VALUES (?, ?, NOW())");

  if (!$insert_stmt) {
    echo json_encode([
      'status' => 'prepare_failed',
      'mysqli_error' => mysqli_error($koneksi)
    ]);
    exit;
  }

  $insert_stmt->bind_param("is", $post_id, $share_type);

  if (!$insert_stmt->execute()) {
    echo json_encode([
      'status' => 'execute_failed',
      'stmt_error' => $insert_stmt->error
    ]);
    exit;
  }

  // Jika berhasil
  $count_query = "SELECT COUNT(*) AS total FROM post_shares WHERE post_id = $post_id";
  $count_result = mysqli_query($koneksi, $count_query);
  if ($count_result) {
    $row = mysqli_fetch_assoc($count_result);
    $response = [
      'status' => 'success',
      'shareCount' => $row['total'] ?? 0
    ];
  }

  $insert_stmt->close();
}

echo json_encode($response);
