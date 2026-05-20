<?php
session_start();
unset($_SESSION['admin_id'], $_SESSION['admin_email']);
session_destroy();

// Hancurkan cookie session (jika ada)
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
  );
}

header('Location: login.php');
exit;
