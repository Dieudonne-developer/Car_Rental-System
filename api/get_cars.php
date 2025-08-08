<?php
require_once '../config/db.php';

$brand = $_GET['brand'] ?? '';
$type = $_GET['type'] ?? '';
$price = $_GET['price'] ?? '';

$sql = "SELECT * FROM cars WHERE approved = 1";
$params = [];

if ($brand !== '') {
  $sql .= " AND brand = ?";
  $params[] = $brand;
}

if ($type !== '') {
  $sql .= " AND type = ?";
  $params[] = $type;
}

if ($price !== '') {
  $sql .= " AND price_per_day <= ?";
  $params[] = $price;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($cars);
