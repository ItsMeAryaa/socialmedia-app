<?php
session_start();
require_once '../config/database.php';

$me    = $_SESSION['user_id'] ?? 0;
$msgId = intval($_POST['message_id'] ?? 0);

if ($me && $msgId) {
  $stmt = mysqli_prepare($koneksi,
    "UPDATE messages
      SET is_deleted = 1
      WHERE id = ? AND from_user = ?");
  mysqli_stmt_bind_param($stmt, "ii", $msgId, $me);
  mysqli_stmt_execute($stmt);
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false]);
}
