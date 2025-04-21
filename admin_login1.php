<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check credentials
    if ($username === "AK.aicet.ac.in" && $password === "AK@321") {
        $_SESSION['admin_logged_in'] = true;
        header("Location: log_entry.html");
        exit();
    } else {
        $error = "Invalid credentials!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f0f0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      flex-direction: column;
    }
    form {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      width: 300px;
    }
    input {
      display: block;
      margin: 1rem 0;
      padding: 0.5rem;
      width: 100%;
    }
    button {
      padding: 0.5rem 1rem;
      background-color: #003366;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .error {
      color: red;
    }
    .logout-link {
      margin-top: 1rem;
      display: inline-block;
      text-decoration: none;
    }
    .logout-link button {
      background-color: #990000;
    }
  </style>
</head>
<body>
  <form method="POST" action="">
    <h2>Admin Login</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    <input type="text" name="username" placeholder="Username" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Login</button>
  </form>

  <!-- Proper logout button as a link -->
  <a href="logout.php" class="logout-link">
    <button type="button">LOGOUT</button>
  </a>
</body>
</html>
