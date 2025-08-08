<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// User must be logged in
if (!isset($_SESSION['user_id'])) {
  redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch user's rentals with car info
$sql = "SELECT r.*, c.brand, c.model, c.image, c.price_per_day
        FROM rentals r
        JOIN cars c ON r.car_id = c.id
        WHERE r.user_id = ?
        ORDER BY r.start_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$rentals = $stmt->fetchAll();

include 'includes/header.php';
?>

<h2 class="mb-4">My Rental History</h2>

<!-- Flash messages -->
<?php if (!empty($_SESSION['success'])): ?>
  <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
  <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (count($rentals) === 0): ?>
  <div class="alert alert-info">You have no rentals yet.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>Car</th>
          <th>Rental Period</th>
          <th>Status</th>
          <th>Price/Day</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rentals as $r): ?>
          <tr>
            <td>
              <img src="uploads/<?= htmlspecialchars($r['image']) ?>" alt="<?= htmlspecialchars($r['brand']) ?>" width="80"
                class="me-2 rounded">
              <?= htmlspecialchars($r['brand']) ?>     <?= htmlspecialchars($r['model']) ?>
            </td>
            <td>
              <?= htmlspecialchars($r['start_date']) ?> to <?= htmlspecialchars($r['end_date']) ?>
            </td>
            <td>
              <?php
              $status = htmlspecialchars(ucfirst($r['status']));
              // Mark as completed if end_date passed but still active
              if ($r['status'] === 'active' && strtotime($r['end_date']) < time()) {
                $status = 'Completed';
              }
              echo $status;
              ?>
            </td>
            <td>$<?= number_format($r['price_per_day'], 2) ?></td>
            <td>
              <?php if ($r['status'] === 'active' && strtotime($r['start_date']) > time()): ?>
                <form method="post" action="cancel_rental.php"
                  onsubmit="return confirm('Are you sure you want to cancel this booking?');" class="d-inline">
                  <input type="hidden" name="rental_id" value="<?= $r['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                </form>
              <?php else: ?>
                <span class="text-muted">N/A</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>