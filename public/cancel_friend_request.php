<?php
session_start();
include_once '../config/database.php'; // pastikan path benar

$currentUserId = $_SESSION['user_id'];
$toUser = isset($_POST['to_user']) ? (int) $_POST['to_user'] : 0;

if ($toUser > 0) {
  $stmt = mysqli_prepare($koneksi, "DELETE FROM friend_requests WHERE from_user = ? AND to_user = ? AND status = 'pending'");
  $stmt->bind_param("ii", $currentUserId, $toUser);
  $stmt->execute();
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
