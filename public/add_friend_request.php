<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['to_user'])) {
  header('Location: index.php');
  exit;
}

$from_user = (int)$_SESSION['user_id'];
$to_user = (int)$_POST['to_user'];

if ($from_user === $to_user) exit;

// Hapus request sebelumnya yang sudah rejected (opsional)
$stmt = mysqli_prepare($koneksi, "
  DELETE FROM friend_requests
  WHERE ((from_user = ? AND to_user = ?) OR (from_user = ? AND to_user = ?))
    AND status = 'rejected'
");
mysqli_stmt_bind_param($stmt, "iiii", $from_user, $to_user, $to_user, $from_user);
mysqli_stmt_execute($stmt);

// Cek apakah sudah ada permintaan pending
$stmt = mysqli_prepare($koneksi, "
  SELECT * FROM friend_requests
  WHERE ((from_user = ? AND to_user = ?) OR (from_user = ? AND to_user = ?))
    AND status = 'pending'
");
mysqli_stmt_bind_param($stmt, "iiii", $from_user, $to_user, $to_user, $from_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$existing = mysqli_fetch_assoc($result);

if (!$existing) {
  // Buat permintaan baru
  $stmt = mysqli_prepare($koneksi, "INSERT INTO friend_requests (from_user, to_user) VALUES (?, ?)");
  mysqli_stmt_bind_param($stmt, "ii", $from_user, $to_user);
  mysqli_stmt_execute($stmt);

  // Ambil nama pengirim untuk notifikasi
  $stmt = mysqli_prepare($koneksi, "SELECT name FROM profiles WHERE user_id = ?");
  mysqli_stmt_bind_param($stmt, "i", $from_user);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $sender = mysqli_fetch_assoc($result);

  if ($sender) {
    $notif = $sender['name'] . " mengirimkan permintaan pertemanan.";
    $stmt = mysqli_prepare($koneksi, "INSERT INTO notifications (user_id, content) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "is", $to_user, $notif);
    mysqli_stmt_execute($stmt);
  }
}

header('Location: index.php');
exit;
