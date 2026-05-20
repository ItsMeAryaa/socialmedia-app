<?php
require '../config/database.php';
session_start();

// Pastikan email dari session tersedia
if (!isset($_SESSION['reset_email'])) {
  header("Location: forgot_password.php");
  exit;
}

$email = $_SESSION['reset_email'];
$answer = strtolower(trim($_POST['security_answer'] ?? '')); // Normalisasi jawaban

// Cek apakah jawaban cocok dengan yang di database
$query = "SELECT id FROM users WHERE email = ? AND LOWER(security_answer) = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "ss", $email, $answer);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user) {
  $userId = $user['id'];

  // Generate 6 digit token angka
  $token = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
  $expiry = date('Y-m-d H:i:s', time() + (30 * 60)); // 30 menit dari sekarang

  // Simpan token ke DB
  $update = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?";
  $stmt2 = mysqli_prepare($koneksi, $update);
  mysqli_stmt_bind_param($stmt2, "ssi", $token, $expiry, $userId);
  mysqli_stmt_execute($stmt2);

  $_SESSION['reset_token_generated'] = true;

  // Simulasi pengiriman token (offline)
  echo "<!DOCTYPE html>
  <html lang='id'>
  <head>
    <meta charset='UTF-8'>
    <meta http-equiv='refresh' content='5;url=verify_token.php'>
    <link rel='stylesheet' href='assets/css/bootstrap.min.css'>
    <title>Kode Terkirim</title>
    <script>
      let seconds = 5;
      function countdown() {
        const el = document.getElementById('timer');
        if (seconds > 0) {
          el.textContent = seconds;
          seconds--;
          setTimeout(countdown, 1000);
        }
      }
      window.onload = countdown;
    </script>
  </head>
  <body style='display:flex; justify-content:center; align-items:center; height:100vh; background:#f9f9f9;'>
    <div class='text-center'>
      <div class='alert alert-success shadow-sm'>
        Kode verifikasi telah dikirim ke email: <strong>$email</strong><br>
        (Token untuk testing: <strong>$token</strong>)<br><br>
        Anda akan diarahkan ke halaman verifikasi dalam <span id='timer'>5</span> detik...
      </div>
    </div>
  </body>
  </html>";
  exit;

} else {
  // Jawaban tidak cocok, tampilkan kembali halaman pertanyaan
  echo "<!DOCTYPE html>
  <html lang='id'>
  <head>
    <meta charset='UTF-8'>
    <title>Jawaban Salah</title>
    <link rel='stylesheet' href='assets/css/bootstrap.min.css'>
  </head>
  <body class='d-flex justify-content-center align-items-center vh-100 bg-light'>
    <div class='form-container shadow-sm p-4 bg-white rounded' style='width: 100%; max-width: 400px;'>
      <h5 class='mb-3 text-center text-danger'>Jawaban Salah</h5>
      <p class='text-muted text-center'>Jawaban tidak sesuai dengan data kami. Silakan coba lagi.</p>
      <form action='send_token.php' method='post'>
        <input type='text' class='form-control mb-3' name='security_answer' placeholder='Jawaban Anda' required>
        <button type='submit' class='btn btn-primary w-100'>Kirim Ulang</button>
      </form>
      <div class='mt-3 text-center'>
        <a href='forgot_password.php' class='text-decoration-none'>Ganti Email</a>
      </div>
    </div>
  </body>
  </html>";
  exit;
}
?>