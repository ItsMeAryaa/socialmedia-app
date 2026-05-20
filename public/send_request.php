<?php
session_start();
require '../config/database.php';

$from_user = $_SESSION['user_id'] ?? null;
$to_user = $_POST['to_user'] ?? null;

if ($from_user && $to_user && $from_user != $to_user) {
  $stmt = mysqli_prepare($koneksi, "INSERT INTO friend_requests (from_user, to_user, status) VALUES (?, ?, 'pending')");
  mysqli_stmt_bind_param($stmt, "ii", $from_user, $to_user);
  mysqli_stmt_execute($stmt);
}

header("Location: profile.php?user_id=$to_user");
exit;
