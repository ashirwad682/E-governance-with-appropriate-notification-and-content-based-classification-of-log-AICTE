<?php
session_start();
require 'db.php';

if (!isset($_SESSION['email_verified']) || $_SESSION['email_verified'] !== true) {
    echo "Please verify your email OTP before registering.";
    exit;
}

$fullname = $_POST['fullname'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_BCRYPT);

// Check if the email matches the verified email
if ($_SESSION['otp_email'] !== $email) {
    echo "Email mismatch! Please verify the OTP for this email.";
    exit;
}

// Insert user into DB
$stmt = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $fullname, $email, $password);

if ($stmt->execute()) {
    echo "Registered successfully!";
    session_unset(); // Optional: Clear session
} else {
    echo "Error: " . $conn->error;
}
?>
