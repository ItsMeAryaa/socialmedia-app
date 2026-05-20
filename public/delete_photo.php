<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: profile.php");
  exit;
}

$id = $_SESSION['user_id'];

$result = mysqli_query($koneksi, "SELECT photo FROM profiles WHERE user_id = $id");
$data = mysqli_fetch_assoc($result);
$foto = $data['photo'];

if ($foto && file_exists($foto)) {
  unlink($foto);
}

mysqli_query($koneksi, "UPDATE profiles SET photo = NULL WHERE user_id = $id");

header("Location: profile.php?msg=Foto_dihapus");
exit;
?>
