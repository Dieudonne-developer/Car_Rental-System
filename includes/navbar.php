<?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'client'): ?>
  <li class="nav-item">
    <a class="nav-link" href="rental_history.php">My Rentals</a>
  </li>
<?php endif; ?>