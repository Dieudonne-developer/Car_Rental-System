<?php
session_start();
require_once "../config/db.php";

// Redirect if not seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
  header("Location: ../login.php");
  exit();
}

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $title = trim($_POST["title"]);
  $description = trim($_POST["description"]);
  $price = $_POST["price"];
  $image = $_FILES["image"];

  // Validate inputs
  if (empty($title) || empty($description) || empty($price)) {
    $errors[] = "All fields are required.";
  }

  // Image validation
  if ($image['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
      $errors[] = "Invalid image format. Use jpg, jpeg, png or gif.";
    }

    $image_name = time() . "_" . basename($image['name']);
    $upload_path = "../uploads/" . $image_name;

    if (empty($errors)) {
      move_uploaded_file($image['tmp_name'], $upload_path);

      // Insert into DB
      $stmt = $conn->prepare("INSERT INTO cars (user_id, title, description, image, price_per_day) VALUES (?, ?, ?, ?, ?)");
      $stmt->execute([$_SESSION['user_id'], $title, $description, $image_name, $price]);

      $success = "Car uploaded successfully and awaiting admin approval.";
    }
  } else {
    $errors[] = "Please upload a car image.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Upload Car - Seller Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-5">
    <h2>Upload Your Car for Rent</h2>

    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form action="upload_car.php" method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Car Title</label>
        <input type="text" name="title" class="form-control" required
          value="<?= isset($title) ? htmlspecialchars($title) : '' ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Car Description</label>
        <textarea name="description" class="form-control" rows="4"
          required><?= isset($description) ? htmlspecialchars($description) : '' ?></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Price Per Day (USD)</label>
        <input type="number" step="0.01" name="price" class="form-control" required
          value="<?= isset($price) ? htmlspecialchars($price) : '' ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Upload Image</label>
        <input type="file" name="image" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-primary">Upload Car</button>
      <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </form>
  </div>
</body>

</html>