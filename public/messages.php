<?php
session_start();
require_once '../config/database.php';

$currentUserId = $_SESSION['user_id'] ?? null;
$isAdmin = isset($_SESSION['admin_id']);

if (!$currentUserId && !$isAdmin) {
  header('Location: login.php');
  exit;
}

// --- 1) Inisialisasi array $friends dan ambil data teman ---
$friends = [];
$searchTerm = $_GET['search_user'] ?? null;

if ($isAdmin) {
  $friends = [];
  // Admin bisa lihat semua user (kecuali admin sendiri)
  if ($searchTerm) {
    $query = "SELECT u.id, p.name, p.last_name, p.photo
              FROM users u
              JOIN profiles p ON u.id = p.user_id
              WHERE p.name LIKE CONCAT('%', ?, '%')";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, 's', $searchTerm);
  } else {
    $query = "SELECT u.id, p.name, p.last_name, p.photo
              FROM users u
              JOIN profiles p ON u.id = p.user_id";
    $stmt = mysqli_prepare($koneksi, $query);
  }
} else {
  // User hanya lihat teman yang sudah berteman
  if ($searchTerm) {
    $query = "SELECT DISTINCT u.id, p.name, p.last_name, p.photo
              FROM friends f
              JOIN users u ON (
                (f.user_id = ? AND u.id = f.friend_id) OR
                (f.friend_id = ? AND u.id = f.user_id)
              )
              JOIN profiles p ON u.id = p.user_id
              WHERE f.status = 'accepted' AND p.name OR p.last_name LIKE CONCAT('%', ?, '%')";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, 'iis', $currentUserId, $currentUserId, $searchTerm);
  } else {
    $query = "SELECT DISTINCT u.id, p.name, p.last_name, p.photo
              FROM friends f
              JOIN users u ON (
                (f.user_id = ? AND u.id = f.friend_id) OR
                (f.friend_id = ? AND u.id = f.user_id)
              )
              JOIN profiles p ON u.id = p.user_id
              WHERE f.status = 'accepted'";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $currentUserId, $currentUserId);
  }
}

// Eksekusi hanya sekali di sini
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
  $friends[] = $row;
}
mysqli_stmt_close($stmt);

// --- 2) Ambil data unread messages ---
$unreads = [];
$sql = "SELECT from_user, COUNT(*) AS cnt
        FROM messages
        WHERE to_user = ? AND is_read = 0
        GROUP BY from_user";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "i", $currentUserId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
while($r = mysqli_fetch_assoc($res)){
  $unreads[$r['from_user']] = $r['cnt'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Messenger - Temanku</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/styleMessage.css">
  <link rel="stylesheet" href="assets/icon/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="website icon" href="assets/icon/web-icon.png">
</head>

<body>

  <?php require_once '../app/views/navbar.php'; ?>
  <div class="container">
    <div class="mt-5 mb-5 text-dark p-3 rounded messenger-container shadow-lg">
      <div class="row">
        <!-- Teman -->
        <div class="col-md-4 friend-list <?= $isAdmin ? 'd-none' : '' ?>">
          <h5>Teman</h5>
          <!-- Form Pencarian User -->
          <form method="GET" class="mb-3">
            <div class="input-group">
              <input type="text"
                  class="form-control"
                  name="search_user"
                  placeholder="Cari user..."
                  value="<?= htmlspecialchars($_GET['search_user'] ?? '') ?>">
              <button class="btn btn-outline-info" type="submit">
                <i class="bi bi-search"></i>
              </button>
            </div>
          </form>
          <ul class="list-group">
            <?php foreach ($friends as $friend):
            $fid = $friend['id'];
            $badge = isset($unreads[$fid]) ? $unreads[$fid] : 0;
            ?>
              <li class="list-group-item d-flex align-items-center friend-item text-dark"
                  data-user-id="<?= $fid ?>"
                  data-username="<?= htmlspecialchars($friend['name'] . ' ' . $friend['last_name']) ?>"
                  style="cursor:pointer;">

                <!-- Foto Profil + Status Dot -->
                <div class="position-relative me-3" style="width: 32px; height: 32px;">
                  <img src="<?= $friend['photo'] ?? 'assets/img/default_profile.png' ?>"
                      class="rounded-circle friend-photo"
                      width="32" height="32"
                      alt="<?= htmlspecialchars($friend['name'] . ' ' . $friend['last_name']) ?>"
                      data-user-id="<?= $fid ?>">

                  <!-- Dot Status -->
                  <span id="status-dot-<?= $fid ?>"
                        class="status-dot position-absolute bottom-0 end-0 border border-white rounded-circle"
                        style="width:10px; height:10px; background-color:gray;"></span>
                </div>

                <!-- Nama User -->
                <span class="friend-name"><?= htmlspecialchars($friend['name'] . ' ' . $friend['last_name']) ?></span>

                <!-- Notif pesan masuk -->
                <?php if($badge): ?>
                  <span class="badge bg-danger rounded-circle float-end ms-2"><?= $badge ?></span>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
            <?php if (empty($friends)): ?>
              <li class="list-group text-muted">Tidak ada hasil.</li>
            <?php endif; ?>
          </ul>
        </div>

        <!-- Chat Room -->
        <div class="<?= $isAdmin ? 'col-12' : 'col-md-8' ?> chat-room d-none d-md-block">
          <div class="d-flex justify-content-between align-items-center px-3 py-2">
              <div class="d-flex align-items-center">
                <button class="nav-link btn-info d-md-none" id="closeChatBtn">
                  <i class="bi bi-caret-left-fill me-3"></i>
                </button>
                <h5 id="chatWithName">
                  Pilih teman untuk mulai chat
                </h5>
              </div>
            <button id="btnDeleteAll" class="nav-link btn-dark d-none">
              <i class="bi bi-three-dots-vertical"></i>
            </button>
          </div>

          <div id="chatBox" class="chat-box border rounded text-dark"
            style="min-height: 300px;">
            <div id="chatPlaceholder" class="text-secondary text-center">
              <div class="fs-5">Berbagi cerita, pengalaman, dan hal-hal lainnya menggunakan chat</div>
            </div>
          </div>

          <div id="filePreview" class="mt-2"></div>
          <form id="chatForm" class="d-none" enctype="multipart/form-data">
            <input type="hidden" name="to_user" id="toUserInput">
            <div class="input-group">

              <!-- Tombol file -->
              <button type="button" class="btn btn-secondary" onclick="document.getElementById('fileInput').click()">
                <i class="bi bi-paperclip"></i>
              </button>
              <input type="file" name="file" id="fileInput" class="d-none" accept="image/*,video/*,audio/*">

              <!-- WRAPPER untuk input + tombol emoji -->
              <div class="position-relative flex-grow-1">
                <!-- Input Pesan -->
                <input type="text"
                      class="form-control pe-5"
                      name="message"
                      id="chatMessageInput"
                      placeholder="Ketik pesan...">

                <!-- Tombol Emoji di dalam input -->
                <div class="dropdown position-absolute end-0 top-50 translate-middle-y me-2">
                  <a class="nav-link p-0" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-emoji-smile" style="font-size: 20px;"></i>
                  </a>
                  <ul class="dropdown-menu p-2" style="width: 250px; max-height: 200px; overflow-y: auto; font-size: 20px;">
                    <li class="d-flex flex-wrap gap-1 px-2">
                      <span class="emoji" style="cursor:pointer;">😀</span>
                      <span class="emoji" style="cursor:pointer;">😁</span>
                      <span class="emoji" style="cursor:pointer;">😂</span>
                      <span class="emoji" style="cursor:pointer;">🤣</span>
                      <span class="emoji" style="cursor:pointer;">😃</span>
                      <span class="emoji" style="cursor:pointer;">😄</span>
                      <span class="emoji" style="cursor:pointer;">😘</span>
                      <span class="emoji" style="cursor:pointer;">🥰</span>
                      <span class="emoji" style="cursor:pointer;">😍</span>
                      <span class="emoji" style="cursor:pointer;">😎</span>
                      <span class="emoji" style="cursor:pointer;">😋</span>
                      <span class="emoji" style="cursor:pointer;">😊</span>
                      <span class="emoji" style="cursor:pointer;">😉</span>
                      <span class="emoji" style="cursor:pointer;">😆</span>
                      <span class="emoji" style="cursor:pointer;">😅</span>
                      <span class="emoji" style="cursor:pointer;">😜</span>
                      <span class="emoji" style="cursor:pointer;">🤪</span>
                      <span class="emoji" style="cursor:pointer;">😏</span>
                      <span class="emoji" style="cursor:pointer;">😒</span>
                      <span class="emoji" style="cursor:pointer;">😞</span>
                      <span class="emoji" style="cursor:pointer;">😔</span>
                      <span class="emoji" style="cursor:pointer;">😢</span>
                      <span class="emoji" style="cursor:pointer;">😭</span>
                      <span class="emoji" style="cursor:pointer;">😤</span>
                      <span class="emoji" style="cursor:pointer;">😠</span>
                      <span class="emoji" style="cursor:pointer;">😡</span>
                      <span class="emoji" style="cursor:pointer;">🤬</span>
                      <span class="emoji" style="cursor:pointer;">😱</span>
                      <span class="emoji" style="cursor:pointer;">😨</span>
                      <span class="emoji" style="cursor:pointer;">😰</span>
                      <span class="emoji" style="cursor:pointer;">👍</span>
                      <span class="emoji" style="cursor:pointer;">👎</span>
                      <span class="emoji" style="cursor:pointer;">👊</span>
                      <span class="emoji" style="cursor:pointer;">✌️</span>
                      <span class="emoji" style="cursor:pointer;">🤞</span>
                      <span class="emoji" style="cursor:pointer;">🙌</span>
                      <span class="emoji" style="cursor:pointer;">👏</span>
                      <span class="emoji" style="cursor:pointer;">🙏</span>
                      <span class="emoji" style="cursor:pointer;">🤝</span>
                      <span class="emoji" style="cursor:pointer;">💪</span>
                      <span class="emoji" style="cursor:pointer;">🧠</span>
                      <span class="emoji" style="cursor:pointer;">🦾</span>
                      <span class="emoji" style="cursor:pointer;">🦿</span>
                      <span class="emoji" style="cursor:pointer;">❤️</span>
                      <span class="emoji" style="cursor:pointer;">💔</span>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Tombol Kirim -->
              <button class="btn btn-info text-light" type="submit">
                <i class="bi bi-send"></i>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
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

  <?php require_once '../app/views/footer.php'; ?>
  <script>
    const currentUserId = <?= $currentUserId ?? ($_SESSION['admin_id'] ?? 'null') ?>;
  </script>

  <script>
    // Tambahkan emoji ke dalam input pesan
    document.querySelectorAll('.emoji').forEach(el => {
      el.addEventListener('click', () => {
        const input = document.getElementById('chatMessageInput');
        input.value += el.textContent;
        input.focus();
      });
    });

    // Hindari dropdown tertutup saat klik emoji
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
      menu.addEventListener('click', function (e) {
        e.stopPropagation(); // cegah klik dalam menu menutup dropdown
      });
    });
  </script>

  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/check_status_online.js"></script>
  <script src="assets/js/chat.js"></script>
  <script src="assets/js/notification.js"></script>

</body>
</html>
