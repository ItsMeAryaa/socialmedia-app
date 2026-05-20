<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $auth = new AuthController($koneksi);
  $user = $auth->login($_POST['email'], $_POST['password']);

  if ($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    header('Location: index.php');
    exit;
  } else {
    header('Location: login.php?error=1');
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Temanku</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/icon/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="website icon" href="assets/icon/web-icon.png">
</head>
<body>

  <div class="overlay">
    <div class="container login-container">
      <!-- Kiri -->
      <div class="login-left">
        <img src="assets/img/web-logo-default.png" alt="Logo Temanku" width="500" height="130">
        <p>Temanku memudahkanmu menjalin pertemanan, berbagi momen, dan terhubung dengan orang-orang yang berarti.</p>
      </div>

      <!-- Kanan -->
      <div class="login-right">
        <h4 class="text-center mb-4">Masuk ke akun Anda</h4>
        <form method="POST" action="login.php">
          <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger py-2">
              <i class="bi bi-exclamation-triangle me-2"></i>Email atau password salah!
            </div>
          <?php endif; ?>
          <div class="mb-3">
            <input type="email" name="email" class="form-control" placeholder="Email" required>
          </div>
          <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Kata Sandi" required>
          </div>
          <button type="submit" class="btn btn-primary w-100 mb-2">Masuk</button>
          <div class="text-center">
            <a href="forgot_password.php" class="text-decoration-none text-info">Lupa password?</a>
          </div>
          <hr>
          <div class="text-center">
            <a class="btn btn-success btn-sm" href="register.php">BUAT AKUN BARU</a>
          </div>
        </form>
      </div>
    </div>
  </div>

</body>
</html>
