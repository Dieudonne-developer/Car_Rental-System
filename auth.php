<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

// Optional: Check for specific role
function require_role($role)
{
  if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
    // Redirect unauthorized users to home or show an error
    header("Location: index.php"); // or "unauthorized.php"
    exit();
  }
}
?>