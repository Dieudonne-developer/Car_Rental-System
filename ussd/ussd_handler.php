<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

header('Content-Type: text/plain');

// Get USSD parameters
$sessionId = $_POST['sessionId'] ?? '';
$serviceCode = $_POST['serviceCode'] ?? '';
$phoneNumber = validate_phone($_POST['phoneNumber'] ?? '');
$text = $_POST['text'] ?? '';

// Validate phone number
if (!preg_match('/^254[17]\d{8}$/', $phoneNumber)) {
  echo "END Invalid phone number format";
  exit;
}

// Initialize response
$response = "";
$steps = $text ? explode('*', $text) : [];
$currentStep = count($steps);
$db = require '../config/db.php';

try {
  if ($currentStep === 0) {
    // Initial menu
    $response = "CON Welcome to Car Rental USSD\n";
    $response .= "1. Rent a Car\n";
    $response .= "2. My Rentals\n";
    $response .= "3. Help";

  } elseif ($steps[0] === '1' && $currentStep === 1) {
    // Step 1: List available cars
    $stmt = $db->query("SELECT id, title, price_per_day FROM cars 
                           WHERE status = 'approved' ORDER BY created_at DESC LIMIT 5");
    $cars = $stmt->fetchAll();

    $response = "CON Available Cars:\n";
    foreach ($cars as $car) {
      $response .= "{$car['id']}. {$car['title']} - {$car['price_per_day']} USD\n";
    }
    $response .= "\nEnter Car Number:";

  } elseif ($steps[0] === '1' && $currentStep === 2) {
    // Step 2: Validate car and request days
    $carId = (int) $steps[1];
    $stmt = $db->prepare("SELECT id, title, price_per_day FROM cars 
                            WHERE id = ? AND status = 'approved'");
    $stmt->execute([$carId]);

    if ($car = $stmt->fetch()) {
      $_SESSION['ussd_car'] = $car;
      $response = "CON {$car['title']}\nPrice: {$car['price_per_day']} USD/day\n";
      $response .= "Enter rental days (1-30):";
    } else {
      $response = "END Invalid car selection";
    }

  } elseif ($steps[0] === '1' && $currentStep === 3) {
    // Step 3: Confirm payment
    $days = (int) $steps[2];
    if ($days < 1 || $days > 30) {
      $response = "END Days must be 1-30";
    } else {
      $car = $_SESSION['ussd_car'];
      $total = $car['price_per_day'] * $days;

      $response = "CON Confirm Rental:\n";
      $response .= "Car: {$car['title']}\n";
      $response .= "Days: $days\n";
      $response .= "Total: $total USD\n";
      $response .= "1. Pay via M-Pesa\n";
      $response .= "2. Cancel";
    }

  } elseif ($steps[0] === '1' && $currentStep === 4 && $steps[3] === '1') {
    // Step 4: Process payment
    $car = $_SESSION['ussd_car'];
    $days = (int) $steps[2];
    $total = $car['price_per_day'] * $days;

    // Initiate M-Pesa payment (mock implementation)
    $paymentId = "MPESA" . time();
    $paymentSuccess = initiateMpesaPayment($phoneNumber, $total);

    if ($paymentSuccess) {
      // Record rental
      $stmt = $db->prepare("INSERT INTO rentals 
                (car_id, user_id, start_date, end_date, total_cost, status, payment_status) 
                VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? DAY), ?, 'confirmed', 'paid')");
      $stmt->execute([
        $car['id'],
        getUserIdByPhone($phoneNumber),
        $days,
        $total
      ]);

      $response = "END Payment successful! Rental confirmed. Details sent via SMS.";
    } else {
      $response = "END Payment failed. Please try again.";
    }

  } else {
    $response = "END Invalid selection";
  }

} catch (PDOException $e) {
  error_log("USSD Error: " . $e->getMessage());
  $response = "END System error. Please try again later.";
}

echo $response;

// Helper functions
function initiateMpesaPayment(string $phone, float $amount): bool
{
  // In production, integrate with Africa's Talking API
  // This is a mock implementation
  return true;
}

function getUserIdByPhone(string $phone): ?int
{
  global $db;
  $stmt = $db->prepare("SELECT id FROM users WHERE phone = ?");
  $stmt->execute([$phone]);
  return $stmt->fetchColumn() ?: null;
}
