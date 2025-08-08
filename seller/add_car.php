<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Only allow sellers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
  header('Location: ../login.php');
  exit;
}

$seller_id = $_SESSION['user_id'];
$errors = [];
$success = '';

$uploadDir = __DIR__ . '/../uploads';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $brand = trim($_POST['brand'] ?? '');
  $model = trim($_POST['model'] ?? '');
  $year = trim($_POST['year'] ?? '');
  $price_per_day = trim($_POST['price_per_day'] ?? '');
  $status = trim($_POST['status'] ?? 'available');

  // Validate inputs
  if ($brand === '' || $model === '' || $year === '' || $price_per_day === '') {
    $errors[] = "Please fill in all required fields.";
  } elseif (!is_numeric($year) || (int) $year < 1900 || (int) $year > (int) date('Y') + 1) {
    $errors[] = "Please enter a valid year.";
  } elseif (!is_numeric($price_per_day) || (float) $price_per_day <= 0) {
    $errors[] = "Please enter a valid price per day.";
  }

  // Validate image upload
  if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors[] = "Please upload a car image.";
  } else {
    $file = $_FILES['image'];
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExts)) {
      $errors[] = "Invalid image type. Allowed: jpg, jpeg, png, gif.";
    } elseif ($file['size'] > 2 * 1024 * 1024) { // 2MB
      $errors[] = "Image size must be less than 2MB.";
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
      $errors[] = "Error uploading image.";
    }
  }

  if (empty($errors)) {
    // Handle image upload
    $newImageName = uniqid('car_', true) . '.' . $ext;
    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $newImageName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
      $errors[] = "Failed to save uploaded image.";
    } else {
      // Insert into DB including image filename
      $stmt = $pdo->prepare("INSERT INTO cars (seller_id, brand, model, year, price_per_day, status, image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
      $result = $stmt->execute([$seller_id, $brand, $model, (int) $year, (float) $price_per_day, $status, $newImageName]);

      if ($result) {
        $success = "Car added successfully!";
        // Clear form values
        $brand = $model = $year = $price_per_day = '';
      } else {
        $errors[] = "Failed to add car. Please try again.";
        // Remove uploaded image if DB insert fails
        if (file_exists($targetPath)) {
          unlink($targetPath);
        }
      }
    }
  }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-4">
  <h2>Add New Car</h2>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
  <?php elseif ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="row g-3 needs-validation" novalidate>
    <div class="col-md-6">
      <label for="brand" class="form-label">Brand *</label>
      <input type="text" name="brand" id="brand" class="form-control" required
        value="<?= htmlspecialchars($brand ?? '') ?>">
    </div>

    <div class="col-md-6">
      <label for="model" class="form-label">Model *</label>
      <input type="text" name="model" id="model" class="form-control" required
        value="<?= htmlspecialchars($model ?? '') ?>">
    </div>

    <div class="col-md-4">
      <label for="year" class="form-label">Year *</label>
      <input type="number" name="year" id="year" class="form-control" min="1900" max="<?= date('Y') + 1 ?>" required
        value="<?= htmlspecialchars($year ?? '') ?>">
    </div>

    <div class="col-md-4">
      <label for="price_per_day" class="form-label">Price Per Day (USD) *</label>
      <input type="number" name="price_per_day" id="price_per_day" class="form-control" min="0" step="0.01" required
        value="<?= htmlspecialchars($price_per_day ?? '') ?>">
    </div>

    <div class="col-md-4">
      <label for="status" class="form-label">Status *</label>
      <select name="status" id="status" class="form-select" required>
        <option value="available" <?= (isset($status) && $status === 'available') ? 'selected' : '' ?>>Available</option>
        <option value="unavailable" <?= (isset($status) && $status === 'unavailable') ? 'selected' : '' ?>>Unavailable
        </option>
      </select>
    </div>

    <div class="col-md-6">
      <label for="image" class="form-label">Car Image (jpg, jpeg, png, gif, max 2MB) *</label>
      <input type="file" name="image" id="image" class="form-control" accept=".jpg,.jpeg,.png,.gif" required>
    </div>

    <div class="col-12">
      <button type="submit" class="btn btn-primary">Add Car</button>
      <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>