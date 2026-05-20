<?php
session_start();
require '../config/database.php';

// Pastikan user sudah diverifikasi
if (!isset($_SESSION['verified_user_id'])) {
  header("Location: forgot_password.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $userId = $_SESSION['verified_user_id'];
  $password = $_POST['password'];
  $confirm = $_POST['confirm_password'];

  // Validasi kecocokan password
  if ($password !== $confirm) {
    header("Location: reset_password.php?error=Password tidak cocok.");
    exit;
  }

  // Hash password
  $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

  // Simpan password ke DB, dan hapus token/token_expiry
  $update = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
  $stmt = mysqli_prepare($koneksi, $update);
  mysqli_stmt_bind_param($stmt, "si", $hashedPassword, $userId);
  mysqli_stmt_execute($stmt);

  // Bersihkan session verifikasi
  unset($_SESSION['verified_user_id']);
  unset($_SESSION['reset_email']);

  // Redirect ke login
  header("Location: login.php?reset=success");
  exit;
} else {
  header("Location: forgot_password.php");
  exit;
}
