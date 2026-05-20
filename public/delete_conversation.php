<?php
session_start();
require_once '../config/database.php';

$me   = $_SESSION['user_id'] ?? 0;
$peer = intval($_POST['peer_id'] ?? 0);

if ($me && $peer) {
  $stmt = mysqli_prepare($koneksi,
    "DELETE FROM messages
      WHERE (from_user = ? AND to_user = ?)
        OR (from_user = ? AND to_user = ?)");
  mysqli_stmt_bind_param($stmt, "iiii", $me, $peer, $peer, $me);
  mysqli_stmt_execute($stmt);
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false]);
}
