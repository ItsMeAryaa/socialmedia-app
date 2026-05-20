<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
  date_default_timezone_set('Asia/Singapore');
  $now = date('Y-m-d H:i:s');

  $stmt = $koneksi->prepare("UPDATE users SET last_active = ? WHERE id = ?");
  $stmt->bind_param("si", $now, $_SESSION['user_id']);
  $stmt->execute();
}
