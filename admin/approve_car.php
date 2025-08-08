<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['car_id'])) {
  $car_id = intval($_POST['car_id']);

  $stmt = $pdo->prepare("UPDATE cars SET approved = 1 WHERE id = ?");
  if ($stmt->execute([$car_id])) {
    $_SESSION['msg'] = "Car approved successfully.";
  } else {
    $_SESSION['msg'] = "Failed to approve car.";
  }
}

header("Location: dashboard.php");
exit;
