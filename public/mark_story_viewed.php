<?php
require_once __DIR__ . '/../config/database.php';
session_start();

$user_id = $_SESSION['user_id'];
$story_id = intval($_POST['story_id']);

if ($story_id && $user_id) {
  $check = mysqli_prepare($koneksi, "SELECT 1 FROM story_views WHERE user_id = ? AND story_id = ?");
  mysqli_stmt_bind_param($check, "ii", $user_id, $story_id);
  mysqli_stmt_execute($check);
  mysqli_stmt_store_result($check);

  if (mysqli_stmt_num_rows($check) === 0) {
    $insert = mysqli_prepare($koneksi, "INSERT INTO story_views (user_id, story_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($insert, "ii", $user_id, $story_id);
    mysqli_stmt_execute($insert);
  }
}
