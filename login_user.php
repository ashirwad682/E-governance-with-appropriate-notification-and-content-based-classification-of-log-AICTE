<?php
session_start();

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "egov_logs";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get input
$user_id = $_POST['user_id'] ?? '';
$password = $_POST['password'] ?? '';

// Prepare SQL to avoid SQL injection
$sql = "SELECT * FROM users WHERE user_id = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $user_id, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  // User exists, set session and redirect to log_entry.html
  $_SESSION['user_id'] = $user_id;

  // Redirect to the desired page after login
  header("Location: log_entry.html");
  exit();
} else {
  // Invalid credentials, show error and stay on login page
  echo "<script>alert('Invalid user ID or password.'); window.location.href='login.html';</script>";
}

$conn->close();
?>
