<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
  header('Location: ../login.php');
  exit;
}

$seller_id = $_SESSION['user_id'];
$car_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($car_id <= 0) {
  $_SESSION['flash_error'] = "Invalid car ID.";
  header('Location: dashboard.php');
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM cars WHERE id = ? AND seller_id = ?");
$stmt->execute([$car_id, $seller_id]);
$car = $stmt->fetch();

if (!$car) {
  $_SESSION['flash_error'] = "Car not found or you don't have permission.";
  header('Location: dashboard.php');
  exit;
}

$delStmt = $pdo->prepare("DELETE FROM cars WHERE id = ? AND seller_id = ?");
if ($delStmt->execute([$car_id, $seller_id])) {
  $_SESSION['flash_success'] = "Car deleted successfully.";
} else {
  $_SESSION['flash_error'] = "Failed to delete the car.";
}

header('Location: dashboard.php');
exit;
