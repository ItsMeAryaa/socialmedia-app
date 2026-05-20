<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$searchTerm = trim($_GET['q'] ?? '');

function escape($text) {
  return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hasil Pencarian</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link href="assets/icon/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="website icon" href="assets/icon/web-icon.png">
  <style>
    body {
      background-color: #ece8e8;
    }
    .search-result {
      background-color: #fbfbfb;
      border-radius: 8px;
      padding: 16px;
      margin-bottom: 20px;
    }
    .search-result img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 8px;
    }
  </style>
</head>
<body>

  <?php include '../app/views/navbar.php'; ?>

  <div class="container py-5">
    <h3>Hasil Pencarian untuk: <em><?= escape($searchTerm) ?></em></h3>
    <hr class="text-secondary">

    <?php if ($searchTerm): ?>

    <!-- 🔍 Cari User -->
    <h5 class="text-info mt-4">Pengguna</h5>
    <?php
    $isAdmin = isset($_SESSION['admin_id']);
    $logged_in_user = $_SESSION['user_id'] ?? null;

    if ($isAdmin) {
      $stmt = mysqli_prepare($koneksi, "SELECT user_id, name, last_name, photo FROM profiles WHERE name LIKE CONCAT('%', ?, '%')");
      mysqli_stmt_bind_param($stmt, "s", $searchTerm);
    } else {
      $stmt = mysqli_prepare($koneksi, "SELECT user_id, name, last_name, photo FROM profiles WHERE name LIKE CONCAT('%', ?, '%') AND user_id != ?");
      mysqli_stmt_bind_param($stmt, "si", $searchTerm, $logged_in_user);
    }
    mysqli_stmt_execute($stmt);
    $users = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($users) > 0):
      while ($user = mysqli_fetch_assoc($users)):
        $photo = $user['photo'] ?: 'assets/img/default_profile.png';
        $user['full_name'] = $user['name'] . ' ' . $user['last_name'];

        // Cek apakah sudah berteman
        $isFriend = false;
        if (!$isAdmin) {
          $friend_stmt = mysqli_prepare($koneksi, "
            SELECT 1 FROM friends
            WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)
          ");
          mysqli_stmt_bind_param($friend_stmt, "iiii", $logged_in_user, $user['user_id'], $user['user_id'], $logged_in_user);
          mysqli_stmt_execute($friend_stmt);
          mysqli_stmt_store_result($friend_stmt);
          $isFriend = mysqli_stmt_num_rows($friend_stmt) > 0;
          mysqli_stmt_close($friend_stmt);
        }
    ?>
      <div class="search-result d-flex justify-content-between align-items-center shadow-lg">
        <div class="d-flex align-items-center gap-3">
          <img src="<?= escape($photo) ?>" width="50" height="50" class="rounded-circle" style="object-fit:cover;">
          <a href="profile.php?user_id=<?= $user['user_id'] ?>" class="text-info text-decoration-none fs-5">
            <?= escape($user['full_name']) ?>
          </a>
        </div>

        <div>
          <?php if (!$isAdmin): ?>
            <?php if ($isFriend): ?>
              <div class="card p-2 shadow-sm">
                <span class="text-success fw-semibold">Anda Telah Berteman</span>
              </div>
            <?php else: ?>
              <form method="POST" action="add_friend_request.php" class="m-0">
                <input type="hidden" name="to_user" value="<?= $user['user_id'] ?>">
                <button type="submit" class="btn btn-sm btn-info text-light">
                  <i class="bi bi-person-plus"></i> Tambah Teman
                </button>
              </form>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; else: ?>
      <p class="text-secondary">Pengguna tidak ditemukan.</p>
    <?php endif; ?>

    <?php else: ?>
      <p class="text-warning">Silakan masukkan kata kunci pencarian.</p>
    <?php endif; ?>
  </div>

  <!-- Modal notifikasi -->
  <div class="modal fade" id="notificationModal" tabindex="-1" aria-hidden="true" aria-labelledby="notifLabel">
    <div class="modal-dialog modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="notifLabel">
            Notifikasi Terbaru
          </h5>
          <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="notifList">
          <div class="text-center">
            Memuat...
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include '../app/views/footer.php'; ?>
  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/notification.js"></script>

</body>
</html>
