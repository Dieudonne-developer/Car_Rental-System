<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
  redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// Get prefill values from GET parameters (sanitize)
$prefill_car_id = isset($_GET['car_id']) ? intval($_GET['car_id']) : '';
$prefill_start_date = $_GET['start_date'] ?? '';
$prefill_end_date = $_GET['end_date'] ?? '';

// Fetch car details if car_id is provided
$car = null;
if ($prefill_car_id) {
  $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
  $stmt->execute([$prefill_car_id]);
  $car = $stmt->fetch();
  if (!$car) {
    $_SESSION['error'] = "Car not found.";
    redirect('index.php');
  }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $car_id = intval($_POST['car_id'] ?? 0);
  $start_date = $_POST['start_date'] ?? '';
  $end_date = $_POST['end_date'] ?? '';

  // Validate inputs
  if (!$car_id) {
    $errors[] = "Invalid car selection.";
  } else {
    // Fetch car details for validation
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch();
    if (!$car) {
      $errors[] = "Selected car does not exist.";
    }
  }

  if (!$start_date || !$end_date) {
    $errors[] = "Please provide both start and end dates.";
  } else {
    // Validate date formats
    $start_timestamp = strtotime($start_date);
    $end_timestamp = strtotime($end_date);

    if (!$start_timestamp || !$end_timestamp) {
      $errors[] = "Invalid date format.";
    } else if ($start_timestamp < strtotime(date('Y-m-d'))) {
      $errors[] = "Start date cannot be in the past.";
    } else if ($end_timestamp <= $start_timestamp) {
      $errors[] = "End date must be after start date.";
    }
  }

  // Prevent double booking
  if (empty($errors)) {
    $sql = "SELECT COUNT(*) FROM rentals WHERE car_id = ? AND status = 'active' AND
                (
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date >= ? AND end_date <= ?)
                )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $car_id,
      $start_date,
      $start_date,
      $end_date,
      $end_date,
      $start_date,
      $end_date
    ]);

    $count = $stmt->fetchColumn();

    if ($count > 0) {
      $errors[] = "This car is already booked during the selected dates.";
    }
  }

  // If no errors, insert rental
  if (empty($errors)) {
    $insert = $pdo->prepare("INSERT INTO rentals (user_id, car_id, start_date, end_date, status) VALUES (?, ?, ?, ?, 'active')");
    $result = $insert->execute([$user_id, $car_id, $start_date, $end_date]);

    if ($result) {
      $_SESSION['success'] = "Car rented successfully!";
      redirect('rental_history.php');
    } else {
      $errors[] = "Failed to book the car. Please try again.";
    }
  }
} else {
  // Not submitted yet, use prefill values for form defaults
  $car_id = $prefill_car_id;
  $start_date = $prefill_start_date;
  $end_date = $prefill_end_date;
}

// Include header (make sure your header does NOT already include conflicting jQuery or Bootstrap JS)
include 'includes/header.php';
?>

<!-- Bootstrap Datepicker CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css"
  rel="stylesheet">
<!-- Bootstrap Icons CSS (optional for calendar icon) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<h2 class="mb-4">Rent Car</h2>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if (!empty($_SESSION['success'])): ?>
  <div class="alert alert-success">
    <?= htmlspecialchars($_SESSION['success']) ?>
  </div>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!$car): ?>
  <div class="alert alert-warning">No car selected. Please <a href="index.php">choose a car</a> to rent.</div>
<?php else: ?>

  <form method="post" action="rent_car.php" id="rentCarForm" autocomplete="off">
    <input type="hidden" name="car_id" value="<?= htmlspecialchars($car['id']) ?>">

    <div class="mb-3">
      <label for="car_name" class="form-label">Car</label>
      <input type="text" id="car_name" class="form-control"
        value="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>" disabled>
    </div>

    <div class="mb-3">
      <label for="start_date" class="form-label">Start Date</label>
      <div class="input-group date" id="startDatePicker">
        <input type="text" id="start_date" name="start_date" class="form-control" required
          value="<?= htmlspecialchars($start_date) ?>">
        <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
      </div>
    </div>

    <div class="mb-3">
      <label for="end_date" class="form-label">End Date</label>
      <div class="input-group date" id="endDatePicker">
        <input type="text" id="end_date" name="end_date" class="form-control" required
          value="<?= htmlspecialchars($end_date) ?>">
        <span class="input-group-text"><i class="bi bi-calendar-date"></i></span>
      </div>
    </div>

    <button type="submit" class="btn btn-primary">Confirm Booking</button>
  </form>

  <!-- jQuery (required for bootstrap-datepicker) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap Bundle JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Bootstrap Datepicker JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

  <script>
    $(function () {
      var today = new Date();
      today.setHours(0, 0, 0, 0);

      $('#startDatePicker').datepicker({
        format: 'yyyy-mm-dd',
        startDate: today,
        autoclose: true,
        todayHighlight: true
      }).on('changeDate', function (e) {
        var startDate = e.date;
        if (startDate) {
          var minEndDate = new Date(startDate.getTime());
          minEndDate.setDate(minEndDate.getDate() + 1);
          $('#endDatePicker').datepicker('setStartDate', minEndDate);
          // If current end date is before new min, clear it
          var currentEnd = $('#end_date').datepicker('getDate');
          if (!currentEnd || currentEnd <= startDate) {
            $('#end_date').datepicker('setDate', null);
          }
        }
      });

      $('#endDatePicker').datepicker({
        format: 'yyyy-mm-dd',
        startDate: new Date(today.getTime() + 24 * 60 * 60 * 1000),
        autoclose: true,
        todayHighlight: true
      });

      $('#rentCarForm').on('submit', function (e) {
        var startDateStr = $('#start_date').val();
        var endDateStr = $('#end_date').val();

        if (!startDateStr || !endDateStr) {
          alert('Please select both start and end dates.');
          e.preventDefault();
          return;
        }

        var startDate = new Date(startDateStr);
        var endDate = new Date(endDateStr);

        if (startDate < today) {
          alert('Start date cannot be in the past.');
          e.preventDefault();
          return;
        }

        if (endDate <= startDate) {
          alert('End date must be after start date.');
          e.preventDefault();
          return;
        }
      });
    });
  </script>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>