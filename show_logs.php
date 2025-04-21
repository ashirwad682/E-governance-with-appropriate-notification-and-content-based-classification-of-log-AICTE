<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

require_once 'db.php';

// Fetch filters from GET request
$username = $_GET['username'] ?? '';
$severity = $_GET['severity'] ?? '';
$event_type = $_GET['event_type'] ?? '';
$classification = $_GET['classification'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

$query = "SELECT * FROM logs WHERE 1=1";
$params = [];
$types = "";

// Apply filters
if (!empty($username)) {
    $query .= " AND username LIKE ?";
    $params[] = "%$username%";
    $types .= "s";
}
if (!empty($severity)) {
    $query .= " AND severity = ?";
    $params[] = $severity;
    $types .= "s";
}
if (!empty($event_type)) {
    $query .= " AND event_type LIKE ?";
    $params[] = "%$event_type%";
    $types .= "s";
}
if (!empty($classification)) {
    $query .= " AND classification = ?";
    $params[] = $classification;
    $types .= "s";
}
if (!empty($from_date) && !empty($to_date)) {
    $query .= " AND timestamp BETWEEN ? AND ?";
    $params[] = $from_date . " 00:00:00";
    $params[] = $to_date . " 23:59:59";
    $types .= "ss";
}

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Logs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #f9f9f9; 
            padding: 20px; 
            margin: 0;
        }

        form { 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0px 0px 10px #ccc; 
            margin-bottom: 20px;
        }

        .btn, .dashboard-btn {
            padding: 10px 15px;
            background: rgba(15, 52, 92, 0.97);;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            cursor: pointer;
        }

        .btn:hover, .dashboard-btn:hover {
            background: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: white;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        @media screen and (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }

            thead {
                display: none;
            }

            tr {
                margin-bottom: 15px;
                border: 1px solid #ccc;
                border-radius: 5px;
                padding: 10px;
                background-color: white;
            }

            td {
                position: relative;
                padding-left: 50%;
                text-align: left;
                border: none;
                border-bottom: 1px solid #eee;
            }

            td::before {
                position: absolute;
                left: 10px;
                top: 10px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                font-weight: bold;
                content: attr(data-label);
            }
        }
    </style>
</head>
<body>

<h2>🔍 Search & Filter Logs</h2>
<form method="GET">
    Username: <input type="text" name="username" value="<?= htmlspecialchars($username) ?>">
    Event Type: <input type="text" name="event_type" value="<?= htmlspecialchars($event_type) ?>">
    Severity:
    <select name="severity">
        <option value="">All</option>
        <option value="info" <?= $severity=='info' ? 'selected' : '' ?>>Info</option>
        <option value="warning" <?= $severity=='warning' ? 'selected' : '' ?>>Warning</option>
        <option value="critical" <?= $severity=='critical' ? 'selected' : '' ?>>Critical</option>
    </select>
    Classification:
    <select name="classification">
        <option value="">All</option>
        <option value="general" <?= $classification=='general' ? 'selected' : '' ?>>General</option>
        <option value="critical" <?= $classification=='critical' ? 'selected' : '' ?>>Critical</option>
        <option value="warning" <?= $classification=='warning' ? 'selected' : '' ?>>Warning</option>
    </select><br><br>
    Date From: <input type="date" name="from_date" value="<?= $from_date ?>">
    Date To: <input type="date" name="to_date" value="<?= $to_date ?>">
    <button class="btn" type="submit">Search</button>
</form>

<h3>📋 Filtered Logs</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Event Type</th>
            <th>Message</th>
            <th>Severity</th>
            <th>Classification</th>
            <th>Timestamp</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td data-label="ID"><?= $row['id'] ?></td>
            <td data-label="Username"><?= htmlspecialchars($row['username']) ?></td>
            <td data-label="Event Type"><?= htmlspecialchars($row['event_type']) ?></td>
            <td data-label="Message"><?= htmlspecialchars($row['message']) ?></td>
            <td data-label="Severity"><?= htmlspecialchars($row['severity']) ?></td>
            <td data-label="Classification"><?= htmlspecialchars($row['classification']) ?></td>
            <td data-label="Timestamp"><?= $row['timestamp'] ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<a href="log_entry.html" class="dashboard-btn">➡ Go Back To Log</a>
<a href="log_dashboard.php" class="dashboard-btn">➡ Go To Log Dashboard</a>

</body>
</html>
