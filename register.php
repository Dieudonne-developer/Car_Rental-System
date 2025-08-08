<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm = $_POST['confirm_password'];
  $role = $_POST['role'];

  // Validate inputs
  if (empty($username) || empty($email) || empty($password) || empty($confirm) || empty($role)) {
    $errors[] = "All fields are required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
  } elseif ($password !== $confirm) {
    $errors[] = "Passwords do not match.";
  } elseif (!in_array($role, ['admin', 'seller', 'client'])) {
    $errors[] = "Invalid role selected.";
  } else {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
      $errors[] = "Email already registered.";
    }
  }

  // If valid, insert user
  if (empty($errors)) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$username, $email, $hashed, $role])) {
      $success = "Registration successful! You can now <a href='login.php'>login</a>.";
    } else {
      $errors[] = "Failed to register user.";
    }
  }
}
?>

<?php include 'includes/header.php'; ?>

<h2 class="mb-4">Create Account</h2>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
<?php elseif ($success): ?>
  <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="post" class="row g-3 needs-validation" novalidate>
  <div class="col-md-6">
    <label class="form-label">Full Name</label>
    <input type="text" name="username" class="form-control" required>
  </div>

  <div class="col-md-6">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" required>
  </div>

  <div class="col-md-6">
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control" required minlength="6">
  </div>

  <div class="col-md-6">
    <label class="form-label">Confirm Password</label>
    <input type="password" name="confirm_password" class="form-control" required minlength="6">
  </div>

  <div class="col-md-6">
    <label class="form-label">Select Role</label>
    <select name="role" class="form-select" required>
      <option value="">Choose...</option>
      <option value="admin">Admin</option>
      <option value="seller">Seller</option>
      <option value="client">Client</option>
    </select>
  </div>

  <div class="col-12">
    <button class="btn btn-primary" type="submit">Register</button>
  </div>
</form>

<?php include 'includes/footer.php'; ?>