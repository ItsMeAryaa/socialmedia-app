<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $storyId = isset($_POST['story_id']) ? (int)$_POST['story_id'] : 0;
  $userId = $_SESSION['user_id'];

  // Validasi: pastikan story milik user sendiri
  $stmt = mysqli_prepare($koneksi, "SELECT * FROM stories WHERE id = ? AND user_id = ?");
  mysqli_stmt_bind_param($stmt, "ii", $storyId, $userId);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $story = mysqli_fetch_assoc($result);

  if ($story) {
    // Jika ada file fisik, hapus juga dari server
    if (!empty($story['file_path'])) {
      $path = __DIR__ . '/../' . $story['file_path'];
      if (is_file($path)) {
        unlink($path);
      }
    }

    // Hapus dari database
    $stmt = mysqli_prepare($koneksi, "DELETE FROM stories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $storyId);
    mysqli_stmt_execute($stmt);
  }
}

header("Location: index.php");
exit;
