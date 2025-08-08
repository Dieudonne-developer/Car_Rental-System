<?php
require_once '../config/db.php';

if (!isset($_GET['brand'])) {
  echo json_encode([]);
  exit;
}

$brand = $_GET['brand'];

$stmt = $pdo->prepare("SELECT DISTINCT model FROM cars WHERE brand = ? ORDER BY model ASC");
$stmt->execute([$brand]);
$models = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($models);
