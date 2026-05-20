<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $last_name = trim($_POST['last_name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $password2 = $_POST['password2'];
  $security_question = $_POST['security_question'];
  $security_answer = trim($_POST['security_answer']);

  if ($password !== $password2) {
    header('Location: register.php?error=Password tidak sama!');
    exit;
  }

  if (strlen($password) < 6) {
    header('Location: register.php?error=Password minimal 6 karakter!');
    exit;
  }

  $auth = new AuthController($koneksi);
  $result = $auth->register($name, $last_name, $email, $password, $security_question, $security_answer);

  if (is_numeric($result)) {
    $_SESSION['user_id'] = $result;
    header('Location: index.php');
    exit;
  } else {
    header('Location: register.php?error=' . urlencode($result));
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Temanku</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/icon/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/styleRegister.css">
  <link rel="website icon" href="assets/icon/web-icon.png">
</head>
<body>

<div class="overlay">
  <div class="container register-container">
    <!-- Kiri -->
    <div class="register-left">
      <img src="assets/img/web-logo-default.png" alt="Logo Temanku" width="500" height="130">
      <p>Bergabunglah bersama Temanku dan mulai membangun koneksi, berbagi cerita, dan menjalin pertemanan secara menyenangkan.</p>
    </div>

    <!-- Kanan -->
    <div class="register-right">
      <h4 class="text-center mb-4">Buat Akun Baru</h4>
      <form class="row g-3" method="POST" action="register.php">
        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger py-2"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <div class="col-md-6">
          <label for="name" class="form-label">Nama Depan</label>
          <input type="text" name="name" class="form-control" id="name" required autocomplete="given-name">
        </div>
        <div class="col-md-6">
          <label for="last_name" class="form-label">Nama Belakang</label>
          <input type="text" name="last_name" class="form-control" id="last_name" required autocomplete="family-name">
        </div>
        <div class="col-md-6">
          <label for="email" class="form-label">Email</label>
          <input type="email" name="email" class="form-control" id="email" required autocomplete="username">
        </div>
        <div class="col-md-6">
          <label for="password" class="form-label">Password</label>
          <input type="password" name="password" class="form-control" id="password" required autocomplete="new-password">
        </div>
        <div class="col-md-6">
          <label for="password2" class="form-label">Konfirmasi Password</label>
          <input type="password" name="password2" class="form-control" id="password2" required>
        </div>
        <div class="col-md-12">
          <label for="security_answer" class="form-label">Pertanyaan Keamanan</label>
          <textarea type="text" class="form-control" disabled>Dimana kamu pertama kali liburan dengan keluarga?</textarea>
          <input type="hidden" name="security_question" value="Dimana kamu pertama kali liburan dengan keluarga?">
        </div>
        <div class="col-md-12">
          <label for="security_answer" class="form-label">Jawaban Anda</label>
          <input type="text" name="security_answer" class="form-control" id="security_answer" required>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary w-100">Daftar</button>
        </div>
      </form>
      <div class="text-center mt-3">
        Sudah punya akun? <a href="login.php" class="text-decoration-none text-info">Login sekarang</a>
      </div>
    </div>
  </div>
</div>

</body>
</html>
