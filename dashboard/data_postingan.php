<?php
session_start();
error_reporting(0);
require '../config/database.php';

// Header anti-cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 🚫 Jika user biasa mencoba masuk dashboard
if (isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
  header('Location: ../public/index.php');
  exit;
}

// ✅ Jika belum login sama sekali
if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php'); // ke login admin
  exit;
}

// ✅ Verifikasi admin dari DB (opsional tapi disarankan)
$adminId = $_SESSION['admin_id'];
$stmt = mysqli_prepare($koneksi, "SELECT id FROM admins WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $adminId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

if (!$admin) {
  session_destroy(); // akun admin sudah tidak ada
  header('Location: login.php');
  exit;
}

$batas = 5;
$halaman = $_GET['halaman'];

if (empty($halaman)) {
  $posisi = 0;
  $halaman = 1;
} else {
  $posisi = ($halaman - 1) * $batas;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Required meta tag start -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS Start -->
  <link rel="stylesheet" href="../public/assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../public/assets/css/styleDashboard.css">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="../public/assets/icon/bootstrap-icons/font/bootstrap-icons.css">

  <!-- Website Icon Start -->
  <link rel="website icon" href="../public/assets/icon/web-icon.png">

  <title>Dashboard Admin - Temanku</title>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a href="#" class="navbar-brand ms-4">
      <font face="Fira Code"><strong>DASHBOARD ADMIN</strong></font>
    </a>
  </nav>

  <div class="row">
    <div class="col-md-2 bg-dark pt-4">
      <nav class="nav flex-column ml-3 mb-5">
        <hr class="garis">
        <a href="dashboard.php" class="nav-link active text-light" aria-current="page">
          <i class="bi bi-speedometer2 me-2" style="font-size: 20px;"></i> Dashboard
        </a>
        <hr class="garis">
        <a href="data_postingan.php" class="nav-link text-light me-2">
          <i class="bi bi-file-earmark-text me-2" style="font-size: 20px;"></i> Data Postingan
        </a>
        <hr class="garis">
        <a href="data_komentar.php" class="nav-link text-light me-2">
          <i class="bi bi-chat-left-text me-2" style="font-size: 20px;"></i> Data Komentar
        </a>
        <hr class="garis">
        <a href="data_user.php" class="nav-link text-light me-2">
          <i class="bi bi-person me-2" style="font-size: 20px;"></i> Data Pengguna
        </a>
        <hr class="garis">
        <a href="../public/index.php" class="nav-link text-light me-2">
          <i class="bi bi-globe me-2" style="font-size: 20px;"></i> Lihat Web
        </a>
        <hr class="garis">
        <li class="nav-link text-light me-2">
          <form action="logout.php" method="POST">
            <button class="dropdown-item d-flex align-items-center" type="submit">
              <i class="bi bi-box-arrow-right me-2" style="font-size: 20px;"></i> Keluar
            </button>
          </form>
        </li>
        <hr class="garis">
      </nav>
    </div>
    <div class="col-md-10 p-5 pt-3 bg-secondary">
      <h3 class="main">
        <i class="bi bi-file-earmark-text me-2 position-relative" style="font-size: 29px"></i> DATA POSTINGAN
        <hr>
      </h3>

      <?php if (isset($_GET['pesan'])): ?>
        <?php if ($_GET['pesan'] === 'berhasilhapus'): ?>
          <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>Postingan berhasil dihapus.</div>
          </div>
        <?php elseif ($_GET['pesan'] === 'gagalhapus'): ?>
          <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>Postingan gagal dihapus.</div>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <form role="search" method="post" class="d-flex mb-4 mt-2">
        <input type="search" name="cari" placeholder="Cari disini..." class="form-control me-1">
        <button type="submit" class="btn btn-primary">Cari</button>
      </form>

      <?php
      include '../config/database.php';

      $cari = $_POST['cari'] ?? '';

      // Query utama ambil data postingan + user + file + count like & komentar
      $sql = "SELECT p.id AS post_id, p.content, pr.name, pf.file_path, pf.file_type,
              (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS like_count,
              (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count
              FROM posts p
              LEFT JOIN profiles pr ON pr.user_id = p.user_id
              LEFT JOIN post_files pf ON pf.post_id = p.id";

      if (!empty($cari)) {
        $sql .= " WHERE pr.name LIKE '%$cari%' OR p.content LIKE '%$cari%'";
      }

      $sql .= " ORDER BY p.created_at DESC LIMIT $posisi, $batas";
      $result = mysqli_query($koneksi, $sql);

      // Hitung total data untuk pagination
      $sql_count = "SELECT COUNT(*) as total FROM posts p
                    LEFT JOIN profiles pr ON pr.user_id = p.user_id";
      if (!empty($cari)) {
        $sql_count .= " WHERE pr.name LIKE '%$cari%' OR p.content LIKE '%$cari%'";
      }
      $res_count = mysqli_query($koneksi, $sql_count);
      $row_count = mysqli_fetch_assoc($res_count);
      $jmlhData = $row_count['total'];
      $jmlhHal = ceil($jmlhData / $batas);
      ?>
      <table class="table table-bordered table-hover">
        <thead class="table-success">
          <tr>
            <th>No</th>
            <th>Nama Pengguna</th>
            <th>Caption</th>
            <th>File</th>
            <th>Like</th>
            <th>Komentar</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody class="table-light">
          <?php
          $no = $posisi + 1;
          while ($row = mysqli_fetch_assoc($result)):
            $id_post = $row['post_id'];
          ?>
            <tr>
              <td><?= $no++; ?></td>
              <td><?= htmlspecialchars($row['name']); ?></td>
              <td><?= nl2br(htmlspecialchars($row['content'])); ?></td>
              <td>
                <?php if ($row['file_type'] == 'image'): ?>
                  <img src="../public/<?= htmlspecialchars($row['file_path']); ?>" class="img-fluid rounded" style="max-width: 100px;">
                <?php elseif ($row['file_type'] == 'video'): ?>
                  <video controls style="max-width:50%;max-height:400px;" class="mb-2">
                    <source src="../public/<?= htmlspecialchars($row['file_path']) ?>">
                    Your browser does not support the video tag.
                  </video>
                <?php elseif ($row['file_type'] == 'audio'): ?>
                  <audio controls class="mb-2" style="width:50%;">
                    <source src="../public/<?= htmlspecialchars($row['file_path']) ?>">
                    Your browser does not support the audio element.
                  </audio>
                <?php else: ?>
                  Tidak ada
                <?php endif; ?>
              </td>
              <td><?= $row['like_count']; ?></td>
              <td><?= $row['comment_count']; ?></td>
              <td>
                <a href="hapus_postingan.php?id=<?= $id_post ?>"
                  onclick="return confirm('Yakin ingin menghapus postingan ini?')" class="btn btn-sm btn-danger">
                  <i class="bi bi-trash"></i> Hapus
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>

      <!-- Pagination -->
      <nav aria-label="page navigation example">
        <ul class="pagination justify-content-center mt-5">
        <?php
        // Link ke halaman sebelumnya/prev
        if ($halaman > 1) {
          $prev = $halaman - 1;
          echo "
            <li class='page-item'>
              <a href='$_SERVER[PHP_SELF]?halaman=$prev' class='page-link' aria-label='Previous'>
                <span aria-hidden='true'>&laquo;</span>
              </a>
            </li>
          ";
        } else {
          echo "
            <li class='page-item'>
              <a href='' class='page-link'>
                <span aria-hidden='true'>&laquo;</span>
              </a>
            </li>
          ";
        }

        // Link ke halaman 1, 2, dst.
        for ($i = 1; $i <= $jmlhHal; $i++) {
          if ($i != $halaman) {
            echo "
              <li class='page-item'>
                <a href='$_SERVER[PHP_SELF]?halaman=$i' class='page-link'>
                  $i
                </a>
              </li>
            ";
          } else {
            echo "
              <li class='page-item active' >
                <a href='' class='page-link'>
                  $i
                </a>
              </li>
            ";
          }
        }

        // Link ke halaman selanjutnya/next
        if ($halaman < $jmlhHal) {
          $next = $halaman + 1;
          echo "
          <li class='page-item'>
            <a href='$_SERVER[PHP_SELF]?halaman=$next' class='page-link' aria-label='next'>
              <span aria-hidden='true'>&raquo;</span>
            </a>
          </li>
          ";
        } else {
          echo "
          <li class='page-item'>
            <a href='' class='page-link'>
              <span aria-hidden='true'>&raquo;</span>
            </a>
          </li>
          ";
        }
        ?>
        </ul>
      </nav>
    </div>
  </div>

  <footer class="bg-dark p-5 text-light">
    <div class="container">
      <div class="row">
        <div class="col">
          <span>&copy; <?php echo date('Y'); ?> | Create and Developed By ItsMe<span class="text-info fw-bold">Aryaa</span></span>
        </div>
      </div>
    </div>
  </footer>


  <!-- Script Start -->
  <script src="../public/assets/js/bootstrap.bundle.min.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      document.getElementById("loading").style.display = "none";
    });
    // diupdate: preview foto
    const photoInput = document.getElementById('photo');
    const preview = document.getElementById('preview');
    photoInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
          preview.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
      } else {
        preview.src = "#";
        preview.classList.add('hidden');
      }
    });
  </script>

</body>
</html>
