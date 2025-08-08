<?php
require_once __DIR__ . '/../includes/functions.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Car Rental System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/car_rental/css/style.css" rel="stylesheet">
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="/car_rental/index.php">CarRental</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <?php if (!is_logged_in()): ?>
            <li class="nav-item">
              <a class="nav-link" href="/car_rental/login.php">Login</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/car_rental/register.php">Register</a>
            </li>
          <?php else: ?>
            <?php if (is_admin()): ?>
              <li class="nav-item">
                <a class="nav-link" href="/car_rental/dashboard/admin.php">Admin Dashboard</a>
              </li>
            <?php elseif (is_seller()): ?>
              <li class="nav-item">
                <a class="nav-link" href="/car_rental/dashboard/seller.php">Seller Dashboard</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/car_rental/upload_car.php">Upload Car</a>
              </li>
            <?php elseif (is_client()): ?>
              <li class="nav-item">
                <a class="nav-link" href="/car_rental/dashboard/client.php">My Rentals</a>
              </li>
            <?php endif; ?>
            <li class="nav-item">
              <a class="nav-link text-danger" href="/car_rental/logout.php">Logout</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4">