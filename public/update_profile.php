<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user_id'];
$name = $_POST['name'] ?? '';
$bio = $_POST['bio'] ?? '';
$photo_path = null;
$cover_path = null;

// Upload foto profil
if (!empty($_FILES['photo']['name'])) {
  $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
  $photo_path = 'assets/uploads/profile/profile_' . uniqid() . '.' . $ext;
  move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
}

// Upload cover
if (!empty($_FILES['cover_photo']['name'])) {
  $ext = pathinfo($_FILES['cover_photo']['name'], PATHINFO_EXTENSION);
  $cover_path = 'assets/uploads/cover/cover_' . uniqid() . '.' . $ext;
  move_uploaded_file($_FILES['cover_photo']['tmp_name'], $cover_path);
}

$query = "UPDATE profiles SET name = ?, bio = ?";

if ($photo_path) $query .= ", photo = '$photo_path'";
if ($cover_path) $query .= ", cover_photo = '$cover_path'";

$query .= " WHERE user_id = ?";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "ssi", $name, $bio, $user_id);
mysqli_stmt_execute($stmt);

header('Location: profile.php');
exit;
?>
