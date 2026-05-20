<?php
session_start();
require '../config/database.php';

// Cek apakah user sudah verifikasi token
if (!isset($_SESSION['verified_user_id'])) {
  header("Location: forgot_password.php");
  exit;
}

$error = isset($_GET['error']) ? $_GET['error'] : null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/icon/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="website icon" href="assets/icon/web-icon.png">
  <style>
    body {
      background-color: #f8f9fa;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .form-container {
      background-color: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
      max-width: 400px;
      width: 100%;
    }
  </style>
</head>
<body>

<div class="form-container shadow-sm">
  <h4 class="mb-3 text-center">Ubah Password</h4>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form action="update_password.php" method="post">
    <div class="mb-3">
      <label for="password" class="form-label">Password Baru</label>
      <input type="password" class="form-control" id="password" name="password" required minlength="6">
    </div>
    <div class="mb-3">
      <label for="confirm_password" class="form-label">Konfirmasi Password</label>
      <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
    </div>
    <button type="submit" class="btn btn-info text-light w-100">Simpan Password</button>
  </form>
</div>

</body>
</html>
