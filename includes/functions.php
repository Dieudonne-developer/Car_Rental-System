<?php
// functions.php

// Redirect helper
function redirect($url)
{
  header("Location: $url");
  exit;
}

// Sanitize input to prevent XSS
function e($string)
{
  return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Check if user is logged in
function is_logged_in()
{
  return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin()
{
  return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Get user role
function get_user_role()
{
  return $_SESSION['role'] ?? null;
}

// Flash message set
function set_flash_message($type, $message)
{
  $_SESSION['flash_messages'][$type][] = $message;
}

// Flash message get and clear
function get_flash_messages()
{
  if (!isset($_SESSION['flash_messages'])) {
    return [];
  }
  $messages = $_SESSION['flash_messages'];
  unset($_SESSION['flash_messages']);
  return $messages;
}

// Validate email format
function validate_email($email)
{
  return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate date format (Y-m-d)
function validate_date($date)
{
  $d = DateTime::createFromFormat('Y-m-d', $date);
  return $d && $d->format('Y-m-d') === $date;
}

// Escape string for LIKE query (PDO)
function escape_like($str)
{
  return str_replace(['%', '_'], ['\%', '\_'], $str);
}

// Pagination helper: calculates offset and limit
function get_pagination($page, $per_page)
{
  $page = max(1, (int) $page);
  $per_page = max(1, (int) $per_page);
  $offset = ($page - 1) * $per_page;
  return [$offset, $per_page];
}
