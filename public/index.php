<?php
session_start();
require __DIR__ . '/../config/database.php';

// ✅ Redirect jika belum login sebagai user atau admin
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
  header('Location: login.php');
  exit;
}

// ✅ Ambil user_id dari session
$user_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'];
$isAdmin = isset($_SESSION['admin_id']);

// ✅ Ambil profil (jika tersedia di tabel profiles)
$stmt = mysqli_prepare($koneksi, "SELECT p.name, p.last_name, p.photo FROM profiles p WHERE p.user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$profile = mysqli_fetch_assoc($result);
$profile['full_name'] = $profile['name'] . ' ' . $profile['last_name'];

// Jika profil tidak ditemukan (biasanya admin), gunakan default
if (!$profile) {
  $profile = [
    'name' => $isAdmin ? 'Admin' : 'User',
    'photo' => 'assets/img/default_profile.png'
  ];
}

// ✅ Format teks postingan (fungsi)
function format_post_text($txt)
{
  $txt = preg_replace('/#([\w]+)/', '<a href="search.php?tag=$1" class="text-primary fw-semibold">#$1</a>', $txt);
  $txt = preg_replace('/@([\w]+)/', '<a href="profile.php?n=$1" class="text-info fw-semibold">@$1</a>', $txt);
  return nl2br($txt);
}

// ✅ Ambil postingan user dan teman
if ($isAdmin) {
  // Ambil postingan milik admin saja (misalnya admin bisa post seperti user)
  $stmt = mysqli_prepare(
    $koneksi,
    "SELECT posts.id, posts.user_id, posts.content, posts.created_at, ? AS name, ? AS photo,
      (SELECT COUNT(*) FROM likes WHERE post_id = posts.id) AS like_count,
      0 AS is_liked
    FROM posts
    WHERE posts.user_id = ?
    ORDER BY posts.created_at DESC"
  );
  mysqli_stmt_bind_param($stmt, "ssi", $profile['name'], $profile['photo'], $user_id);
} else {
  // Logika user biasa
  $stmt = mysqli_prepare(
    $koneksi,
    "SELECT posts.id, posts.user_id, posts.content, posts.created_at, p.name, p.photo,
      (SELECT COUNT(*) FROM likes WHERE post_id = posts.id) AS like_count,
      (SELECT COUNT(*) FROM likes WHERE post_id = posts.id AND user_id = ?) AS is_liked
    FROM posts
    JOIN users u ON posts.user_id = u.id
    JOIN profiles p ON u.id = p.user_id
    WHERE posts.user_id = ? OR posts.user_id IN (
      SELECT friend_id FROM friends WHERE user_id = ? AND status = 'accepted'
      UNION
      SELECT user_id FROM friends WHERE friend_id = ? AND status = 'accepted'
    )
    ORDER BY RAND() DESC"
  );
  mysqli_stmt_bind_param($stmt, "iiii", $user_id, $user_id, $user_id, $user_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
}

$posts = [];
while ($row = mysqli_fetch_assoc($result)) {
  $posts[] = $row;
}

// ✅ Ambil file media untuk tiap post
$post_ids = array_column($posts, 'id');
$post_files_by_post = [];

if (!empty($post_ids)) {
  $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
  $types = str_repeat('i', count($post_ids));
  $query = "SELECT * FROM post_files WHERE post_id IN ($placeholders)";
  $stmt = mysqli_prepare($koneksi, $query);
  mysqli_stmt_bind_param($stmt, $types, ...$post_ids);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  while ($f = mysqli_fetch_assoc($result)) {
    $post_files_by_post[$f['post_id']][] = $f;
  }
}

// ✅ Ambil semua komentar tiap post
$comments_by_post = [];
if (!empty($posts)) {
  $placeholders = implode(',', array_fill(0, count($post_ids), '?'));
  $types = str_repeat('i', count($post_ids) + 1); // +1 untuk user_id
  $query = "
    SELECT c.*, p.name, p.photo,
      (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id) AS like_count,
      (SELECT COUNT(*) FROM comment_likes WHERE comment_id = c.id AND user_id = ?) AS is_liked
    FROM comments c
    JOIN users u ON c.user_id = u.id
    JOIN profiles p ON u.id = p.user_id
    WHERE c.post_id IN ($placeholders)
    ORDER BY c.created_at ASC";

  $stmt = mysqli_prepare($koneksi, $query);
  mysqli_stmt_bind_param($stmt, $types, $user_id, ...$post_ids);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  while ($c = mysqli_fetch_assoc($result)) {
    $comments_by_post[$c['post_id']][] = $c;
  }
}

// ✅ Total postingan milik user
$stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM posts WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_posts = mysqli_fetch_assoc($result)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Beranda - Temanku</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/styleHome.css" rel="stylesheet">
  <link href="assets/icon/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/icon/web-icon.png" rel="website icon">
</head>

<body>
  <?php include '../app/views/navbar.php'; ?>
  <div class="container mt-4">
    <div class="row">

      <!-- Kolom kiri -->
      <div class="col-lg-3 mb-4 d-none d-lg-block">
        <?php
        // Ambil data user dan profil
        $user_id = isset($_GET['id']) ? $_GET['id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);
        // atau dari profil yang dikunjungi
        $query = "SELECT profiles.name, profiles.last_name, profiles.photo, profiles.cover_photo, profiles.bio, profiles.user_id
                  FROM users
                  LEFT JOIN profiles ON users.id = profiles.user_id
                  WHERE users.id = ?";
        $stmt = $koneksi->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $userProfile = $result->fetch_assoc();
        $userProfile['full_name'] = $userProfile['name'] . ' ' . $userProfile['last_name'];

        // Hitung jumlah teman
        $friend_query = "SELECT COUNT(*) as total FROM friends WHERE user_id = ? AND status = 'accepted'";
        $stmt2 = $koneksi->prepare($friend_query);
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        $friend_result = $stmt2->get_result();
        $friend_data = $friend_result->fetch_assoc();
        $total_friends = $friend_data['total'] ?? 0;
        ?>
        <div class="sticky-top" style="top:100px;">
          <div class="card border border-secondary-subtle shadow-lg">
            <div class="card-body text-center">
              <div class="col-lg-12 mb-5">
                <!-- Foto Sampul -->
                <div class="position-relative mb-5">
                  <img src="<?= $userProfile['cover_photo'] ?? 'assets/img/default_cover.png' ?>"
                    class="w-100 rounded shadow"
                    style="height: 50px; object-fit: cover;">
                  <div class="position-absolute top-100 start-50 translate-middle" style="margin-top: 20px;">
                    <!-- Foto Profil -->
                    <img src="<?= $userProfile['photo'] ?? 'assets/img/default_profile.png' ?>"
                      width="100" height="100"
                      class="rounded-circle border border-white shadow"
                      style="object-fit: cover; background-color: #fff;">
                  </div>
                </div>
              </div>
              <h5 class="mb-0 mt-5" style="padding-top: 39px; align-items: center; justify-content: center;">
                <a href="profile.php?user_id=<?= $userProfile['user_id'] ?>" class="nav-link">
                  <?= htmlspecialchars($userProfile['full_name'] ?? 'Unknow') ?>
                </a>
              </h5>
              <!-- Bio -->
              <p class="mt-2 text-muted">
                <?= htmlspecialchars($userProfile['bio'] ?? '') ?>
              </p>
              <!-- Statistik -->
              <div class="d-flex justify-content-center align-items-center gap-4 text-center mt-3">
                <div>
                  <h6 class="text-muted"><?= $total_posts ?? 0 ?></h6>
                  <small class="text-muted">Post</small>
                </div>
                <div class="vr"></div>
                <div>
                  <h6 class="text-muted"><?= $total_friends ?></h6>
                  <small class="text-muted">Teman</small>
                </div>
              </div>
            </div>
          </div>
          <footer class="mt-2 p-4 text-center">
            Dibuat oleh <span class="text-info">ItsMeAryaa</span>.
            <br>
            <span class="small">&copy; <?= date('Y') ?> Temanku.</span>
          </footer>
        </div>
      </div>

      <!-- Kolom tengah -->
      <div class="col-lg-6 m">
        <?php
        require __DIR__ . '/../config/database.php';

        // Hapus story yang sudah expired
        mysqli_query($koneksi, "DELETE FROM stories WHERE expires_at <= NOW()");

        // Ambil story yang masih aktif
        $query = "
          SELECT s.*, p.name, p.photo FROM stories s
          JOIN users u ON s.user_id = u.id
          JOIN profiles p ON u.id = p.user_id
          WHERE s.expires_at > NOW()
          ORDER BY s.created_at DESC
        ";
        $result = mysqli_query($koneksi, $query);
        $stories = [];
        while ($row = mysqli_fetch_assoc($result)) {
          $stories[] = $row;
        }
        ?>

        <!-- Story List -->
        <div class="container border border-secondary-subtle shadow-lg story-container d-flex overflow-auto gap-2 mb-3 px-1">
          <?php
          require __DIR__ . '/../config/database.php';

          $me = $_SESSION['user_id'] ?? null;

          // Query ambil semua story aktif dari user sendiri dan teman yang sudah berteman
          $sql = "SELECT
              s.*,
              p.name,
              p.photo,
              EXISTS (
                SELECT 1 FROM story_views v WHERE v.user_id = ? AND v.story_id = s.id
              ) AS has_viewed,
              (
                SELECT MAX(created_at)
                FROM stories
                WHERE user_id = s.user_id
              ) AS latest_story_time
            FROM stories s
            JOIN users u ON s.user_id = u.id
            JOIN profiles p ON u.id = p.user_id
            WHERE s.expires_at > NOW()
              AND (
                s.user_id = ?
                OR s.user_id IN (
                  SELECT friend_id FROM friends WHERE user_id = ? AND status = 'accepted'
                  UNION
                  SELECT user_id FROM friends WHERE friend_id = ? AND status = 'accepted'
                )
              )
          ";

          $stmt = mysqli_prepare($koneksi, $sql);
          mysqli_stmt_bind_param($stmt, "iiii", $me, $me, $me, $me);
          mysqli_stmt_execute($stmt);
          $result = mysqli_stmt_get_result($stmt);

          $stories = [];
          while ($row = mysqli_fetch_assoc($result)) {
            $stories[] = $row;
          }

          // Kelompokkan berdasarkan user
          $grouped_stories = [];
          foreach ($stories as $story) {
            $uid = $story['user_id'];
            if (!isset($grouped_stories[$uid])) {
              $grouped_stories[$uid] = [
                'user_info' => [
                  'user_id' => $uid,
                  'name' => ($uid == $_SESSION['user_id']) ? 'Cerita saya' : $story['name'],
                  'photo' => $story['photo'] ?? 'assets/img/default_profile.png'
                ],
                'stories' => []
              ];
            }
            $grouped_stories[$uid]['stories'][] = [
              'id' => $story['id'],
              'user_id' => $story['user_id'],
              'name' => $story['name'],
              'photo' => $story['photo'] ?? 'assets/img/default_profile.png',
              'type' => $story['type'],
              'content' => $story['content'],
              'file_path' => $story['file_path'],
              'created_at' => $story['created_at'],
              'has_viewed' => $story['has_viewed']
            ];
          }

          // Pisahkan story milik sendiri dan orang lain
          $user_id = $_SESSION['user_id'] ?? null;
          $my_story = isset($grouped_stories[$user_id]) ? [$grouped_stories[$user_id]] : [];
          $unviewed_stories = [];
          $viewed_stories = [];

          foreach ($grouped_stories as $uid => $group) {
            if ($uid == $_SESSION['user_id']) continue;

            $has_unviewed = false;
            foreach ($group['stories'] as $s) {
              if (empty($s['has_viewed']) || $s['has_viewed'] == 0) {
                $has_unviewed = true;
                break;
              }
            }

            // Ambil waktu posting terakhir dari user ini
            $group['latest_story_time'] = max(array_column($group['stories'], 'created_at'));

            if ($has_unviewed) {
              $unviewed_stories[] = $group;
            } else {
              $viewed_stories[] = $group;
            }
          }

          // Urutkan unviewed dan viewed berdasarkan waktu story terakhir
          usort($unviewed_stories, function ($a, $b) {
            return strtotime($b['latest_story_time']) - strtotime($a['latest_story_time']);
          });

          usort($viewed_stories, function ($a, $b) {
            return strtotime($b['latest_story_time']) - strtotime($a['latest_story_time']);
          });

          $other_stories = array_merge($unviewed_stories, $viewed_stories);
          ?>

          <!-- Card Buat Cerita -->
          <div class="story-card text-center bg-dark text-light rounded"
            style="width:110px; flex-shrink:0; cursor:pointer;"
            data-bs-toggle="modal" data-bs-target="#storyModal">
            <div class="position-relative">
              <div class="w-100 rounded-top d-flex align-items-center justify-content-center bg-secondary"
                style="height:140px;">
                <i class="bi bi-plus-circle fs-1 text-white"></i>
              </div>
            </div>
            <div class="small fw-semibold text-center mt-1">Buat Cerita</div>
          </div>

          <!-- Story milik user -->
          <?php foreach ($my_story as $group): ?>
            <?php $info = $group['user_info'];
            $first = $group['stories'][0]; ?>
            <div class="story-card story-box bg-dark text-light rounded position-relative text-center"
              style="width:110px; flex-shrink:0; cursor:pointer;"
              data-user-id="<?= $info['user_id'] ?>"
              data-name="<?= htmlspecialchars($info['name']) ?>"
              data-photo="<?= htmlspecialchars($info['photo']) ?>">

              <div class="position-relative">
                <!-- Konten utama -->
                <?php if ($first['type'] === 'video'): ?>
                  <video class="w-100 rounded-top" style="height:140px; object-fit:cover;" muted autoplay loop>
                    <source src="<?= htmlspecialchars($first['file_path']) ?>" type="video/mp4">
                  </video>
                <?php elseif ($first['type'] === 'music'): ?>
                  <div class="w-100 rounded-top d-flex align-items-center justify-content-center bg-dark" style="height:140px;">
                    <i class="bi bi-music-note-beamed fs-1 text-white"></i>
                  </div>
                <?php elseif ($first['type'] === 'text'): ?>
                  <div class="w-100 rounded-top d-flex align-items-center justify-content-center bg-secondary text-white px-2 text-center"
                    style="height:140px; font-size: 0.85rem; overflow:hidden;">
                    <?= nl2br(htmlspecialchars(mb_strimwidth($first['content'], 0, 100, '...'))) ?>
                  </div>
                <?php else: ?>
                  <img src="<?= htmlspecialchars($first['file_path']) ?>" class="w-100 rounded-top" style="height:140px; object-fit:cover;">
                <?php endif; ?>

                <!-- Foto profil -->
                <img src="<?= htmlspecialchars($info['photo']) ?>"
                  class="rounded-circle position-absolute top-0 start-0 m-1"
                  width="32" height="32" style="object-fit:cover;">

                <!-- Tombol titik tiga -->
                <button class="btn btn-sm position-absolute top-0 end-0 m-1 text-white"
                  onclick="showStoryOptions(<?= $first['id'] ?>)">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
              </div>

              <div class="small fw-semibold text-center mt-1"><?= htmlspecialchars($info['name']) ?></div>
            </div>
          <?php endforeach; ?>

          <!-- Story milik user lain -->
          <?php foreach ($other_stories as $group): ?>
            <?php
            // Lewati jika user_id sama dengan session (untuk hindari double "Cerita Saya")
            if ($group['user_info']['user_id'] == $_SESSION['user_id']) continue;

            // Lewati jika stories kosong
            if (empty($group['stories'])) continue;

            $info = $group['user_info'];
            $first = $group['stories'][0];

            // Cek apakah semua story sudah dilihat
            $is_viewed = true;
            foreach ($group['stories'] as $s) {
              if (empty($s['has_viewed']) || $s['has_viewed'] == 0) {
                $is_viewed = false;
                break;
              }
            }
            $card_class = $is_viewed ? 'viewed' : '';
            ?>
            <div class="story-card story-box bg-dark text-light rounded position-relative text-center <?= $card_class ?>"
              style="width:110px; flex-shrink:0; cursor:pointer;"
              data-user-id="<?= $info['user_id'] ?>"
              data-name="<?= htmlspecialchars($info['name']) ?>"
              data-photo="<?= htmlspecialchars($info['photo']) ?>">
              <div class="position-relative">
                <?php if ($first['type'] === 'video'): ?>
                  <video class="w-100 rounded-top" style="height:140px; object-fit:cover;" muted autoplay loop>
                    <source src="<?= htmlspecialchars($first['file_path']) ?>" type="video/mp4">
                  </video>
                <?php elseif ($first['type'] === 'music'): ?>
                  <div class="w-100 rounded-top d-flex align-items-center justify-content-center bg-dark" style="height:140px;">
                    <i class="bi bi-music-note-beamed fs-1 text-white"></i>
                  </div>
                <?php elseif ($first['type'] === 'text'): ?>
                  <div class="w-100 rounded-top d-flex align-items-center justify-content-center bg-secondary text-white px-2 text-center"
                    style="height:140px; font-size: 0.85rem; overflow:hidden;">
                    <?= nl2br(htmlspecialchars(mb_strimwidth($first['content'], 0, 100, '...'))) ?>
                  </div>
                <?php else: ?>
                  <img src="<?= htmlspecialchars($first['file_path']) ?>" class="w-100 rounded-top" style="height:140px; object-fit:cover;">
                <?php endif; ?>
                <img src="<?= htmlspecialchars($info['photo']) ?>"
                  class="rounded-circle border border-3 border-info position-absolute top-0 start-0 m-1"
                  width="32" height="32" style="object-fit:cover;">
              </div>
              <div class="small fw-semibold text-center mt-1"><?= htmlspecialchars($info['name']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Modal Upload Story -->
        <div class="modal fade" id="storyModal" tabindex="-1" aria-labelledby="storyModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form class="modal-content bg-light text-dark" action="story_create.php" method="POST" enctype="multipart/form-data">
              <div class="modal-header">
                <h5 class="modal-title" id="storyModalLabel">Buat Story Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div id="storyAlert" class="alert alert-danger d-none" role="alert"></div>

                <div class="mb-3">
                  <label for="storyType" class="form-label">Pilih Tipe Story</label>
                  <select class="form-select bg-light text-dark" id="storyType" name="type" required>
                    <option value="" disabled selected>- Pilih -</option>
                    <option value="text">Teks</option>
                    <option value="image">Gambar</option>
                    <option value="video">Video</option>
                    <option value="music">Musik</option>
                  </select>
                </div>

                <!-- Untuk Teks -->
                <div class="mb-3 d-none" id="textContent">
                  <label class="form-label">Isi Teks</label>
                  <textarea class="form-control bg-light text-dark" name="text_content" rows="4" maxlength="300"></textarea>
                </div>

                <div class="mb-3 d-none" id="fileContent">
                  <label class="form-label">Pilih File</label>
                  <input type="file" class="form-control bg-light text-dark" name="file" id="storyFileInput" accept="image/*,video/*,audio/*">

                  <!-- Preview -->
                  <div class="mt-3" id="filePreviewContainer"></div>

                  <!-- Untuk Deskripsi File -->
                  <div class="mt-3">
                    <label for="fileDescription" class="form-label">Deskripsi</label>
                    <textarea class="form-control bg-light text-dark"
                      name="file_description" id="fileDescription" rows="3" maxlength="300"></textarea>
                  </div>
                </div>

              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-info text-light">Bagikan Story</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Form Posting Baru -->
        <div class="card mb-4 border border-secondary-subtle shadow-lg">
          <div class="card-body">
            <form method="POST" action="post_create.php" enctype="multipart/form-data">
              <div class="d-flex mb-3">
                <img src="<?= htmlspecialchars($profile['photo'] ?? 'assets/img/default_profile.png') ?>"
                  alt="Foto Profil" width="45" height="45"
                  class="rounded-circle me-2" style="object-fit:cover">

                <!-- Input dan emoji dalam container -->
                <div class="position-relative w-100">
                  <input type="text"
                    id="caption"
                    name="content"
                    class="form-control border-0 pe-5"
                    placeholder="Apa yang kamu pikirkan?"
                    maxlength="500"
                    style="background-color: #f0f2f5; border-radius: 20px; padding: 10px 15px;">

                  <!-- Tombol emoji -->
                  <div class="dropdown position-absolute end-0 top-0 mt-1 mb-2 me-2">
                    <a class="nav-link p-1" type="button" data-bs-toggle="dropdown">
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

        <!-- Daftar postingan -->
        <?php foreach ($posts as $post): ?>
          <div class="post-container timeline-card p-3 position-relative border border-secondary-subtle shadow-lg">
            <div class="d-flex align-items-center mb-1 justify-content-between">
              <div class="d-flex align-items-center">
                <img src="<?= htmlspecialchars($post['photo'] ?? 'assets/img/default_profile.png') ?>" class="post-photo me-2" alt="Foto Profil">
                <div>
                  <div class="fw-semibold d-flex align-items-center gap-2">
                    <?php
                    $user_login_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
                    $post_user_id = (int)$post['user_id'];

                    // Cek apakah sudah berteman
                    $stmt = mysqli_prepare($koneksi, "SELECT 1 FROM friends WHERE
                      (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
                    mysqli_stmt_bind_param($stmt, "iiii", $user_login_id, $post_user_id, $post_user_id, $user_login_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $is_friend = mysqli_fetch_assoc($result) ? true : false;
                    ?>

                    <a href="profile.php?user_id=<?= $post['user_id'] ?>" class="nav-link"><?= htmlspecialchars($post['name']) ?></a>
                    <?php if ($is_friend): ?>
                      <span class="text-secondary">Teman</span>
                    <?php endif; ?>
                  </div>
                  <div class="post-time"><?= date('d M Y H:i', strtotime($post['created_at'])) ?></div>
                </div>
              </div>
              <?php if (isset($_SESSION['user_id']) && $post['user_id'] == $_SESSION['user_id']): ?>
                <div class="dropdown">
                  <button class="btn btn-sm btn-link text-dark"
                    type="button"
                    id="dropdownMenu<?= $post['id'] ?>"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="bi bi-three-dots-vertical fs-5"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenu<?= $post['id'] ?>">
                    <li>
                      <button class="dropdown-item"
                        data-bs-toggle="modal"
                        data-bs-target="#editModal<?= $post['id'] ?>">Ubah</button>
                    </li>
                    <li>
                      <form method="POST" action="post_delete.php" onsubmit="return confirm('Hapus postingan ini?')" style="display:inline;">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <button type="submit" class="dropdown-item text-danger">Hapus</button>
                      </form>
                    </li>
                  </ul>
                </div>
              <?php endif; ?>
            </div>
            <div class="mt-2 mb-2" style="white-space:pre-line;">
              <?= format_post_text($post['content']) ?>
            </div>

            <?php if (!empty($post_files_by_post[$post['id']])): ?>
              <div class="mb-2">
                <?php foreach ($post_files_by_post[$post['id']] as $file): ?>
                  <?php if ($file['file_type'] == 'image'): ?>
                    <img src="<?= htmlspecialchars($file['file_path']) ?>"
                      class="img-fluid rounded mb-2 post-image-thumb"
                      style="max-height:400px;object-fit:cover;"
                      data-bs-toggle="modal"
                      data-bs-target="#imageModal"
                      data-img="<?= htmlspecialchars($file['file_path']) ?>" />
                  <?php elseif ($file['file_type'] == 'video'): ?>
                    <video controls style="max-width:100%;max-height:400px;" class="mb-2">
                      <source src="<?= htmlspecialchars($file['file_path']) ?>">
                      Your browser does not support the video tag.
                    </video>
                  <?php elseif ($file['file_type'] == 'audio'): ?>
                    <audio controls class="mb-2" style="width:100%;">
                      <source src="<?= htmlspecialchars($file['file_path']) ?>">
                      Your browser does not support the audio element.
                    </audio>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- Like postingan -->
            <div class="d-flex gap-3 pt-1 mt-3 pb-0">

              <!-- Jumlah like -->
              <a href="#"
                class="like-btn link-cyan text-decoration-none<?= $post['is_liked'] ? ' liked' : '' ?>"
                data-post-id="<?= $post['id'] ?>">
                <?php if ($post['is_liked']): ?>
                  <i class="bi bi-heart-fill text-danger"></i>
                <?php else: ?>
                  <i class="bi bi-heart"></i>
                <?php endif; ?>
                <span class="like-count"><?= $post['like_count'] ?></span> Like
              </a>
              |

              <!-- Jumlah share -->
              <div class="dropdown">
                <a href="#" class="share-btn link-cyan text-decoration-none" data-bs-toggle="dropdown">
                  <i class="bi bi-arrow-repeat" style="font-size: 15px;"></i> Share
                </a>
                <ul class="dropdown-menu">
                  <li>
                    <a class="dropdown-item copy-link"
                      href="#"
                      data-link="<?= 'https://' . $_SERVER['HTTP_HOST'] . '/post.php?id=' . $post['id'] ?>"
                      data-postid="<?= $post['id'] ?>">
                      <i class="bi bi-link-45deg me-2"></i>Salin Tautan
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item share-track"
                      target="_blank"
                      href="https://wa.me/?text=<?= urlencode('Lihat postingan ini: https://localhost/social-mediaapp/public/post.php?id=' . $post['id']) ?>"
                      data-postid="<?= $post['id'] ?>" data-type="whatsapp">
                      <i class="bi bi-whatsapp me-2 text-success"></i>Bagikan ke WhatsApp
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item share-track"
                      target="_blank"
                      href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://localhost/social-mediaapp/public/post.php?id=' . $post['id']) ?>"
                      data-postid="<?= $post['id'] ?>" data-type="facebook">
                      <i class="bi bi-facebook me-2 text-primary"></i>Bagikan ke Facebook
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item share-track"
                      target="_blank"
                      href="https://twitter.com/intent/tweet?url=<?= urlencode('https://localhost/social-mediaapp/public/post.php?id=' . $post['id']) ?>"
                      data-postid="<?= $post['id'] ?>" data-type="twitter">
                      <i class="bi bi-twitter-x me-2 text-info"></i>Bagikan ke Twitter
                    </a>
                  </li>
                </ul>
              </div>
              |

              <?php
              $stmt = $koneksi->prepare("SELECT COUNT(*) as total FROM post_shares WHERE post_id = ?");
              $stmt->bind_param("i", $post['id']);
              $stmt->execute();
              $result = $stmt->get_result()->fetch_assoc();
              $total_shares = $result['total'];
              $stmt->close();
              ?>

              <p class="text-muted mb-0">
                <span class="share-count"><?= $total_shares ?></span> kali dibagikan
              </p>
              |

              <!-- Jumlah komentar -->
              <?php
              $post_comments = $comments_by_post[$post['id']] ?? [];
              $total_comments = count($post_comments);
              ?>
              <p class="text-muted mb-0">
                <i class="bi bi-chat-left-dots me-2"></i><span class="comment-count"><?= $total_comments ?></span> komentar
              </p>

            </div>
            <hr>

            <!-- Komentar -->
            <div class="mt-2 ms-4 text-dark">
              <div class="comments-list" id="comments-list-<?= $post['id'] ?>">
                <?php
                $comments = $comments_by_post[$post['id']] ?? [];
                $main_comments = [];
                $replies_by_parent = [];
                foreach ($comments as $c) {
                  if (empty($c['parent_id'])) {
                    $main_comments[] = $c;
                  } else {
                    $replies_by_parent[$c['parent_id']][] = $c;
                  }
                }
                $total_comments = count($comments);

                foreach ($main_comments as $i => $comment):
                  $hidden = ($total_comments > 2 && $i < $total_comments - 2) ? 'd-none comment-hidden' : '';
                  $is_comment_owner = isset($_SESSION['user_id']) && $comment['user_id'] == $_SESSION['user_id'];
                  $is_post_owner = isset($_SESSION['user_id']) && $post['user_id'] == $_SESSION['user_id'];
                  // Like data
                  $comment_like_count = $comment['like_count'] ?? 0;
                  $comment_is_liked = !empty($comment['is_liked']);
                ?>
                  <div class="d-flex align-items-start mb-2 <?= $hidden ?>" data-comment-id="<?= $comment['id'] ?>">
                    <img src="<?= htmlspecialchars($comment['photo'] ?? 'assets/img/default_profile.png') ?>"
                      class="rounded-circle me-2" width="32" height="32" style="object-fit:cover;" />
                    <div>
                      <div class="comment bg-light rounded px-3 py-2 position-relative border">
                        <a href="profile.php?user_id=<?= $comment['user_id'] ?>" class="nav-link text-dark">
                          <span class="fw-semibold"><?= htmlspecialchars($comment['name']) ?></span>
                        </a>
                        <span class="text-muted small">· <?= date('d M H:i', strtotime($comment['created_at'])) ?></span>
                        <div class="comment-content"><?= nl2br(htmlspecialchars($comment['content'])) ?></div>
                        <!-- Like & Reply: SELALU MUNCUL -->
                        <a href="#"
                          class="comment-like-btn d-inline-flex align-items-center text-decoration-none<?= $comment_is_liked ? ' text-danger' : ' text-primary' ?>"
                          data-comment-id="<?= $comment['id'] ?>">
                          <i class="bi <?= $comment_is_liked ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                          <span class="comment-like-count ms-1"><?= $comment_like_count ?></span>
                          <span class="ms-1">Like</span>
                        </a>
                        <a href="#" class="reply-link ms-3 text-decoration-none" data-comment-id="<?= $comment['id'] ?>">Balas</a>
                        <!-- Dropdown edit/hapus: Hanya Ubah untuk owner komentar, Hapus untuk owner komentar atau owner posting -->
                        <?php if ($is_comment_owner || $is_post_owner): ?>
                          <div class="dropdown position-absolute top-0 end-0 me-1">
                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                              <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                              <?php if ($is_comment_owner): ?>
                                <li>
                                  <button class="dropdown-item edit-comment-btn" data-comment-id="<?= $comment['id'] ?>">Ubah</button>
                                </li>
                              <?php endif; ?>
                              <?php if ($is_comment_owner || $is_post_owner): ?>
                                <li>
                                  <button class="dropdown-item text-danger delete-comment-btn" data-comment-id="<?= $comment['id'] ?>">Hapus</button>
                                </li>
                              <?php endif; ?>
                            </ul>
                          </div>
                        <?php endif; ?>
                      </div>
                      <!-- Reply List (jika ada) -->
                      <?php if (!empty($replies_by_parent[$comment['id']])): ?>
                        <?php foreach ($replies_by_parent[$comment['id']] as $reply):
                          $is_reply_owner = $reply['user_id'] == $user_id;
                          $is_reply_post_owner = $post['user_id'] == $user_id;
                          $reply_like_count = $reply['like_count'] ?? 0;
                          $reply_is_liked = !empty($reply['is_liked']);
                        ?>
                          <div class="d-flex align-items-start mt-2 ms-4" data-comment-id="<?= $reply['id'] ?>">
                            <img src="<?= htmlspecialchars($reply['photo'] ?? 'assets/img/default_profile.png') ?>"
                              class="rounded-circle me-2" width="28" height="28" style="object-fit:cover;" />
                            <div>
                              <div class="comment bg-light rounded px-3 py-2 position-relative border">
                                <span class="fw-semibold"><?= htmlspecialchars($reply['name']) ?></span>
                                <span class="text-muted small">· <?= date('d M H:i', strtotime($reply['created_at'])) ?></span>
                                <div class="comment-content"><?= nl2br(htmlspecialchars($reply['content'])) ?></div>
                                <!-- Like reply: SELALU MUNCUL -->
                                <a class="comment-like-btn d-inline-flex align-items-center text-decoration-none<?= $reply_is_liked ? ' text-danger' : ' text-primary' ?>"
                                  href="#"
                                  data-comment-id="<?= $reply['id'] ?>">
                                  <i class="bi <?= $reply_is_liked ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                                  <span class="comment-like-count ms-1"><?= $reply_like_count ?></span>
                                  <span class="ms-1">Like</span>
                                </a>
                                <!-- Dropdown edit/hapus: Ubah untuk pemilik reply, Hapus untuk pemilik reply atau pemilik postingan -->
                                <?php if ($is_reply_owner || $is_reply_post_owner): ?>
                                  <div class="dropdown position-absolute top-0 end-0 me-1">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                      <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                      <?php if ($is_reply_owner): ?>
                                        <li>
                                          <button class="dropdown-item edit-comment-btn" data-comment-id="<?= $reply['id'] ?>">Ubah</button>
                                        </li>
                                      <?php endif; ?>
                                      <?php if ($is_reply_owner || $is_reply_post_owner): ?>
                                        <li>
                                          <button class="dropdown-item text-danger delete-comment-btn"
                                            data-comment-id="<?= $reply['id'] ?>">Hapus</button>
                                        </li>
                                      <?php endif; ?>
                                    </ul>
                                  </div>
                                <?php endif; ?>
                              </div>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      <?php endif; ?>
                      <!-- Form reply, muncul saat "Balas" diklik -->
                      <form class="reply-form d-none mt-2 ms-4" data-post-id="<?= $post['id'] ?>" data-parent-id="<?= $comment['id'] ?>">
                        <div class="d-flex align-items-start w-100">
                          <!-- Tombol Emoji -->
                          <button type="button"
                            class="nav-link position-absolute emoji-btn"
                            style="z-index:2; border-radius: 50%; padding: 4px; margin-left: 10px; background: transparent;"
                            title="Emoji">
                            <i class="bi bi-emoji-smile" style="font-size: 20px;"></i>
                          </button>

                          <div class="position-relative w-100">
                            <textarea class="form-control ps-5 pe-2"
                              name="content"
                              placeholder="Tulis balasan..."
                              rows="1"
                              style="background-color: #f0f2f5; border-radius: 20px; padding: 10px 15px; resize:none; max-height:60px;"
                              required></textarea>
                          </div>

                          <button type="submit"
                            class="btn btn-info text-light d-inline-flex align-items-center px-3 ms-2 rounded-circle"
                            style="height: 40px;">
                            <i class="bi bi-send"></i>
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <?php if ($total_comments > 3): ?>
                <a href="#"
                  class="show-comments-link text-decoration-none d-block mb-2"
                  data-post-id="<?= $post['id'] ?>">
                  Lihat semua komentar (<?= $total_comments ?>)
                </a>
                <a href="#"
                  class="hide-comments-link text-decoration-none d-block mb-2 d-none"
                  data-post-id="<?= $post['id'] ?>">
                  Sembunyikan komentar
                </a>
              <?php endif; ?>
              <!-- Form komentar utama -->
              <form class="comment-form d-flex align-items-center gap-2 mt-2 w-100" data-post-id="<?= $post['id'] ?>">
                <!-- Foto Profil -->
                <img src="<?= htmlspecialchars($profile['photo'] ?? 'assets/img/default_profile.png') ?>"
                  class="rounded-circle"
                  width="32" height="32"
                  style="object-fit:cover;">

                <!-- Input komentar + emoji -->
                <div class="position-relative flex-grow-1">
                  <!-- Tombol Emoji -->
                  <button type="button"
                    class="nav-link position-absolute top-50 start-0 translate-middle-y ms-2 emoji-btn"
                    style="z-index:2; border-radius: 50%; padding: 4px;"
                    title="Emoji">
                    <i class="bi bi-emoji-smile" style="font-size: 20px;"></i>
                  </button>

                  <!-- Textarea -->
                  <textarea class="form-control ps-5 pe-2"
                    name="content"
                    placeholder="Tulis komentar..."
                    rows="1"
                    style="background-color: #f0f2f5; border-radius: 20px; padding: 10px 15px; resize: none; max-height: 60px;"
                    required></textarea>
                </div>

                <!-- Tombol Kirim -->
                <button type="submit"
                  class="nav-link text-info d-flex justify-content-center align-items-center"
                  style="width: 40px; height: 40px; border-radius: 50%;">
                  <i class="bi bi-send-fill" style="font-size: 25px;"></i>
                </button>
              </form>
            </div>

            <!-- Modal untuk mengedit postingan -->
            <?php if (isset($_SESSION['user_id']) && $post['user_id'] == $_SESSION['user_id']): ?>
              <!-- Modal Edit Post -->
              <div class="modal fade" id="editModal<?= $post['id'] ?>"
                tabindex="-1" aria-labelledby="editModalLabel<?= $post['id'] ?>" aria-hidden="true">
                <div class="modal-dialog">
                  <form class="modal-content bg-light text-dark" method="POST" action="post_edit.php">
                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                    <div class="modal-header">
                      <h5 class="modal-title" id="editModalLabel<?= $post['id'] ?>">Ubah Postingan</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <textarea name="content"
                        class="form-control bg-light text-dark"
                        rows="4"
                        maxlength="500" required><?= htmlspecialchars($post['content']) ?></textarea>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                      <button type="submit" class="btn btn-info text-light">Simpan Perubahan</button>
                    </div>
                  </form>
                </div>
              </div>
            <?php endif; ?>

          </div>
        <?php endforeach; ?>
        <!-- TODO: Infinite scroll, load more -->
      </div>

      <!-- Kolom kanan -->
      <?php
      // Ambil ID user yang sedang login
      $currentUserId = $_SESSION['user_id'] ?? null;

      // Ambil user yang belum berteman dan bukan diri sendiri
      $stmt = mysqli_prepare($koneksi, "
        SELECT users.id, profiles.name, profiles.last_name, profiles.photo,
              fr.status AS request_status
        FROM users
        JOIN profiles ON users.id = profiles.user_id
        LEFT JOIN friend_requests fr
          ON fr.from_user = ? AND fr.to_user = users.id AND fr.status = 'pending'
        WHERE users.id != ?
          AND users.id NOT IN (
              SELECT friend_id FROM friends WHERE user_id = ? AND status = 'accepted'
              UNION
              SELECT user_id FROM friends WHERE friend_id = ? AND status = 'accepted'
          )
        ORDER BY RAND() DESC LIMIT 5
      ");
      $stmt->bind_param("iiii", $currentUserId, $currentUserId, $currentUserId, $currentUserId);
      $stmt->execute();
      $result = $stmt->get_result();
      ?>

      <div class="col-lg-3 mb-4 d-none d-lg-block">
        <div class="card mb-3 border border-secondary-subtle shadow-lg">
          <div class="card-header fw-bold">Rekomendasi Teman</div>
          <ul class="list-group list-group-flush">
            <?php if ($result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center">
                    <img src="<?= htmlspecialchars($row['photo'] ?: 'assets/img/default_profile.png') ?>"
                      class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">
                    <a href="profile.php?user_id=<?= $row['id'] ?>"
                      class="nav-link text-dark"><strong><?= htmlspecialchars($row['name'] . ' ' . $row['last_name']) ?></strong></a>
                  </div>

                  <?php if ($row['request_status'] === 'pending'): ?>
                    <form method="POST" action="cancel_friend_request.php" class="m-0">
                      <input type="hidden" name="to_user" value="<?= $row['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-danger">Batal</button>
                    </form>
                  <?php else: ?>
                    <form method="POST" action="add_friend_request.php" class="m-0">
                      <input type="hidden" name="to_user" value="<?= $row['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-info text-light">+</button>
                    </form>
                  <?php endif; ?>
                </li>
              <?php endwhile; ?>
            <?php else: ?>
              <li class="list-group-item text-center text-muted">
                Tidak ada rekomendasi teman untuk Anda!
              </li>
            <?php endif; ?>
          </ul>
          <div class="mt-2 mb-2 text-center d-grid ms-5 me-5">
            <a href="friends.php" class="btn btn-sm btn-info text-light text-wrapp">Daftar Teman Anda</a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Modal Edit Komentar -->
  <div class="modal fade" id="editCommentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content" id="editCommentForm">
        <input type="hidden" name="comment_id" id="edit-comment-id">
        <div class="modal-header">
          <h5 class="modal-title">Edit Komentar</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <textarea name="content" id="edit-comment-content" class="form-control" rows="3" required></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-info text-light">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Viewer untuk Story -->
  <div class="modal fade" id="storyViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-dark text-light">
        <div class="modal-body p-0 position-relative">

          <!-- Progress Bar Wrapper -->
          <div id="storyProgressWrapper" class="d-flex px-3 pt-2 gap-1 pb-2">
            <div class="story-progress-bar">
              <div class="progress-fill"></div>
            </div>
          </div>

          <!-- TAMBAHKAN TOMBOL INI -->
          <div id="storyOptionsButtonContainer" class="position-absolute top-0 end-0 p-2">
            <!-- Tombol akan diisi oleh JavaScript -->
          </div>

          <!-- Area klik untuk skip story -->
          <div id="storyLeftClick" class="story-skip-area start"></div>
          <div id="storyRightClick" class="story-skip-area end"></div>

          <!-- Story Content -->
          <div id="storyContentViewer" class="text-center px-3 pb-3"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal hapus story -->
  <div class="modal fade" id="deleteStoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="modal-title">Hapus Cerita</h5>
          <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Yakin ingin menghapus cerita ini?
        </div>
        <div class="modal-footer border-0">
          <form method="post" action="story_delete.php">
            <input type="hidden" name="story_id" id="storyIdInput">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger">Hapus</button>
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

  <!-- ✅ DITAMBAHKAN: Bootstrap Toast -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="copyToast" class="toast align-items-center text-white bg-success border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body">
          Tautan berhasil disalin!
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>
  </div>

  <script src="assets/js/bootstrap.bundle.min.js"></script>

  <script>
    document.querySelectorAll('.emoji').forEach(el => {
      el.addEventListener('click', function(e) {
        e.preventDefault(); // agar tidak trigger close
        e.stopPropagation(); // mencegah dropdown tertutup

        const input = document.getElementById('caption');
        input.value += this.textContent;
        input.focus();
      });
    });

    // Hindari dropdown tertutup saat klik emoji
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
      menu.addEventListener('click', function(e) {
        e.stopPropagation(); // cegah klik dalam menu menutup dropdown
      });
    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.emoji-btn').forEach(function(button) {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();

          const emojiList = [
            '😀', '😁', '😂', '🤣', '😍', '😘', '😎', '😋', '😊', '😉', '😆', '😅',
            '😢', '😭', '😠', '😡', '😱', '❤️', '💔', '👍', '👎', '👏', '🙏', '💪'
          ];

          let dropdown = document.getElementById('emoji-picker');

          if (!dropdown) {
            dropdown = document.createElement('div');
            dropdown.id = 'emoji-picker';
            dropdown.style.position = 'absolute';
            dropdown.style.background = '#fff';
            dropdown.style.border = '1px solid #ddd';
            dropdown.style.padding = '6px';
            dropdown.style.borderRadius = '6px';
            dropdown.style.boxShadow = '0 0 10px rgba(0,0,0,0.1)';
            dropdown.style.fontSize = '20px';
            dropdown.style.display = 'flex';
            dropdown.style.flexWrap = 'wrap';
            dropdown.style.gap = '6px';
            dropdown.style.zIndex = 1000;
            document.body.appendChild(dropdown);
          }

          // Clear previous emojis
          dropdown.innerHTML = '';

          emojiList.forEach(emoji => {
            const span = document.createElement('span');
            span.textContent = emoji;
            span.style.cursor = 'pointer';
            dropdown.appendChild(span);

            span.addEventListener('click', function(event) {
              event.stopPropagation(); // Mencegah close saat klik emoji

              const textarea = button.closest('form').querySelector('textarea');
              const start = textarea.selectionStart;
              const end = textarea.selectionEnd;
              const text = textarea.value;
              textarea.value = text.slice(0, start) + emoji + text.slice(end);
              textarea.focus();
              textarea.selectionStart = textarea.selectionEnd = start + emoji.length;

              // Jangan tutup dropdown, biarkan terbuka sampai user klik di luar
            });
          });

          const rect = button.getBoundingClientRect();
          dropdown.style.top = (rect.bottom + window.scrollY + 5) + 'px';
          dropdown.style.left = (rect.left + window.scrollX) + 'px';
          dropdown.style.display = 'flex';
        });
      });

      // Tutup emoji picker saat klik di luar area picker
      document.addEventListener('click', function(e) {
        const picker = document.getElementById('emoji-picker');
        if (picker && !picker.contains(e.target) && !e.target.classList.contains('emoji-btn')) {
          picker.style.display = 'none';
        }
      });
    });
  </script>

  <script>
    window.STORY_DATA = {
      userId: <?= json_encode($_SESSION['user_id']) ?>,
      groupedStories: <?= json_encode($grouped_stories) ?>
    };
  </script>

  <script src="assets/js/story_viewer.js"></script>

  <script src="assets/js/like_post.js"></script>
  <script src="assets/js/comment.js"></script>
  <script src="assets/js/story_update.js"></script>
  <script src="assets/js/story_modal.js"></script>
  <script src="assets/js/file_preview.js"></script>
  <script src="assets/js/story_input.js"></script>
  <script src="assets/js/notification.js"></script>
  <script src="assets/js/share_btn.js"></script>

</body>

</html>