<?php
session_start();
require 'db.php';

// Handle email verification check
if (!isset($_SESSION['email_verified']) || $_SESSION['email_verified'] !== true) {
    echo "Please verify your email OTP before registering.";
    exit;
}

// Check if the form was submitted for registration or login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Handle Registration
    if (isset($_POST['register'])) {
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
    }

    // Handle Login
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        // Query database for user matching the username
        $stmt = $conn->prepare("SELECT password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($stored_password, $stored_role);
        $stmt->fetch();

        // Check if user exists and password is correct
        if ($stmt->num_rows > 0 && password_verify($password, $stored_password)) {
            // Validate the role
            if ($stored_role === $role) {
                // Store user data in session
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $stored_role;
                
                // Redirect to log_entry.html
                header('Location: log_entry.html');
                exit();
            } else {
                echo "Role mismatch! Please select the correct role.";
                exit();
            }
        } else {
            echo "Invalid credentials. Please try again.";
            exit();
        }
    }
}
?>
