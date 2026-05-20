<?php
require_once __DIR__ . '/../models/User.php';

class AuthController
{
  private $koneksi;

  public function __construct($koneksi) {
    $this->koneksi = $koneksi;
  }

  public function login($email, $password) {
    $user = User::findByEmail($this->koneksi, $email);
    if ($user && password_verify($password, $user['password'])) {
      return $user; // Berhasil login
    }
    return false; // Gagal login
  }

  public function register($name, $last_name, $email, $password, $security_question, $security_answer) {
    if (User::findByEmail($this->koneksi, $email)) {
      return "Email sudah terdaftar!";
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $user_id = User::create($this->koneksi, $name, $last_name, $email, $hashed, $security_question, $security_answer);
    return $user_id;
  }

  public function isAdmin($email, $password) {
    $stmt = mysqli_prepare($this->koneksi, "SELECT * FROM admins WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);

    if ($admin && password_verify($password, $admin['password'])) {
      return $admin;
    }
    return false;
  }
}
