<?php
require_once __DIR__ . '/../../config/database.php';
$currentPage = basename($_SERVER['PHP_SELF']);

$user_id = $_SESSION['user_id'] ?? null;
$admin_id = $_SESSION['admin_id'] ?? null;
$isAdmin = isset($admin_id);
$isUser = isset($user_id);

$user = null;
$unread_notif_count = 0;
$friend_request_count = 0;
$total_unread = 0;

// Cek data pengguna
// Jika login sebagai user
if ($isUser) {
  // Ambil profil user
  $stmt = mysqli_prepare($koneksi, "SELECT name, last_name, photo FROM profiles WHERE user_id = ?");
  mysqli_stmt_bind_param($stmt, "i", $user_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $user = mysqli_fetch_assoc($result);
  $user['full_name'] = $user['name'] . ' ' . $user['last_name'];

  // Notifikasi
  $stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0");
  mysqli_stmt_bind_param($stmt, "i", $user_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $unread_notif_count = mysqli_fetch_assoc($result)['total'] ?? 0;

  // Permintaan teman
  $stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM friend_requests WHERE to_user = ? AND status = 'pending'");
  mysqli_stmt_bind_param($stmt, "i", $user_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $friend_request_count = mysqli_fetch_assoc($result)['total'] ?? 0;

  // Pesan
  $stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total_unread FROM messages WHERE to_user = ? AND is_read = 0");
  mysqli_stmt_bind_param($stmt, "i", $user_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $total_unread = mysqli_fetch_assoc($result)['total_unread'] ?? 0;
}

// Jika login sebagai admin
elseif ($isAdmin) {
  $user = [
    'name' => 'Administrator',
    'photo' => 'assets/img/default_profile.png'
  ];
}
?>

<header class="sticky-top bg-info border-bottom">
  <div class="container">
    <nav class="navbar navbar-expand-lg navbar-dark bg-info">
      <a class="navbar-brand" href="index.php">
        <img src="assets/icon/web-logo-putih.png" alt="Logo" width="100" height="60">
      </a>

      <!-- Hamburger -->
      <button class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Navbar Content -->
      <div class="collapse navbar-collapse" id="mainNavbar">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a href="index.php" class="nav-link <?= ($currentPage === 'index.php') ? 'active' : '' ?>">
              <i class="bi bi-house-fill"></i>
            </a>
          </li>
          <li class="nav-item position-relative">
            <a href="friends.php" class="nav-link <?= ($currentPage === 'friends.php') ? 'active' : '' ?>">
              <i class="bi bi-people-fill"></i>
              <?php if ($friend_request_count > 0): ?>
                <span id="friend-dot"
                  class="position-absolute top-10 start-10 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
              <?php endif; ?>
            </a>
          </li>
          <li class="nav-item position-relative">
            <button class="btn btn-link nav-link position-relative" data-bs-toggle="modal" data-bs-target="#notificationModal">
              <i class="bi bi-bell-fill"></i>
              <?php if ($unread_notif_count > 0): ?>
                <span id="notif-dot"
                  class="position-absolute top-10 start-10 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
              <?php endif; ?>
            </button>
          </li>
          <li class="nav-item position-relative">
            <a href="messages.php" class="nav-link <?= ($currentPage === 'messages.php') ? 'active' : '' ?>">
              <i class="bi bi-chat-left-fill"></i>
              <?php if ($currentPage !== 'messages.php' && $total_unread > 0): ?>
                <span id="notif-dot"
                  class="position-absolute top-10 start-10 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
              <?php endif; ?>
            </a>
          </li>
        </ul>

        <!-- Search -->
        <form class="d-flex me-3" action="search.php" method="GET" role="search">
          <input class="form-control me-2" name="q" type="search" placeholder="Search..." aria-label="Search" required>
          <button class="btn btn-light" type="submit"><i class="bi bi-search"></i></button>
        </form>

        <!-- User Dropdown -->
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <?php if ($isAdmin && !$isUser): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?= $user['photo'] ?? 'assets/img/default_profile.png' ?>"
                  width="30" height="30" class="rounded-circle me-2" style="object-fit:cover; background-color: #fff;">
                Profil
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="../dashboard/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item" href="../public/login.php"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a></li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?= $user['photo'] ?? 'assets/img/default_profile.png' ?>"
                  width="30" height="30" class="rounded-circle me-2" style="object-fit:cover; background-color: #fff;">
                <?= htmlspecialchars($user['full_name'] ?? 'Profil') ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-fill me-2"></i>Profil</a></li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
              </ul>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </nav>
  </div>
</header>

<script>
  setInterval(() => {
    fetch('ping_active.php'); // Sesuaikan path jika perlu
  }, 5000); // 30 detik
</script>