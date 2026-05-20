<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $auth = new AuthController($koneksi);
  $email = $_POST['email'];
  $password = $_POST['password'];

  if ($admin = $auth->isAdmin($email, $password)) {
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_email'] = $admin['email'];
    header('Location: dashboard.php');
    exit;
  }

  if ($user = $auth->login($email, $password)) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    header('Location: ../public/index.php');
    exit;
  }

  header('Location: login.php?error=email_atau_password_salah!');
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Admin - Temanku</title>
  <link href="../public/assets/css/bootstrap.min.css" rel="stylesheet">
  <link rel="website icon" href="../public/assets/icon/web-icon.png">
</head>
<body class="bg-dark">
  <div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-sm p-4 bg-secondary text-light" style="min-width:350px;max-width:370px;width:100%">
      <div class="text-center">
        <img src="../public/assets/icon/web-logo-default.png" alt="Logo" width="200">
      </div>
      <h4 class="mb-3 text-center">Login Admin</h4>
      <form method="POST" action="login.php">
        <?php if (isset($_GET['error'])): ?>
          <div class="alert alert-danger py-2">
            Email atau password salah!
          </div>
        <?php endif; ?>
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" name="email" class="form-control" id="email" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" name="password" class="form-control" id="password" required>
        </div>
        <button type="submit" class="btn btn-info text-white w-100">Login</button>
      </form>
    </div>
  </div>
</body>
</html>
