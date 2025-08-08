<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  redirect('../login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $carIds = $_POST['car_ids'] ?? [];

  if (empty($carIds)) {
    $_SESSION['errors'][] = "No cars selected for approval.";
    redirect('dashboard.php');
  }

  // Prepare placeholders for query
  $placeholders = implode(',', array_fill(0, count($carIds), '?'));

  // Fetch cars info and their sellers' emails/usernames
  $stmt = $pdo->prepare("SELECT c.id, c.brand, c.model, c.seller_id, u.email, u.username 
                           FROM cars c 
                           JOIN users u ON c.seller_id = u.id 
                           WHERE c.id IN ($placeholders)");
  $stmt->execute($carIds);
  $cars = $stmt->fetchAll();

  if (!$cars) {
    $_SESSION['errors'][] = "Selected cars not found.";
    redirect('dashboard.php');
  }

  // Begin transaction
  $pdo->beginTransaction();
  try {
    // Bulk approve cars
    $updateStmt = $pdo->prepare("UPDATE cars SET approved = 1 WHERE id = ?");
    foreach ($carIds as $id) {
      $updateStmt->execute([$id]);
    }
    $pdo->commit();

    // Group cars by seller to optimize emails
    $sellers = [];
    foreach ($cars as $car) {
      $seller_id = $car['seller_id'];
      if (!isset($sellers[$seller_id])) {
        $sellers[$seller_id] = [
          'email' => $car['email'],
          'username' => $car['username'],
          'cars' => [],
        ];
      }
      $sellers[$seller_id]['cars'][] = $car['brand'] . ' ' . $car['model'];
    }

    $mailErrors = [];

    // Send one email per seller with all their approved cars
    foreach ($sellers as $seller) {
      $mail = new PHPMailer(true);
      try {
        // SMTP configuration (adjust as needed)
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@example.com';
        $mail->Password = 'your_password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('no-reply@yourdomain.com', 'Car Rental Admin');
        $mail->addAddress($seller['email'], $seller['username']);

        // Build list of cars HTML
        $carListHtml = '<ul>';
        foreach ($seller['cars'] as $carName) {
          $carListHtml .= "<li>" . htmlspecialchars($carName) . "</li>";
        }
        $carListHtml .= '</ul>';

        $mail->isHTML(true);
        $mail->Subject = 'Your cars have been approved!';
        $mail->Body = "
                    <p>Dear {$seller['username']},</p>
                    <p>The following car(s) you listed have been approved and are now visible to customers:</p>
                    $carListHtml
                    <p>Thank you for using our platform!</p>
                    <p>Best regards,<br>Car Rental Team</p>
                ";

        $mail->send();
      } catch (Exception $e) {
        $mailErrors[] = "Failed to send email to {$seller['email']}: {$mail->ErrorInfo}";
      }
    }

    // Flash success and/or mail errors
    $_SESSION['success'] = "Selected cars have been approved successfully.";
    if (!empty($mailErrors)) {
      $_SESSION['errors'] = $mailErrors;
    }

  } catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['errors'][] = "Failed to approve cars: " . $e->getMessage();
  }

  redirect('dashboard.php');
} else {
  // Not a POST request
  redirect('dashboard.php');
}
