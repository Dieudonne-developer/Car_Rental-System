<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['car_ids']) && is_array($_POST['car_ids']) && isset($_POST['action'])) {
  $car_ids = $_POST['car_ids'];
  $action = $_POST['action'];

  if ($action === 'approve') {
    $approved = 1;
  } elseif ($action === 'reject') {
    $approved = 0;
  } else {
    $_SESSION['error'] = "Invalid action.";
    header("Location: admin_dashboard.php");
    exit;
  }

  $placeholders = rtrim(str_repeat('?,', count($car_ids)), ',');
  $sql = "UPDATE cars SET approved = ? WHERE id IN ($placeholders)";
  $stmt = $pdo->prepare($sql);

  // Merge approved status param and car IDs for execute
  $params = array_merge([$approved], $car_ids);
  $stmt->execute($params);

  $action_text = $approved ? "approved" : "rejected";
  $_SESSION['success'] = count($car_ids) . " car(s) successfully $action_text.";
} else {
  $_SESSION['error'] = "No cars selected or invalid request.";
}

header("Location: admin_dashboard.php");
exit;
