<?php
session_start();
require __DIR__ . '/../config/database.php';

$currentUserId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null; // ✅ dukung user & admin

if (!$currentUserId) {
  header('Location: login.php');
  exit;
}

// Ambil user_id dari URL jika ada dan valid
$viewedUserId = isset($_GET['user_id']) && is_numeric($_GET['user_id']) ? (int)$_GET['user_id'] : $currentUserId;

// Cek apakah ini profil milik sendiri
$isOwner = ($currentUserId === $viewedUserId);

// Cek apakah ini admin
$isAdmin = isset($_SESSION['admin_id']) && !isset($_SESSION['user_id']);

// Ambil data profil dari tabel profiles berdasarkan user_id yang dikunjungi
$stmt = mysqli_prepare($koneksi, "
  SELECT p.user_id, p.name, p.last_name, p.bio, p.photo, p.cover_photo, u.created_at AS joined_at
  FROM profiles p
  JOIN users u ON u.id = p.user_id
  WHERE p.user_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $viewedUserId);
mysqli_stmt_execute($stmt);
$result_user = mysqli_stmt_get_result($stmt);
$userProfile = mysqli_fetch_assoc($result_user);
$userProfile['full_name'] = $userProfile['name'] . ' ' . $userProfile['last_name'];

$isFriend = false;
$isPending = false;

if (!$isOwner) {
  // Cek apakah sudah berteman
  $stmt = mysqli_prepare($koneksi, "SELECT * FROM friends
    WHERE (user_id = ? AND friend_id = ? OR user_id = ? AND friend_id = ?)
    AND status = 'accepted'");
  mysqli_stmt_bind_param($stmt, "iiii", $currentUserId, $viewedUserId, $viewedUserId, $currentUserId);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $isFriend = mysqli_num_rows($result) > 0;

  // Jika belum berteman, cek apakah sudah kirim permintaan
  if (!$isFriend) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM friend_requests
      WHERE from_user = ? AND to_user = ? AND status = 'pending'");
    mysqli_stmt_bind_param($stmt, "ii", $currentUserId, $viewedUserId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $isPending = mysqli_num_rows($result) > 0;
  }
}

if (!$userProfile) {
  echo "User tidak ditemukan.";
  exit;
}

// Hitung total teman
$stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM friends WHERE user_id = ? AND status = 'accepted'");
mysqli_stmt_bind_param($stmt, "i", $viewedUserId); // ✅ ganti
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_friends = mysqli_fetch_assoc($result)['total'] ?? 0;

// Hitung total postingan
$stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM posts  WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $viewedUserId); // ✅ ganti
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_posts = mysqli_fetch_assoc($result)['total'] ?? 0;

// Ambil semua postingan untuk ditampilkan
$stmt = mysqli_prepare($koneksi, "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $viewedUserId); // ✅ ganti
mysqli_stmt_execute($stmt);
$posts = mysqli_stmt_get_result($stmt);

$post_ids = [];
while ($row = mysqli_fetch_assoc($posts)) {
  $all_posts[] = $row;
  $post_ids[] = $row['id'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Saya</title>
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/icon/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/icon/web-icon.png" rel="website icon">
  <link rel="stylesheet" href="assets/css/styleProfil.css">
</head>

<body>
  <?php include_once '../app/views/navbar.php'; ?>
  <div class="container my-4">
  <!-- Header Profil dengan Cover -->
  <div class="card shadow-lg mb-4 p-0">
    <div class="position-relative">
      <!-- Foto Sampul -->
      <img src="<?= $userProfile['cover_photo'] ?: 'assets/img/default_cover.png' ?>" class="img-fluid w-100"
        style="height: 240px; object-fit: cover; border-radius: 5px 5px 0 0;">
    </div>

    <!-- Foto Profil -->
    <div class="position-absolute start-50 top-50 translate-middle bottom-0">
      <div class="profile-container">
        <div class="profile-circle">
          <img src="<?= $userProfile['photo'] ?: 'assets/img/default_profile.png' ?>"
            class="profile-image"
            width="130" height="130"
            style="background-color: #fff;">
        </div>
      </div>
    </div>

    <!-- Info User -->
    <div class="text-center mt-5 pt-4 px-3">
      <!-- Tombol Edit Profil -->
      <?php if ($isOwner): ?>
        <div class="position-absolute end-0 top-40 translate-middle-y me-4">
          <button class="edit-btn no-wra btn btn-info text-light btn-sm" data-bs-toggle="modal" data-bs-target="#editProfileModal">
            <i class="bi bi-pencil-fill me-2"></i> Edit Profile
          </button>
        </div>
      <?php endif; ?>

      <?php if (!$isOwner): ?>
        <div class="position-absolute end-0 top-40 translate-middle-y me-4">
          <?php if ($isFriend): ?>
            <div class="dropdown">
              <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-check2-circle"></i> Berteman
              </button>
              <ul class="dropdown-menu">
                <li>
                  <form action="unfriend.php" method="POST" onsubmit="return confirm('Hapus pertemanan?')">
                    <input type="hidden" name="friend_id" value="<?= $viewedUserId ?>">
                    <button type="submit" class="dropdown-item text-danger">Hapus Teman</button>
                  </form>
                </li>
              </ul>
            </div>
          <?php elseif ($isPending): ?>
            <form action="cancel_request.php" method="POST">
              <input type="hidden" name="to_user" value="<?= $viewedUserId ?>">
              <button type="submit" class="btn btn-danger btn-sm">
                <i class="bi bi-clock-history"></i> Batal Permintaan
              </button>
            </form>
          <?php else: ?>
            <form action="send_request.php" method="POST">
              <input type="hidden" name="to_user" value="<?= $viewedUserId ?>">
              <button type="submit" class="btn btn-info text-light btn-sm">
                <i class="bi bi-person-plus"></i> Tambah Teman
              </button>
            </form>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <h4 class="mb-2 fw-bold">
        <?= htmlspecialchars($userProfile['full_name']) ?>
        <i class="bi bi-patch-check-fill text-primary" title="Verified"></i>
      </h4>
      <div class="text-muted mb-2"><?= $total_friends ?> Berteman</div>

      <div class="d-flex justify-content-center flex-wrap gap-3 small text-muted mb-3">
        <span>
          <i class="bi bi-calendar-event mb-3"></i> Bergabung pada <?= date('d M, Y', strtotime($userProfile['joined_at'])) ?>
        </span>
      </div>

      <?php if (!empty($userProfile['bio'])): ?>
        <p class="mt-3 text-dark"><?= nl2br(htmlspecialchars($userProfile['bio'])) ?></p>
      <?php endif; ?>
    </div>
  </div>

  <div class="card mb-4 shadow-lg">
    <div class="card-body">
      <form method="POST" action="post_create.php" enctype="multipart/form-data">
        <div class="d-flex mb-3">
          <img src="<?= htmlspecialchars($userProfile['photo'] ?? 'assets/img/default_profile.png') ?>"
              alt="Foto Profil" width="45" height="45"
              class="rounded-circle me-2" style="object-fit:cover">
          <input type="text"
                name="content"
                class="form-control border-0"
                placeholder="Share your thoughts..."
                maxlength="500"
                style="background-color: #f0f2f5; border-radius: 20px; padding: 10px 15px;">
        </div>

        <!-- Tombol input terpisah -->
        <div class="d-flex flex-wrap gap-2 mt-2">
          <label class="btn btn-sm btn-light d-flex align-items-center gap-1">
            <i class="bi bi-image text-success"></i> Photo
            <input type="file" id="photo-input" name="photo_files[]" accept="image/*" hidden multiple>
          </label>
          <label class="btn btn-sm btn-light d-flex align-items-center gap-1">
            <i class="bi bi-camera-video text-primary"></i> Video
            <input type="file" id="video-input" name="video_files[]" accept="video/*" hidden multiple>
          </label>
          <button type="submit" class="btn btn-sm btn-info text-light ms-auto">
            <i class="bi bi-send-fill"></i>
          </button>
        </div>

        <!-- Preview foto dan video -->
        <div id="photo-preview" class="d-flex flex-wrap"></div>
        <div id="video-preview" class="d-flex flex-wrap"></div>
      </form>
    </div>
  </div>

    <?php
    $post_files_by_post = [];
    $likes_by_post = [];
    $comments_by_post = [];

    if (!empty($post_ids)) {
      // Gabungkan post_id jadi string untuk IN clause
      $in = implode(',', array_map('intval', $post_ids)); // pastikan aman dari injeksi

      // Ambil file
      $query = mysqli_query($koneksi, "SELECT * FROM post_files WHERE post_id IN ($in)");
      while ($file = mysqli_fetch_assoc($query)) {
        $post_files_by_post[$file['post_id']][] = $file;
      }

      // Ambil jumlah like per post
      $query = mysqli_query($koneksi, "SELECT post_id, COUNT(*) as like_count FROM likes WHERE post_id IN ($in) GROUP BY post_id");
      while ($like = mysqli_fetch_assoc($query)) {
        $likes_by_post[$like['post_id']] = $like['like_count'];
      }

      // Ambil jumlah komentar per post
      $query = mysqli_query($koneksi, "SELECT post_id, COUNT(*) as comment_count FROM comments WHERE post_id IN ($in) GROUP BY post_id");
      while ($comment = mysqli_fetch_assoc($query)) {
        $comments_by_post[$comment['post_id']] = $comment['comment_count'];
      }
    }
    ?>

    <div class="card mt-4 p-4 text-dark shadow-lg">
      <h5>Postingan Saya</h5>
      <hr>
      <?php foreach ($all_posts as $p): ?>
        <div class="mb-4 p-3 rounded shadow-lg position-relative text-dark">
          <div class="small text-secondary"><?= date('d M Y H:i', strtotime($p['created_at'])) ?></div>
          <div class="mb-2 mt-3"><?= nl2br(htmlspecialchars($p['content'])) ?></div>

          <?php if ($isOwner): ?>
            <div class="dropdown position-absolute top-0 end-0 me-2 mt-2">
              <button class="btn btn-sm btn-link text-dark"
                type="button"
                id="dropdownMenu<?= $p['id'] ?>"
                data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="bi bi-three-dots-vertical fs-5"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenu<?= $p['id'] ?>">
                <li>
                  <button class="dropdown-item"
                    data-bs-toggle="modal"
                    data-bs-target="#editModal<?= $p['id'] ?>">Ubah</button>
                </li>
                <li>
                  <form method="POST" action="post_delete.php" onsubmit="return confirm('Hapus postingan ini?')" style="display:inline;">
                    <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
                    <button type="submit" class="dropdown-item text-danger">Hapus</button>
                  </form>
                </li>
              </ul>
            </div>
          <?php endif; ?>

          <?php if (!empty($post_files_by_post[$p['id']])): ?>
            <div class="row">
              <?php foreach ($post_files_by_post[$p['id']] as $file): ?>
                <div class="col-12 col-md-6 mb-2 mt-4">
                  <?php if ($file['file_type'] === 'image'): ?>
                    <img src="<?= $file['file_path'] ?>" class="img-fluid rounded">
                  <?php elseif ($file['file_type'] === 'video'): ?>
                    <video controls class="w-100"><source src="<?= $file['file_path'] ?>"></video>
                  <?php elseif ($file['file_type'] === 'audio'): ?>
                    <audio controls class="w-100"><source src="<?= $file['file_path'] ?>"></audio>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="mt-2">
            <small class="text-muted">
              <i class="bi bi-heart-fill text-danger"></i> <?= $likes_by_post[$p['id']] ?? 0 ?> | <i class="bi bi-chat-left-dots"></i> <?= $comments_by_post[$p['id']] ?? 0 ?>
            </small>
          </div>
        </div>

        <!-- Modal Edit -->
        <div class="modal fade" id="editModal<?= $p['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $p['id'] ?>" aria-hidden="true">
          <div class="modal-dialog">
            <form class="modal-content bg-light text-dark" method="POST" action="post_edit.php">
              <input type="hidden" name="post_id" value="<?= $p['id'] ?>">
              <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel<?= $p['id'] ?>">Ubah Postingan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <textarea name="content"
                  class="form-control bg-light text-dark" rows="4" maxlength="500" required><?= htmlspecialchars($p['content']) ?>
                </textarea>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-info text-light">Simpan Perubahan</button>
              </div>
            </form>
          </div>
        </div>

      <?php endforeach; ?>
    </div>
  </div>

  <!-- Modal Edit Profile -->
  <div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog">
      <form class="modal-content" method="POST" action="update_profile.php" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Edit Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Nama</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($userProfile['name']) ?>" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Bio</label>
            <textarea name="bio" rows="3" class="form-control"><?= htmlspecialchars($userProfile['bio']) ?></textarea>
          </div>
          <div class="mb-2">
            <label class="form-label">Foto Profil</label>
            <input type="file" name="photo" class="form-control" accept="image/*">
          </div>
          <div class="mb-2">
            <label class="form-label">Foto Sampul</label>
            <input type="file" name="cover_photo" class="form-control" accept="image/*">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Simpan Perubahan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
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

  <?php include_once '../app/views/footer.php'; ?>

  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/notification.js"></script>
  <script src="assets/js/file_preview.js"></script>

</body>
</html>
