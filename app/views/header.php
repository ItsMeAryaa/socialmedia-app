<?php
require_once __DIR__ . '/../../config/database.php';

$currentPage = basename($_SERVER['PHP_SELF']);

if ($currentPage === 'notifications.php') {
  $unread_notif_count = 0;
}

$user_id = $_SESSION['user_id'] ?? null;
$isAdmin = false;
$user = null;
$unread_notif_count = 0;
$friend_request_count = 0;

// Cek apakah user login
if ($user_id) {
  // Cek apakah user adalah admin
  $stmt = mysqli_prepare($koneksi, "SELECT * FROM admins WHERE user_id = ?");
  mysqli_stmt_bind_param($stmt, "i", $user_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $isAdmin = mysqli_fetch_assoc($result) ? true : false;

  // Ambil profil user (walaupun admin tetap bisa punya profil)
  $stmt = mysqli_prepare($koneksi, "SELECT p.name, p.photo FROM profiles p WHERE p.user_id = ?");
  mysqli_stmt_bind_param($stmt, "i", $user_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $user = mysqli_fetch_assoc($result);

  // Hitung notifikasi & permintaan teman (boleh muncul meski admin)
  $stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0");
  mysqli_stmt_bind_param($stmt, "i", $user_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($result);
  $unread_notif_count = $row['total'] ?? 0;

  $stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM friend_requests WHERE to_user = ? AND status = 'pending'");
  mysqli_stmt_bind_param($stmt, "i", $user_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($result);
  $friend_request_count = $row['total'] ?? 0;
}

// Ambil total unread messages untuk current user
$stmt = mysqli_prepare($koneksi,
    "SELECT COUNT(*) AS total_unread
      FROM messages
      WHERE to_user = ? AND is_read = 0"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$total_unread = mysqli_fetch_assoc($res)['total_unread'] ?? 0;

// Ambil notif untuk ditampilkan ke menu notif
$result = mysqli_query($koneksi,
    "SELECT COUNT(*) AS unread FROM notifications
    WHERE user_id = $user_id AND is_read = 0");
$notif = mysqli_fetch_assoc($result);
$unread_count = (int)$notif['unread'];
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">
      <img src="assets/icon/web-logo.png" alt="Logo" width="95" height="55" class="d-inline-block align-text">
    </a>

    <!-- Toggler untuk mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- Menu User/Admin -->
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link <?= ($currentPage === 'index.php') ? 'active' : '' ?>" href="index.php">
            <i class="bi bi-house-fill"></i>
          </a>
        </li>
        <li class="nav-item position-relative">
          <a class="nav-link <?= ($currentPage === 'friends.php') ? 'active' : '' ?>" href="friends.php">
            <i class="bi bi-people-fill"></i>
            <?php if ($friend_request_count > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $friend_request_count ?>
              </span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item position-relative dropdown">
          <button class="btn btn-link nav-link position-relative"
            data-bs-toggle="modal"
            data-bs-target="#notificationModal">
            <i class="bi bi-bell-fill"></i>
            <?php if ($unread_count > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unread_count ?>
              </span>
            <?php endif; ?>
          </button>
        </li>
        <li class="nav-item position-relative">
          <a class="nav-link <?= ($currentPage==='messages.php')?'active':'' ?>" href="messages.php">
            <i class="bi bi-chat-left-fill"></i>
            <?php if($currentPage!=='messages.php' && $total_unread>0): ?>
              <span class="badge rounded-pill bg-danger"><?= $total_unread ?></span>
            <?php endif; ?>
          </a>
        </li>
      </ul>

      <!-- Form Search -->
      <form class="d-flex me-3" action="search.php" method="GET" role="search">
        <input class="form-control me-1" name="q" type="search" placeholder="Search" aria-label="Search" required>
        <button class="btn btn-light" type="submit"><i class="bi bi-search"></i></button>
      </form>

      <!-- Bagian Kanan (Profile atau Login Admin) -->
      <ul class="navbar-nav ms-auto">
        <?php if ($isAdmin): ?>
          <li class="nav-item">
            <a href="login.php" class="btn btn-info text-light">Login</a>
          </li>
        <?php else: ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="<?= $user['photo'] ?? 'assets/img/default_profile.png' ?>"
                class="me-2" width="26" height="26" style="object-fit:cover;border-radius:50%" />
              <?= htmlspecialchars($user['name'] ?? 'Profil') ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
              <li>
                <a class="dropdown-item" href="profile.php">
                  <i class="bi bi-person-fill me-2"></i> Akun Saya
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger" href="logout.php">
                  <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
              </li>
            </ul>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
