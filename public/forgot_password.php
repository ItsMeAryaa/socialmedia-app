<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Password</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/icon/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="website icon" href="assets/icon/web-icon.png">
  <style>
    body {
      background-color: #f8f9fa;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .form-container {
      background: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.05);
      width: 100%;
      max-width: 400px;
    }
  </style>
</head>
<body>

<div class="form-container shadow-sm">
  <h4 class="mb-3 text-center">Lupa Password</h4>
  <form action="verify_security_question.php" method="post">
    <div class="mb-3">
      <label>Email</label>
      <input type="email" class="form-control" name="email" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Lanjutkan</button>
  </form>
  <div class="mt-3 text-center">
    <a href="login.php" class="text-decoration-none">Kembali ke Login</a>
  </div>
</div>

</body>
</html>
