<?php
session_start();
require_once 'config/db.php';
require_once 'includes/functions.php';

// Redirect if not logged in (optional, remove if index is public)
if (!is_logged_in()) {
  redirect('login.php');
}

// Filters
$filter_brand = $_GET['brand'] ?? '';
$filter_model = $_GET['model'] ?? '';

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
list($offset, $limit) = get_pagination($page, $per_page);

// Fetch brands for filter dropdown
$brands = $pdo->query("SELECT DISTINCT brand FROM cars ORDER BY brand ASC")->fetchAll(PDO::FETCH_COLUMN);

// Fetch models based on brand filter
if ($filter_brand !== '') {
  $stmt = $pdo->prepare("SELECT DISTINCT model FROM cars WHERE brand = ? ORDER BY model ASC");
  $stmt->execute([$filter_brand]);
  $models = $stmt->fetchAll(PDO::FETCH_COLUMN);
} else {
  $models = $pdo->query("SELECT DISTINCT model FROM cars ORDER BY model ASC")->fetchAll(PDO::FETCH_COLUMN);
}

// Build WHERE clause for filtering
$where = [];
$params = [];

if ($filter_brand !== '') {
  $where[] = "brand = ?";
  $params[] = $filter_brand;
}

if ($filter_model !== '') {
  $where[] = "model = ?";
  $params[] = $filter_model;
}

$where_sql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Get total cars count (for pagination)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM cars $where_sql");
$stmt->execute($params);
$total_cars = $stmt->fetchColumn();
$total_pages = ceil($total_cars / $per_page);

// Get cars with limit & offset for current page
$sql = "SELECT * FROM cars $where_sql ORDER BY id DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();

// Include header
include 'includes/header.php';
?>

<div class="container mt-4">
  <h1 class="mb-4">Available Cars</h1>

  <!-- Filter form -->
  <form method="GET" class="row g-3 mb-4" id="filter-form">
    <div class="col-md-4">
      <select name="brand" id="brand-select" class="form-select">
        <option value="">All Brands</option>
        <?php foreach ($brands as $brand_option): ?>
          <option value="<?= e($brand_option) ?>" <?= $filter_brand === $brand_option ? 'selected' : '' ?>>
            <?= e($brand_option) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <select name="model" id="model-select" class="form-select">
        <option value="">All Models</option>
        <?php foreach ($models as $model_option): ?>
          <option value="<?= e($model_option) ?>" <?= $filter_model === $model_option ? 'selected' : '' ?>>
            <?= e($model_option) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4 d-grid">
      <button type="submit" class="btn btn-primary">Filter</button>
    </div>
  </form>

  <!-- Cars list -->
  <div class="row">
    <?php if (count($cars) > 0): ?>
      <?php foreach ($cars as $car): ?>
        <div class="col-md-4 mb-4">
          <div class="card">
            <?php if (!empty($car['image_path'])): ?>
              <img src="<?= e($car['image_path']) ?>" class="card-img-top"
                alt="<?= e($car['brand'] . ' ' . $car['model']) ?>">
            <?php else: ?>
              <img src="assets/no-image.png" class="card-img-top" alt="No Image">
            <?php endif; ?>
            <div class="card-body">
              <h5 class="card-title"><?= e($car['brand'] . ' ' . $car['model']) ?></h5>
              <p class="card-text">
                Price per day: $<?= e(number_format($car['price_per_day'], 2)) ?>
              </p>
              <a href="car_details.php?id=<?= e($car['id']) ?>" class="btn btn-primary">View Details</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No cars found matching your criteria.</p>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <?php if ($total_pages > 1): ?>
    <nav>
      <ul class="pagination justify-content-center">
        <?php if ($page > 1): ?>
          <li class="page-item">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
          </li>
        <?php endif; ?>

        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
          <li class="page-item <?= $p == $page ? 'active' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
          <li class="page-item">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  <?php endif; ?>
</div>

<script>
  // Dynamic model dropdown loading based on selected brand (optional AJAX)
  document.getElementById('brand-select').addEventListener('change', function () {
    const brand = this.value;
    const modelSelect = document.getElementById('model-select');
    modelSelect.innerHTML = '<option value="">All Models</option>';

    if (brand === '') return;

    fetch('get_models.php?brand=' + encodeURIComponent(brand))
      .then(response => response.json())
      .then(models => {
        models.forEach(model => {
          const option = document.createElement('option');
          option.value = model;
          option.textContent = model;
          modelSelect.appendChild(option);
        });
      })
      .catch(() => {
        alert('Failed to load models for selected brand.');
      });
  });
</script>

<?php
include 'includes/footer.php';
?>