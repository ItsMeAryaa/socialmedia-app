<?php
session_start();
require '../config/database.php';

$user_id = $_SESSION['user_id'] ?? null;
$friend_id = $_POST['friend_id'] ?? null;

if ($user_id && $friend_id) {
  $stmt = mysqli_prepare($koneksi, "DELETE FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
  mysqli_stmt_bind_param($stmt, "iiii", $user_id, $friend_id, $friend_id, $user_id);
  mysqli_stmt_execute($stmt);
}

header("Location: profile.php?user_id=$friend_id");
exit;
