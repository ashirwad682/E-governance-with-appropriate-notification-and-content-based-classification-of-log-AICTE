<?php
require_once 'db.php';

$severityData = $conn->query("SELECT severity, COUNT(*) as count FROM logs GROUP BY severity");
$classificationData = $conn->query("SELECT classification, COUNT(*) as count FROM logs GROUP BY classification");
$logsOverTime = $conn->query("SELECT DATE(timestamp) as log_date, COUNT(*) as count FROM logs WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(timestamp) ORDER BY log_date");
$topUsers = $conn->query("SELECT username, COUNT(*) as count FROM logs GROUP BY username ORDER BY count DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>📈 Dashboard - Log Analytics</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    :root {
      --primary-color:rgba(15, 52, 92, 0.97);
      --secondary-color: #F5F7FA;
      --card-bg: #ffffff;
      --text-color: #333;
      --shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--secondary-color);
      color: var(--text-color);
    }

    header {
      background-color: var(--primary-color);
      color: white;
      padding: 20px;
      text-align: center;
    }

    nav {
      text-align: center;
      margin-bottom: 1rem;
    }

    nav a {
      margin: 0 10px;
      text-decoration: none;
      color: var(--primary-color);
      font-weight: bold;
    }

    .dashboard-wrapper {
      transition: filter 0.3s ease;
    }

    .dashboard-wrapper.blurred {
      filter: blur(5px);
      pointer-events: none;
      user-select: none;
    }

    .dashboard {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 20px;
      padding: 30px;
    }

    .card {
      background-color: var(--card-bg);
      padding: 20px;
      border-radius: 12px;
      box-shadow: var(--shadow);
      cursor: pointer;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-container {
      text-align: center;
      margin: 30px;
    }

    .dashboard-btn {
      background-color: var(--primary-color);
      color: white;
      padding: 12px 24px;
      border-radius: 8px;
      font-size: 16px;
      text-decoration: none;
      transition: background-color 0.3s ease;
    }

    .dashboard-btn:hover {
      background-color: #0056b3;
    }

    canvas {
      width: 100% !important;
      height: auto !important;
    }

    /* Modal Styles */
    .modal {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) scale(0.8);
      width: 90%;
      max-width: 600px;
      background-color: white;
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
      padding: 30px;
      z-index: 999;
      opacity: 0;
      transition: all 0.3s ease;
      pointer-events: none;
    }

    .modal.active {
      transform: translate(-50%, -50%) scale(1);
      opacity: 1;
      pointer-events: auto;
    }

    .modal-close {
      position: absolute;
      top: 10px;
      right: 15px;
      background: none;
      border: none;
      font-size: 20px;
      cursor: pointer;
    }

  </style>
</head>
<body>

<header>
  <h1>Government of India - E-Governance Portal</h1>
  <p>AICTE-Compliant Server Log Management System</p>
</header>

<nav>
  <a href="1.html">Home</a>
  <a href="2.html">About</a>
  <a href="3.html">Contact</a>
</nav>

<div class="dashboard-wrapper" id="dashboardWrapper">
  <div class="dashboard">
    <div class="card" onclick="openModal('modal1')">
      <h2>1. Severity Distribution</h2>
      <canvas id="severityChart"></canvas>
    </div>

    <div class="card" onclick="openModal('modal2')">
      <h2>2. Classification Distribution</h2>
      <canvas id="classificationChart"></canvas>
    </div>

    <div class="card" onclick="openModal('modal3')">
      <h2>3. Logs Over Last 7 Days</h2>
      <canvas id="logsOverTimeChart"></canvas>
    </div>

    <div class="card" onclick="openModal('modal4')">
      <h2>4. Top 5 Users Generating Logs</h2>
      <canvas id="topUsersChart"></canvas>
    </div>
  </div>

  <div class="btn-container">
    <a href="show_logs.php" class="dashboard-btn">🔙 Back to Log Viewer</a>
  </div>
</div>

<!-- Modals -->
<div id="modal1" class="modal">
  <button class="modal-close" onclick="closeModal()">×</button>
  <h2>1. Severity Distribution</h2>
  <canvas id="severityChartModal"></canvas>
</div>

<div id="modal2" class="modal">
  <button class="modal-close" onclick="closeModal()">×</button>
  <h2>2. Classification Distribution</h2>
  <canvas id="classificationChartModal"></canvas>
</div>

<div id="modal3" class="modal">
  <button class="modal-close" onclick="closeModal()">×</button>
  <h2>3. Logs Over Last 7 Days</h2>
  <canvas id="logsOverTimeChartModal"></canvas>
</div>

<div id="modal4" class="modal">
  <button class="modal-close" onclick="closeModal()">×</button>
  <h2>4. Top 5 Users Generating Logs</h2>
  <canvas id="topUsersChartModal"></canvas>
</div>

<script>
  // Chart Data from PHP
  const severityLabels = [<?php while($row = $severityData->fetch_assoc()) echo "'".$row['severity']."',"; ?>];
  const severityCounts = [<?php $severityData->data_seek(0); while($row = $severityData->fetch_assoc()) echo $row['count'].","; ?>];

  const classificationLabels = [<?php while($row = $classificationData->fetch_assoc()) echo "'".$row['classification']."',"; ?>];
  const classificationCounts = [<?php $classificationData->data_seek(0); while($row = $classificationData->fetch_assoc()) echo $row['count'].","; ?>];

  const logsLabels = [<?php while($row = $logsOverTime->fetch_assoc()) echo "'".$row['log_date']."',"; ?>];
  const logsCounts = [<?php $logsOverTime->data_seek(0); while($row = $logsOverTime->fetch_assoc()) echo $row['count'].","; ?>];

  const userLabels = [<?php while($row = $topUsers->fetch_assoc()) echo "'".$row['username']."',"; ?>];
  const userCounts = [<?php $topUsers->data_seek(0); while($row = $topUsers->fetch_assoc()) echo $row['count'].","; ?>];

  // Create all charts both in card and modal
  function createChart(canvasId, type, labels, data, color) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    return new Chart(ctx, {
      type: type,
      data: {
        labels: labels,
        datasets: [{
          label: 'Log Count',
          data: data,
          backgroundColor: color,
          borderColor: color,
          fill: type === 'line' ? false : true,
          tension: 0.3
        }]
      }
    });
  }

  window.onload = () => {
    createChart('severityChart', 'pie', severityLabels, severityCounts, ['#4CAF50', '#FF9800', '#F44336']);
    createChart('classificationChart', 'bar', classificationLabels, classificationCounts, '#2196F3');
    createChart('logsOverTimeChart', 'line', logsLabels, logsCounts, '#673AB7');
    createChart('topUsersChart', 'bar', userLabels, userCounts, '#9C27B0');

    createChart('severityChartModal', 'pie', severityLabels, severityCounts, ['#4CAF50', '#FF9800', '#F44336']);
    createChart('classificationChartModal', 'bar', classificationLabels, classificationCounts, '#2196F3');
    createChart('logsOverTimeChartModal', 'line', logsLabels, logsCounts, '#673AB7');
    createChart('topUsersChartModal', 'bar', userLabels, userCounts, '#9C27B0');
  }

  function openModal(id) {
    document.getElementById(id).classList.add('active');
    document.getElementById('dashboardWrapper').classList.add('blurred');
  }

  function closeModal() {
    document.querySelectorAll('.modal').forEach(modal => modal.classList.remove('active'));
    document.getElementById('dashboardWrapper').classList.remove('blurred');
  }
</script>

</body>
</html>
