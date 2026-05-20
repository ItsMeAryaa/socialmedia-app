<?php
session_start();
require_once '../config/database.php';

/* hanya boleh masuk kalau sudah login sebagai admin */
if (!isset($_SESSION['admin_id'])) {
  header('Location: login.php');   // balik ke form login admin
  exit;
}

/* (opsional) verifikasi ulang di DB kalau mau ekstra aman */
$adminId = $_SESSION['admin_id'];
$stmt    = mysqli_prepare($koneksi, "SELECT id FROM admins WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $adminId);
mysqli_stmt_execute($stmt);
$valid   = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$valid) {          // admin dihapus dari DB, paksa logout
  session_destroy();
  header('Location: login.php');
  exit;
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

  <!-- Dashboard start -->
  <div class="row bg-dark">
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
        <i class="bi bi-speedometer2 me-2" style="font-size: 2rem;"></i> DASHBOARD<hr>
      </h3>
      <div class="row dash-row text-light">
      <?php
      include_once '../config/database.php';

      // Hitung jumlah postingan
      $query_postingan = mysqli_query($koneksi, "SELECT COUNT(id) as jumlah_postingan FROM posts");
      $jumlah_postingan = $query_postingan ? mysqli_fetch_assoc($query_postingan)['jumlah_postingan'] : 0;

      // Hitung jumlah komentar
      $query_komentar = mysqli_query($koneksi, "SELECT COUNT(id) as jumlah_komentar FROM comments");
      $jumlah_komentar = $query_komentar ? mysqli_fetch_assoc($query_komentar)['jumlah_komentar'] : 0;

      // Hitung jumlah user
      $query_pengguna = mysqli_query($koneksi, "SELECT COUNT(id) as jumlah_pengguna FROM users");
      $jumlah_pengguna = $query_pengguna ? mysqli_fetch_assoc($query_pengguna)['jumlah_pengguna'] : 0;
      ?>
        <!-- Card Postingan -->
        <div class="card kartu bg-warning me-3 mt-2" style="width: 18rem;">
          <div class="card-body">
            <div class="card-body-icon">
              <i class="bi bi-file-earmark-text-fill"></i>
            </div>
            <h5 class="card-title text-light">JUMLAH POSTINGAN</h5>
            <div class="display-4 text-light"><?php echo number_format($jumlah_postingan); ?></div>
            <a href="data_postingan.php" class="text-decoration-none">
              <p class="card-text text-light">
                Lihat Detail <i class="bi bi-chevron-double-right" style="font-size: 12px;"></i>
              </p>
            </a>
          </div>
        </div>

        <!-- Card Komentar -->
        <div class="card kartu bg-success me-3 mt-2" style="width: 18rem;">
          <div class="card-body">
            <div class="card-body-icon">
              <i class="bi bi-chat-left-text-fill"></i>
            </div>
            <h5 class="card-title text-light">JUMLAH KOMENTAR</h5>
            <div class="display-4 text-light"><?php echo number_format($jumlah_komentar); ?></div>
            <a href="data_komentar.php" class="text-decoration-none">
              <p class="card-text text-light">
                Lihat Detail <i class="bi bi-chevron-double-right" style="font-size: 12px;"></i>
              </p>
            </a>
          </div>
        </div>

        <!-- Card Pengguna -->
        <div class="card kartu bg-info me-3 mt-2" style="width: 18rem;">
          <div class="card-body">
            <div class="card-body-icon">
              <i class="bi bi-person-fill"></i>
            </div>
            <h5 class="card-title text-light">JUMLAH PENGGUNA</h5>
            <div class="display-4 text-light"><?php echo number_format($jumlah_pengguna); ?></div>
            <a href="data_user.php" class="text-decoration-none">
              <p class="card-text text-light">
                Lihat Detail <i class="bi bi-chevron-double-right" style="font-size: 12px;"></i>
              </p>
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>
  <!-- Dashboard end -->

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

</body>
</html>
