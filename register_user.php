<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "egov_logs";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$email = $_POST['email'];
$fullname = $_POST['fullname'];
$phone = $_POST['phone'];
$department = $_POST['department'];
$role = $_POST['role'];

$result = $conn->query("SELECT user_id, password FROM users WHERE email = '$email'");

if ($result->num_rows > 0) {
  // Already registered
  $row = $result->fetch_assoc();
  $user_id = $row['user_id'];
  $password = $row['password'];
  echo "<script>alert('You are already registered.\\nUser ID: $user_id\\nPassword: $password'); window.location.href='login.html';</script>";
} else {
  // Generate new user_id and password
  $user_id = "user" . rand(1000, 9999);
  $password = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789@#$"), 0, 8);

  $stmt = $conn->prepare("INSERT INTO users (fullname, email, phone, department, role, user_id, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sssssss", $fullname, $email, $phone, $department, $role, $user_id, $password);
  $stmt->execute();

  echo "<script>alert('Registration successful!\\nUser ID: $user_id\\nPassword: $password'); window.location.href='login.html';</script>";
}

$conn->close();
?>
