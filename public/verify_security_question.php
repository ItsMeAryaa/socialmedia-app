<?php
require '../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
  $email = trim($_POST['email']);
  $_SESSION['reset_email'] = $email;

  $stmt = mysqli_prepare($koneksi, "SELECT security_question FROM users WHERE email = ?");
  mysqli_stmt_bind_param($stmt, "s", $email);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $user = mysqli_fetch_assoc($result);

  if ($user) {
    $question = $user['security_question'];
  } else {
    echo "<script>alert('Email tidak ditemukan'); window.location.href='forgot_password.php';</script>";
    exit;
  }
} else {
  header("Location: forgot_password.php");
  exit;
}
?>

<!-- HTML form untuk jawab pertanyaan -->
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jawab Pertanyaan Keamanan</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/icon/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="website icon" href="assets/icon/web-icon.png">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
  <div class="form-container shadow-sm p-4 bg-white rounded" style="width: 100%; max-width: 400px;">
    <h5 class="mb-3 text-center">Pertanyaan Keamanan</h5>
    <p class="text-muted">Email: <strong><?= htmlspecialchars($email) ?></strong></p>
    <p class="mb-3"><?= htmlspecialchars($question) ?></p>
    <form action="send_token.php" method="post">
      <input type="text" class="form-control mb-3" name="security_answer" placeholder="Jawaban Anda" required>
      <button type="submit" class="btn btn-primary w-100">Kirim Kode Verifikasi</button>
    </form>
    <div class="mt-3 text-center">
      <a href="forgot_password.php" class="text-decoration-none">Kembali</a>
    </div>
  </div>
</body>
</html>
