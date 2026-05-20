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
        <i class="bi bi-chat-left-text me-2 position-relative" style="font-size: 29px"></i> DATA KOMENTAR
        <hr>
      </h3>

      <?php if (isset($_GET['pesan'])): ?>
        <?php if ($_GET['pesan'] === 'berhasilhapus'): ?>
          <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>Komentar berhasil dihapus.</div>
          </div>
        <?php elseif ($_GET['pesan'] === 'gagalhapus'): ?>
          <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>Komentar gagal dihapus.</div>
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

      $sql = "SELECT c.id, c.content, pr.name, c.parent_id,
        (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.id) AS like_count,
        CASE
          WHEN c.parent_id IS NULL
          THEN (SELECT COUNT(*) FROM comments cb WHERE cb.parent_id = c.id)
          ELSE 0
        END AS reply_count
      FROM comments c
      LEFT JOIN profiles pr ON pr.user_id = c.user_id
      WHERE 1 = 1
      ";

      if (!empty($cari)) {
        $sql .= " AND (pr.name LIKE '%$cari%' OR c.content LIKE '%$cari%')";
      }

      $sql .= " ORDER BY c.created_at DESC LIMIT $posisi, $batas";
      $result = mysqli_query($koneksi, $sql);

      // Hitung total komentar (semua, termasuk reply)
      $sql_count = "SELECT COUNT(*) as total
      FROM comments c
      LEFT JOIN profiles pr ON pr.user_id = c.user_id
      WHERE 1 = 1
      ";

      if (!empty($cari)) {
        $sql_count .= " AND (pr.name LIKE '%$cari%' OR c.content LIKE '%$cari%')";
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
            <th>Komentar</th>
            <th>Jumlah Like</th>
            <th>Jumlah Balasan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody class="table-light">
          <?php
          $no = $posisi + 1;
          while ($row = mysqli_fetch_assoc($result)):
            $id_komentar = $row['id'];
            $is_reply = !is_null($row['parent_id']);
          ?>
            <tr>
              <td><?= $no++; ?></td>
              <td><?= htmlspecialchars($row['name']); ?></td>
              <td>
                <?= nl2br(htmlspecialchars($row['content'])); ?>
              </td>
              <td><?= $row['like_count']; ?></td>
              <td><?= $is_reply ? '-' : $row['reply_count']; ?></td>
              <td>
                <a href="hapus_komentar.php?id=<?= $id_komentar ?>"
                  onclick="return confirm('Yakin ingin menghapus komentar ini?')" class="btn btn-sm btn-danger">
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
