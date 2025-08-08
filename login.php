<?php
session_start();
require_once __DIR__ . '/config/db.php';  // Make sure this path is correct!

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  if (empty($email) || empty($password)) {
    $errors[] = "Email and password are required.";
  } else {
    // Prepare and execute the query using PDO
    $stmt = $pdo->prepare("SELECT id, username, email, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
      // Login successful: set session variables
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];

      // Redirect user based on their role
      switch ($user['role']) {
        case 'admin':
          header('Location: admin/dashboard.php');
          break;
        case 'seller':
          header('Location: seller/dashboard.php');
          break;
        case 'client':
        default:
          header('Location: client/dashboard.php');
      }
      exit;
    } else {
      $errors[] = "Invalid email or password.";
    }
  }
}
?>

<?php include 'includes/header.php'; ?>

<h2>Login</h2>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
<?php endif; ?>

<form method="post" class="row g-3 needs-validation" novalidate>
  <div class="col-md-6">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" required>
  </div>

  <div class="col-md-6">
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control" required>
  </div>

  <div class="col-12">
    <button class="btn btn-primary" type="submit">Login</button>
  </div>
</form>

<?php include 'includes/footer.php'; ?>