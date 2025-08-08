<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
  header('Location: ../login.php');
  exit;
}

$seller_id = $_SESSION['user_id'];

// Fetch cars uploaded by this seller
$stmt = $pdo->prepare("SELECT * FROM cars WHERE seller_id = ? ORDER BY created_at DESC");
$stmt->execute([$seller_id]);
$cars = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container mt-4">
  <h2>Seller Dashboard</h2>
  <p>Welcome, <?= htmlspecialchars($_SESSION['username']); ?>!</p>

  <a href="add_car.php" class="btn btn-success mb-3">Add New Car</a>

  <?php if (count($cars) === 0): ?>
    <div class="alert alert-info">You have not uploaded any cars yet.</div>
  <?php else: ?>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Car Brand</th>
          <th>Model</th>
          <th>Year</th>
          <th>Price Per Day</th>
          <th>Status</th>
          <th>Uploaded On</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cars as $car): ?>
          <tr>
            <td><?= htmlspecialchars($car['brand']) ?></td>
            <td><?= htmlspecialchars($car['model']) ?></td>
            <td><?= htmlspecialchars($car['year']) ?></td>
            <td>$<?= number_format($car['price_per_day'], 2) ?></td>
            <td><?= htmlspecialchars($car['status']) ?></td>
            <td><?= date('Y-m-d', strtotime($car['created_at'])) ?></td>
            <td>
              <a href="edit_car.php?id=<?= $car['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
              <a href="delete_car.php?id=<?= $car['id'] ?>" class="btn btn-sm btn-danger"
                onclick="return confirm('Are you sure you want to delete this car?');">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>