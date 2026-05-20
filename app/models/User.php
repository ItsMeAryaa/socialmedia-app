<?php
class User
{
  public static function findByEmail($mysqli, $email) {
    $stmt = mysqli_prepare($mysqli, "SELECT * FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
  }

  public static function create($mysqli, $name, $last_name, $email, $password, $security_question, $security_answer) {
    // Insert ke tabel users
    $stmt = mysqli_prepare($mysqli, "INSERT INTO users (email, password, security_question, security_answer, created_at) VALUES (?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "ssss", $email, $password, $security_question, $security_answer);
    mysqli_stmt_execute($stmt);

    $user_id = mysqli_insert_id($mysqli);

    // Insert ke tabel profiles (tambahkan last_name di sini)
    $stmt2 = mysqli_prepare($mysqli, "INSERT INTO profiles (user_id, name, last_name) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt2, "iss", $user_id, $name, $last_name);
    mysqli_stmt_execute($stmt2);

    return $user_id;
  }
}
