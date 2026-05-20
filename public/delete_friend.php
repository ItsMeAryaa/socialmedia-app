<?php
session_start();
require_once '../config/database.php';

$currentUserId = $_SESSION['user_id'] ?? null;
$friendId = $_POST['friend_id'] ?? null;

if (!$currentUserId || !$friendId) {
  header('Location: friends.php');
  exit;
}

// 1. Hapus pertemanan
$stmt = mysqli_prepare($koneksi, "DELETE FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
mysqli_stmt_bind_param($stmt, 'iiii', $currentUserId, $friendId, $friendId, $currentUserId);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// 2. Ambil nama saya
$stmt = mysqli_prepare($koneksi, "SELECT name FROM profiles WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $currentUserId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$myName = $row['name'] ?? 'Seseorang';
mysqli_stmt_close($stmt);

// 3. Tambahkan notifikasi ke user yang dihapus
$content = "$myName telah menghapus kamu dari daftar temannya.";
$stmt = mysqli_prepare($koneksi, "INSERT INTO notifications (user_id, content) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt, 'is', $friendId, $content);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// (Opsional) Notifikasi berhasil
$_SESSION['alert'] = [
  'type' => 'warning',
  'message' => "Kamu telah menghapus teman dari daftar."
];

header('Location: friends.php');
exit;
