<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['user'];
    $event_type = $_POST['event_type'];
    $message = $_POST['message'];
    $severity = $_POST['severity'];
    $timestamp = date("Y-m-d H:i:s");

    // Classification
    $classification = "general";
    if (stripos($message, "error") !== false || $severity == "critical") {
        $classification = "critical";
    } elseif (stripos($message, "warning") !== false) {
        $classification = "warning";
    }

    $sql = "INSERT INTO logs (username, event_type, message, severity, classification, timestamp)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $user, $event_type, $message, $severity, $classification, $timestamp);

    if ($stmt->execute()) {
        echo "✅ Log entry submitted successfully. ";
    } else {
        echo "❌ Error submitting log: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
