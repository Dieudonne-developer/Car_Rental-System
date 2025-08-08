<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['user_id'])) {
  redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$rental_id = $_POST['rental_id'] ?? null;

if (!$rental_id) {
  $_SESSION['error'] = "Invalid rental selection.";
  redirect('rental_history.php');
}

// Check if rental exists, belongs to user, and is active
$sql = "SELECT * FROM rentals WHERE id = ? AND user_id = ? AND status = 'active'";
$stmt = $pdo->prepare($sql);
$stmt->execute([$rental_id, $user_id]);
$rental = $stmt->fetch();

if (!$rental) {
  $_SESSION['error'] = "Rental not found or cannot be cancelled.";
  redirect('rental_history.php');
}

// Check if rental start date is in the future
if (strtotime($rental['start_date']) <= time()) {
  $_SESSION['error'] = "You can only cancel bookings before the rental start date.";
  redirect('rental_history.php');
}

// Update rental status to cancelled
$update = $pdo->prepare("UPDATE rentals SET status = 'cancelled' WHERE id = ?");
if ($update->execute([$rental_id])) {
  $_SESSION['success'] = "Booking cancelled successfully.";
} else {
  $_SESSION['error'] = "Failed to cancel booking. Please try again.";
}

redirect('rental_history.php');
