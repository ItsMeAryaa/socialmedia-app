<?php
session_start();
require_once '../config/database.php';
$me = $_SESSION['user_id'];
$peer = $_POST['peer_id'] ?? 0;
if($me && $peer){
  $stmt = mysqli_prepare($koneksi,
    "UPDATE messages
      SET is_read = 1
      WHERE from_user = ? AND to_user = ? AND is_read = 0");
  mysqli_stmt_bind_param($stmt, "ii", $peer, $me);
  mysqli_stmt_execute($stmt);
}
