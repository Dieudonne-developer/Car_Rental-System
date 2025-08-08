<?php
session_start();
require_once __DIR__ . '/../config/db.php';  // Your PDO connection here

// Secure image upload function (adjust as needed)
function secure_image_upload($input_name, $upload_dir)
{
  if (!isset($_FILES[$input_name]) || $_FILES[$input_name]['error'] === UPLOAD_ERR_NO_FILE) {
    return ['error' => 'No file uploaded.'];
  }

  $file = $_FILES[$input_name];
  if ($file['error'] !== UPLOAD_ERR_OK) {
    return ['error' => 'Upload error code: ' . $file['error']];
  }

  $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
  if (!in_array(mime_content_type($file['tmp_name']), $allowed_types)) {
    return ['error' => 'Invalid file type. Only JPG, PNG, GIF allowed.'];
  }

  if ($file['size'] > 2 * 1024 * 1024) { // 2MB max
    return ['error' => 'File size exceeds 2MB limit.'];
  }

  $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
  $new_filename = uniqid('car_', true) . '.' . $ext;
  $destination = $upload_dir . DIRECTORY_SEPARATOR . $new_filename;

  if (!move_uploaded_file($file['tmp_name'], $destination)) {
    return ['error' => 'Failed to move uploaded file.'];
  }

  return ['filename' => $new_filename];
}

// Secure image delete function
function delete_image_file($filename, $uploadDir)
{
  if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
    return ['error' => 'Invalid filename.'];
  }

  $filePath = realpath($uploadDir . DIRECTORY_SEPARATOR . $filename);
  $realUploadDir = realpath($uploadDir);

  if ($filePath === false || strpos($filePath, $realUploadDir) !== 0) {
    return ['error' => 'File path is not allowed.'];
  }

  if (!is_file($filePath)) {
    return ['error' => 'File does not exist.'];
  }

  $defaultImages = ['default_car.jpg', 'no_image.png'];
  if (in_array($filename, $defaultImages)) {
    return ['error' => 'Cannot delete default images.'];
  }

  if (!unlink($filePath)) {
    return ['error' => 'Failed to delete file.'];
  }

  return ['success' => true];
}

// Check if user is logged in and has seller role (adjust this according to your auth logic)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
  header('Location: ../login.php');
  exit;
}

$uploadDir = __DIR__ . '/../uploads/cars';

if (!isset($_GET['id'])) {
  die('Car ID is required');
}

$car_id = intval($_GET['id']);

// Fetch car details for pre-fill
$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND seller_id = ?");
$stmt->execute([$car_id, $_SESSION['user_id']]);
$car = $stmt->fetch();

if (!$car) {
  die('Car not found or access denied.');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $brand = trim($_POST['brand']);
  $model = trim($_POST['model']);
  $price_per_day = floatval($_POST['price_per_day']);

  if (empty($brand) || empty($model) || $price_per_day <= 0) {
    $errors[] = 'Please fill all fields correctly.';
  }

  $oldImage = $car['image'];
  $newImageFilename = $oldImage;

  // Handle image upload if a new file is provided
  if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploadResult = secure_image_upload('image', $uploadDir);
    if (isset($uploadResult['error'])) {
      $errors[] = $uploadResult['error'];
    } else {
      $newImageFilename = $uploadResult['filename'];

      // Delete old image safely (ignore error if any)
      $deleteResult = delete_image_file($oldImage, $uploadDir);
      // Optional: log deletion errors if needed
    }
  }

  if (empty($errors)) {
    $updateStmt = $pdo->prepare("UPDATE cars SET brand = ?, model = ?, price_per_day = ?, image = ? WHERE id = ? AND seller_id = ?");
    $updated = $updateStmt->execute([$brand, $model, $price_per_day, $newImageFilename, $car_id, $_SESSION['user_id']]);

    if ($updated) {
      $_SESSION['flash_success'] = 'Car updated successfully.';
      header('Location: dashboard.php');
      exit;
    } else {
      $errors[] = 'Failed to update car.';
    }
  }
}

?>

<?php include '../includes/header.php'; ?>

<div class="container mt-4">
  <h2>Edit Car</h2>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="row g-3 needs-validation" novalidate>
    <div class="col-md-6">
      <label for="brand" class="form-label">Brand</label>
      <input type="text" name="brand" id="brand" class="form-control" required
        value="<?= htmlspecialchars($car['brand']) ?>">
    </div>

    <div class="col-md-6">
      <label for="model" class="form-label">Model</label>
      <input type="text" name="model" id="model" class="form-control" required
        value="<?= htmlspecialchars($car['model']) ?>">
    </div>

    <div class="col-md-6">
      <label for="price_per_day" class="form-label">Price per Day (USD)</label>
      <input type="number" step="0.01" min="0" name="price_per_day" id="price_per_day" class="form-control" required
        value="<?= htmlspecialchars($car['price_per_day']) ?>">
    </div>

    <div class="col-md-6">
      <label class="form-label">Current Image</label><br>
      <img src="../uploads/cars/<?= htmlspecialchars($car['image']) ?>" alt="Car Image" style="max-width: 200px;">
    </div>

    <div class="col-md-6">
      <label for="image" class="form-label">Upload New Image (optional)</label>
      <input type="file" name="image" id="image" class="form-control" accept="image/*">
      <small class="form-text text-muted">Leave empty to keep current image.</small>
    </div>

    <div class="col-12">
      <button class="btn btn-primary" type="submit">Update Car</button>
      <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>

<?php include '../includes/footer.php'; ?>