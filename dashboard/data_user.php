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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../public/assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../public/assets/css/styleDashboard.css">
  <link rel="stylesheet" href="../public/assets/icon/bootstrap-icons/font/bootstrap-icons.css">
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
        <a href="dashboard.php" class="nav-link text-light">
          <i class="bi bi-speedometer2 me-2" style="font-size: 20px;"></i> Dashboard
        </a>
        <hr class="garis">
        <a href="data_postingan.php" class="nav-link text-light">
          <i class="bi bi-file-earmark-text me-2" style="font-size: 20px;"></i> Data Postingan
        </a>
        <hr class="garis">
        <a href="data_komentar.php" class="nav-link text-light">
          <i class="bi bi-chat-left-text me-2" style="font-size: 20px;"></i> Data Komentar
        </a>
        <hr class="garis">
        <a href="data_user.php" class="nav-link active text-light">
          <i class="bi bi-person me-2" style="font-size: 20px;"></i> Data Pengguna
        </a>
        <hr class="garis">
        <a href="../public/index.php" class="nav-link text-light">
          <i class="bi bi-globe me-2" style="font-size: 20px;"></i> Lihat Web
        </a>
        <hr class="garis">
        <li class="nav-link text-light">
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
        <i class="bi bi-person me-2 position-relative" style="font-size: 29px"></i> DATA PENGGUNA
        <hr>
      </h3>

      <?php if (isset($_GET['pesan'])): ?>
        <?php if ($_GET['pesan'] === 'berhasilhapus'): ?>
          <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>User berhasil dihapus.</div>
          </div>
        <?php elseif ($_GET['pesan'] === 'gagalhapus'): ?>
          <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>User gagal dihapus.</div>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <form role="search" method="post" class="d-flex mb-4 mt-2">
        <input type="search" name="cari" placeholder="Cari nama atau email..." class="form-control me-1">
        <button type="submit" class="btn btn-primary">Cari</button>
      </form>

      <?php
      $cari = $_POST['cari'] ?? '';

      $sql = "SELECT u.id, u.email, u.created_at, p.name
              FROM users u
              LEFT JOIN profiles p ON p.user_id = u.id
              WHERE 1";

      if (!empty($cari)) {
        $sql .= " AND (p.name LIKE '%$cari%' OR u.email LIKE '%$cari%')";
      }

      $sql .= " ORDER BY u.created_at DESC LIMIT $posisi, $batas";
      $result = mysqli_query($koneksi, $sql);

      $sql_count = "SELECT COUNT(*) as total
                    FROM users u
                    LEFT JOIN profiles p ON p.user_id = u.id
                    WHERE 1";

      if (!empty($cari)) {
        $sql_count .= " AND (p.name LIKE '%$cari%' OR u.email LIKE '%$cari%')";
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
            <th>Nama</th>
            <th>Email</th>
            <th>Tanggal Dibuat</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody class="table-light">
          <?php
          $no = $posisi + 1;
          while ($row = mysqli_fetch_assoc($result)):
            $user_id = $row['id'];
          ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= htmlspecialchars($row['name'] ?? '-'); ?></td>
            <td><?= htmlspecialchars($row['email']); ?></td>
            <td><?= date('d M Y, H:i', strtotime($row['created_at'])); ?></td>
            <td>
              <a href="hapus_user.php?id=<?= $user_id ?>"
                onclick="return confirm('Yakin ingin menghapus user ini?')" class="btn btn-sm btn-danger">
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
            echo "<li class='page-item'><span class='page-link'>&laquo;</span></li>";
          }

          for ($i = 1; $i <= $jmlhHal; $i++) {
            $active = ($i == $halaman) ? 'active' : '';
            echo "
              <li class='page-item $active'>
                <a href='$_SERVER[PHP_SELF]?halaman=$i' class='page-link'>$i</a>
              </li>
            ";
          }

          if ($halaman < $jmlhHal) {
            $next = $halaman + 1;
            echo "
              <li class='page-item'>
                <a href='$_SERVER[PHP_SELF]?halaman=$next' class='page-link' aria-label='Next'>
                  <span aria-hidden='true'>&raquo;</span>
                </a>
              </li>
            ";
          } else {
            echo "<li class='page-item'><span class='page-link'>&raquo;</span></li>";
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
          <span>&copy; <?= date('Y') ?> | Create and Developed By ItsMe<span class="text-info fw-bold">Aryaa</span></span>
        </div>
      </div>
    </div>
  </footer>

  <script src="../public/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
