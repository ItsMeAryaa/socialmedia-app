<?php
session_start();
session_unset();

// Hapus semua data session
$_SESSION = array();

// Hapus session cookie
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(
    session_name(),
    '',
    time() - 42000,
    $params['path'],
    $params['domain'],
    $params['secure'],
    $params['httponly']
  );
}

// Update last active nya
if (isset($_SESSION['user_id'])) {
  require_once '../config/database.php';
  $stmt = $koneksi->prepare("UPDATE users SET last_active = NULL WHERE id = ?");
  $stmt->bind_param("i", $_SESSION['user_id']);
  $stmt->execute();
}

// Hancurkan session
session_destroy();
header('Location: login.php');
exit;