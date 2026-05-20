<?php
require_once '../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id) {
  header('Location: data_user.php?pesan=gagalhapus');
  exit;
}

$hapusUsers = mysqli_query($koneksi, "DELETE FROM users WHERE id = $id");
$hapusProfiles = mysqli_query($koneksi, "DELETE FROM profiles WHERE user_id = $id");

if ($hapusUsers && $hapusProfiles) {
  header('Location: data_user.php?pesan=berhasilhapus');
} else {
  header('Location: data_user.php?pesan=gagalhapus');
}
exit;
