<?php
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

// Optional filters
$filter_brand = $_GET['brand'] ?? '';
$filter_model = $_GET['model'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_start_date = $_GET['start_date'] ?? '';
$filter_end_date = $_GET['end_date'] ?? '';

// Get distinct brands and models
$brands = $pdo->query("SELECT DISTINCT brand FROM cars ORDER BY brand ASC")->fetchAll(PDO::FETCH_COLUMN);

if ($filter_brand !== '') {
  $stmt = $pdo->prepare("SELECT DISTINCT model FROM cars WHERE brand = ? ORDER BY model ASC");
  $stmt->execute([$filter_brand]);
  $models = $stmt->fetchAll(PDO::FETCH_COLUMN);
} else {
  $models = $pdo->query("SELECT DISTINCT model FROM cars ORDER BY model ASC")->fetchAll(PDO::FETCH_COLUMN);
}

// Build query
$where = ["c.approved = 1"];
$params = [];

if ($filter_brand !== '') {
  $where[] = "c.brand = ?";
  $params[] = $filter_brand;
}
if ($filter_model !== '') {
  $where[] = "c.model = ?";
  $params[] = $filter_model;
}
if ($filter_status !== '' && in_array($filter_status, ['available', 'rented'])) {
  $where[] = "c.status = ?";
  $params[] = $filter_status;
}
if ($filter_start_date !== '') {
  $where[] = "r.start_date >= ?";
  $params[] = $filter_start_date;
}
if ($filter_end_date !== '') {
  $where[] = "r.end_date <= ?";
  $params[] = $filter_end_date;
}

$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Join rentals to show booking status
$sql = "
  SELECT c.id, c.brand, c.model, c.status, c.image_path, r.start_date, r.end_date
  FROM cars c
  LEFT JOIN rentals r ON c.id = r.car_id
  $where_sql
  ORDER BY c.id DESC
  LIMIT 50
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container mt-4">
  <h1 class="mb-4">Browse Available Cars</h1>

  <!-- Filter Form -->
  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-3">
      <select name="brand" id="brand-select" class="form-select">
        <option value="">All Brands</option>
        <?php foreach ($brands as $brand): ?>
          <option value="<?= htmlspecialchars($brand) ?>" <?= $filter_brand === $brand ? 'selected' : '' ?>>
            <?= htmlspecialchars($brand) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <select name="model" id="model-select" class="form-select">
        <option value="">All Models</option>
        <?php foreach ($models as $model): ?>
          <option value="<?= htmlspecialchars($model) ?>" <?= $filter_model === $model ? 'selected' : '' ?>>
            <?= htmlspecialchars($model) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="status" class="form-select">
        <option value="">All Statuses</option>
        <option value="available" <?= $filter_status === 'available' ? 'selected' : '' ?>>Available</option>
        <option value="rented" <?= $filter_status === 'rented' ? 'selected' : '' ?>>Rented</option>
      </select>
    </div>
    <div class="col-md-2">
      <input type="date" name="start_date" class="form-control" placeholder="Start Date"
        value="<?= htmlspecialchars($filter_start_date) ?>">
    </div>
    <div class="col-md-2">
      <input type="date" name="end_date" class="form-control" placeholder="End Date"
        value="<?= htmlspecialchars($filter_end_date) ?>">
    </div>
    <div class="col-md-12 d-grid mt-2">
      <button type="submit" class="btn btn-primary">Filter</button>
    </div>
  </form>

  <!-- Car Listings -->
  <div class="row">
    <?php if (count($cars) > 0): ?>
      <?php foreach ($cars as $car): ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <?php if (!empty($car['image_path'])): ?>
              <img src="<?= htmlspecialchars($car['image_path']) ?>" class="card-img-top" alt="Car image">
            <?php else: ?>
              <div class="card-img-top bg-secondary text-white text-center p-5">No Image</div>
            <?php endif; ?>
            <div class="card-body">
              <h5 class="card-title"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h5>
              <p>Status:
                <?= $car['status'] === 'available'
                  ? '<span class="badge bg-success">Available</span>'
                  : '<span class="badge bg-warning text-dark">Rented</span>' ?>
              </p>
              <?php if ($car['status'] === 'available'): ?>
                <a href="rent_car.php?car_id=<?= $car['id'] ?>" class="btn btn-primary">Rent This Car</a>
              <?php else: ?>
                <button class="btn btn-secondary" disabled>Not Available</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12 text-center">
        <div class="alert alert-info">No cars match the selected filters.</div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  document.getElementById('brand-select').addEventListener('change', function () {
    const brand = this.value;
    const modelSelect = document.getElementById('model-select');
    modelSelect.innerHTML = '<option value="">All Models</option>';

    if (brand === '') return;

    fetch('admin/get_models.php?brand=' + encodeURIComponent(brand))
      .then(res => res.json())
      .then(models => {
        models.forEach(model => {
          const option = document.createElement('option');
          option.value = model;
          option.textContent = model;
          modelSelect.appendChild(option);
        });
      })
      .catch(() => alert('Failed to load models.'));
  });
</script>

<?php include 'includes/footer.php'; ?>