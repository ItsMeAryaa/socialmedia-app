<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['request_id'], $_POST['action'])) {
  header('Location: friends.php');
  exit;
}

$request_id = (int)$_POST['request_id'];
$action = $_POST['action'];
$user_id = $_SESSION['user_id'];

// Ambil data permintaan dan info pengirim
$query = "SELECT fr.*, u.id AS from_user_id, p.name AS from_name, pu.name AS to_name
  FROM friend_requests fr
  JOIN users u ON fr.from_user = u.id
  JOIN profiles p ON u.id = p.user_id
  JOIN profiles pu ON fr.to_user = pu.user_id
  WHERE fr.id = ? AND fr.to_user = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "ii", $request_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$request = mysqli_fetch_assoc($result);

if (!$request) {
  header('Location: friends.php');
  exit;
}

// Simpan nama pengirim ke sesi (untuk alert)
$_SESSION['friend_request_from_name'] = $request['from_name'];

if ($action === 'accept') {
  // Mulai transaksi
  mysqli_begin_transaction($koneksi);
  try {
    // Update status ke 'accepted'
    $stmt = mysqli_prepare($koneksi, "UPDATE friend_requests SET status = 'accepted' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $request_id);
    mysqli_stmt_execute($stmt);

    // Tambahkan ke tabel friends dua arah dengan status accepted
    $stmt = mysqli_prepare($koneksi, "
      INSERT INTO friends (user_id, friend_id, status)
      VALUES (?, ?, 'accepted'), (?, ?, 'accepted')
    ");
    mysqli_stmt_bind_param($stmt, "iiii", $request['from_user'], $request['to_user'], $request['to_user'], $request['from_user']);
    mysqli_stmt_execute($stmt);

    // Tambahkan notifikasi
    $notif = $request['to_name'] . " menerima permintaan pertemanan dari kamu!";
    $stmt = mysqli_prepare($koneksi, "INSERT INTO notifications (user_id, content) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "is", $request['from_user'], $notif);
    mysqli_stmt_execute($stmt);

    // Commit transaksi
    mysqli_commit($koneksi);
    $_SESSION['friend_request_status'] = 'accepted';

  } catch (Exception $e) {
    mysqli_rollback($koneksi);
    $_SESSION['friend_request_status'] = 'error';
  }

} elseif ($action === 'reject') {
  // Tolak permintaan
  $stmt = mysqli_prepare($koneksi, "UPDATE friend_requests SET status = 'rejected' WHERE id = ?");
  mysqli_stmt_bind_param($stmt, "i", $request_id);
  mysqli_stmt_execute($stmt);

  // Tambahkan notifikasi
  $notif = $request['to_name'] . " menolak permintaan pertemanan dari kamu.";
  $stmt = mysqli_prepare($koneksi, "INSERT INTO notifications (user_id, content) VALUES (?, ?)");
  mysqli_stmt_bind_param($stmt, "is", $request['from_user'], $notif);
  mysqli_stmt_execute($stmt);

  $_SESSION['friend_request_status'] = 'rejected';
}

header('Location: friends.php');
exit;
