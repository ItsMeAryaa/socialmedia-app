<?php
require_once '../config/database.php';

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
$ids = $data['ids'] ?? [];

$response = [];

if (!empty($ids)) {
  $placeholders = implode(',', array_fill(0, count($ids), '?'));
  $types = str_repeat('i', count($ids));
  $now = new DateTime('now', new DateTimeZone('Asia/Singapore'));
  $params = $ids;

  $sql = "SELECT id, last_active FROM users WHERE id IN ($placeholders)";
  $stmt = $koneksi->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
    $lastActive = new DateTime($row['last_active'], new DateTimeZone('Asia/Singapore'));
    $interval = $now->getTimestamp() - $lastActive->getTimestamp();

    $isOnline = $interval <= 5; // 1 menit terakhir
    $response[$row['id']] = $isOnline ? 'online' : 'offline';
  }
}

header('Content-Type: application/json');
echo json_encode($response);
?>
