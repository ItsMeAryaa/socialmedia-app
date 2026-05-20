<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'];
$isAdmin = isset($_SESSION['admin_id']);

// Cek apakah user adalah admin
$isAdmin = false;
$cekAdmin = mysqli_prepare($koneksi, "SELECT * FROM admins WHERE id = ?");
mysqli_stmt_bind_param($cekAdmin, 'i', $user_id);
mysqli_stmt_execute($cekAdmin);
$adminResult = mysqli_stmt_get_result($cekAdmin);
if (mysqli_fetch_assoc($adminResult)) {
  $isAdmin = true;
}

// Ambil notifikasi hasil respon permintaan
$alert = '';
if (isset($_SESSION['friend_request_status'])) {
  $status = $_SESSION['friend_request_status'];
  $from = htmlspecialchars($_SESSION['friend_request_from_name']);
  if ($status === 'accepted') {
    $alert = "
      <div class='alert alert-success'>
        <i class='bi bi-check-circle me-2'></i> Kamu telah menerima permintaan pertemanan dari <strong>$from</strong>.
      </div>
    ";
  } elseif ($status === 'rejected') {
    $alert = "
      <div class='alert alert-danger'>
        <i class='bi bi-exclamation-triangle me-2'></i> Kamu menolak permintaan pertemanan dari <strong>$from</strong>.
      </div>
    ";
  }
  unset($_SESSION['friend_request_status'], $_SESSION['friend_request_from_name']);
}

$friends = [];
$requests = [];

if (!$isAdmin) {
  // Teman (ambil semua user yang berteman dengan user login)
  $query = "
    SELECT DISTINCT u.id, p.name, p.last_name, p.photo
    FROM friends f
    JOIN users u ON (
      (f.user_id = ? AND u.id = f.friend_id) OR
      (f.friend_id = ? AND u.id = f.user_id)
    )
    JOIN profiles p ON u.id = p.user_id
    WHERE f.status = 'accepted'
  ";
  $stmt = mysqli_prepare($koneksi, $query);
  mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  while ($row = mysqli_fetch_assoc($result)) {
    $friends[] = $row;
  }

  // Permintaan pertemanan masuk
  $query = "
    SELECT fr.id, u.id AS user_id, p.name, p.last_name, p.photo
    FROM friend_requests fr
    JOIN users u ON fr.from_user = u.id
    JOIN profiles p ON u.id = p.user_id
    WHERE fr.to_user = ? AND fr.status = 'pending'
  ";
  $stmt = mysqli_prepare($koneksi, $query);
  mysqli_stmt_bind_param($stmt, "i", $user_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  while ($row = mysqli_fetch_assoc($result)) {
    $requests[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Teman Saya - Temanku</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/styleHome.css" rel="stylesheet">
  <link href="assets/icon/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/icon/web-icon.png" rel="website icon">
</head>
<body>
  <?php include '../app/views/navbar.php'; ?>

  <?php if (isset($_SESSION['alert'])): ?>
    <div class="container mt-2">
      <div class="alert alert-<?= $_SESSION['alert']['type'] ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-"></i>
        <?= $_SESSION['alert']['message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    </div>
    <?php unset($_SESSION['alert']); ?>
  <?php endif; ?>

  <div class="container py-4" style="max-width:700px;">
    <h2 class="mb-3">Permintaan Pertemanan</h2>

    <?= $alert ?>

    <?php if (count($requests) > 0): ?>
      <?php foreach ($requests as $r): ?>
        <div class="card mb-3">
          <div class="card-body d-flex align-items-center">
            <img src="<?= htmlspecialchars($f['photo'] ?? 'assets/img/default_profile.png') ?>"
              width="45" height="45" class="rounded-circle me-3" style="object-fit:cover;">
            <div class="flex-grow-1">
              <strong><?= htmlspecialchars($r['name'] . ' ' . $r['last_name']) ?></strong><br>
              <small>ingin berteman denganmu</small>
            </div>
            <form method="POST" action="handle_request.php" class="d-flex gap-1">
              <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
              <button name="action" value="accept" class="btn btn-sm btn-success">Terima</button>
              <button name="action" value="reject" class="btn btn-sm btn-outline-danger">Tolak</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="text-secondary">Tidak ada permintaan pertemanan.</div>
    <?php endif; ?>

    <hr class="my-4">

    <h2 class="mb-3">Teman Anda</h2>

    <?php if (count($friends) > 0): ?>
      <?php foreach ($friends as $f): ?>
        <div class="card mb-2">
          <div class="card-body d-flex justify-content-between align-items-center">
            <img src="<?= htmlspecialchars($f['photo'] ?? 'assets/img/default_profile.png') ?>"
              width="45" height="45" class="rounded-circle me-3" style="object-fit:cover;">
            <div class="flex-grow-1">
              <a href="profile.php?user_id=<?= $f['id'] ?>" class="nav-link">
                <?= htmlspecialchars($f['name'] . ' ' . $f['last_name']) ?>
              </a>
            </div>
            <form action="delete_friend.php" method="post">
              <input type="hidden" name="friend_id" value="<?= $f['id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-danger ms-auto">Hapus</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="alert alert-info text-center">
        Kamu belum punya teman. <a href="timeline.php" class="text-primary fw-semibold" style="text-decoration: none;">Cari teman yuk!</a>
      </div>
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

  <?php include_once '../app/views/footer.php'; ?>
  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/notification.js"></script>

</body>
</html>
