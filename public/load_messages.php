<?php
session_start();
require_once '../config/database.php'; // pastikan variabel $koneksi tersedia

$from = $_SESSION['user_id'] ?? 0;
$to = $_GET['to_user'] ?? 0;

if (!$from || !$to) {
  echo json_encode([]);
  exit;
}

$sql = "SELECT id, from_user, message, is_deleted
        FROM messages
        WHERE (from_user = ? AND to_user = ?)
            OR (from_user = ? AND to_user = ?)
        ORDER BY created_at ASC";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'iiii', $from, $to, $to, $from);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
  $messages[] = $row;
}

header('Content-Type: application/json');
echo json_encode($messages);
