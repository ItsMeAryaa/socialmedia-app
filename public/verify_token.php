<?php
session_start();
require '../config/database.php';

// Jika tidak ada email yang sedang diverifikasi
if (!isset($_SESSION['reset_email'])) {
  header("Location: forgot_password.php");
  exit;
}

$email = $_SESSION['reset_email'];
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
  $tokenInput = trim($_POST['token']);

  // Ambil data user berdasarkan email
  $query = "SELECT id, reset_token, reset_token_expiry FROM users WHERE email = ?";
  $stmt = mysqli_prepare($koneksi, $query);
  mysqli_stmt_bind_param($stmt, "s", $email);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $user = mysqli_fetch_assoc($result);

  if ($user) {
    $validToken = $user['reset_token'];
    $expiry = strtotime($user['reset_token_expiry']);

    if ($tokenInput === $validToken && time() <= $expiry) {
      // Token cocok dan belum kedaluwarsa
      $_SESSION['verified_user_id'] = $user['id'];
      header("Location: reset_password.php");
      exit;
    } else {
      $error = "Token salah atau sudah kedaluwarsa.";
    }
  } else {
    $error = "User tidak ditemukan.";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verifikasi Token</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/icon/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="website icon" href="assets/icon/web-icon.png">
  <style>
    body {
      background-color: #f2f2f2;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .form-container {
      max-width: 400px;
      width: 100%;
      background-color: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
  </style>
</head>
<body>

  <div class="form-container shadow-sm">
    <h4 class="mb-3 text-center">Verifikasi Kode</h4>
    <p class="text-muted text-center">Masukkan 6 digit kode yang dikirim ke email <strong><?= htmlspecialchars($email) ?></strong></p>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <input type="text" name="token" class="form-control text-center" placeholder="Contoh: 123456" maxlength="6" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Verifikasi</button>
    </form>

    <div class="mt-3 text-center">
      <a href="forgot_password.php" class="text-decoration-none">Kembali</a>
    </div>
  </div>

</body>
</html>
