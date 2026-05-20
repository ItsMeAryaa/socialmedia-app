<?php
session_start();
require_once '../config/database.php';

$user_id = $_SESSION['user_id'] ?? null;

// Ambil 10 notif terbaru
$stmt = mysqli_prepare($koneksi,
    "SELECT * FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC LIMIT 5"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
  echo "<p class='text-center text-muted'>Tidak ada notifikasi baru.</p>";
} else {
  while ($row = mysqli_fetch_assoc($result)) {
    echo "
      <div class='border-bottom py-2'>
    ";
    echo
      htmlspecialchars($row['content']);
    echo "
      <div class='text-muted small'>"
        . date('d M Y H:i', strtotime($row['created_at'])) .
      "</div>
    ";
    echo "
      </div>
    ";
  }
}

// Tandai semua sebagai dibaca
if ($user_id) {
  $stmt = mysqli_prepare($koneksi, "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
  mysqli_stmt_bind_param($stmt, "i", $user_id);
  mysqli_stmt_execute($stmt);
}
