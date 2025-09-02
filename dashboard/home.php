<?php
include("../includes/auth.php");
include("../includes/db.php");

// Latest readings
$latest = $conn->query("SELECT * FROM sensor_data ORDER BY created_at DESC LIMIT 1")->fetch_assoc();

// All data (for graph)
$data = $conn->query("SELECT * FROM sensor_data ORDER BY created_at DESC LIMIT 20");
$timestamps = [];
$temp = [];
$ph = [];
$do = [];
while ($row = $data->fetch_assoc()) {
    $timestamps[] = $row['created_at'];
    $temp[] = $row['temperature'];
    $ph[] = $row['ph'];
    $do[] = $row['dissolved_oxygen'];
}

// Get current page for active navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - WaterMonitor</title>

<link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/home.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
<header class="dashboard-header">
    <div class="header-left">
        <div class="header-titles">
            <h1>WaterMonitor Dashboard</h1>
            <p>Real-time water quality monitoring system</p>
        </div>
        <div class="header-center">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page=='reports.php')?'active':'' ?>" href="reports.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0Zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07Z"/>
                        </svg>
                        Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page=='settings.php')?'active':'' ?>" href="settings.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/>
                        </svg>
                        Settings
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <div class="header-right">
        <div class="status-container">
            <span class="status-indicator status-online"></span>
            <span>System Online</span>
        </div>
        <div class="user-info">Welcome, <?= $_SESSION['username'] ?? 'User' ?></div>
        <a href="../auth/logout.php" class="btn btn-outline-light btn-sm btn-logout">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
                <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
            </svg>
            Logout
        </a>
    </div>
</header>

<main class="container">
    <!-- Latest Readings -->
    <section>
        <h5>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
            </svg>
            Latest Readings
        </h5>
        <div class="horizontal-readings">
            <div class="reading-item">
                <div class="reading-icon temp-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 16a6 6 0 0 0 6-6c0-3-4-9-6-9S2 7 2 10a6 6 0 0 0 6 6zm0-7a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
                    </svg>
                </div>
                <div class="reading-details">
                    <div class="reading-label">Temperature</div>
                    <div class="reading-value text-info"><?= $latest['temperature'] ?? 'N/A' ?> °C</div>
                    <div class="reading-time"><?= isset($latest['created_at'])?date('M j, H:i',strtotime($latest['created_at'])):'N/A' ?></div>
                </div>
            </div>

            <div class="reading-item">
                <div class="reading-icon ph-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M7.5 8a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1H8a.5.5 0 0 1-.5-.5z"/>
                    </svg>
                </div>
                <div class="reading-details">
                    <div class="reading-label">pH Level</div>
                    <div class="reading-value text-success"><?= $latest['ph'] ?? 'N/A' ?></div>
                    <div class="reading-time"><?= isset($latest['created_at'])?date('M j, H:i',strtotime($latest['created_at'])):'N/A' ?></div>
                </div>
            </div>

            <div class="reading-item">
                <div class="reading-icon do-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z"/>
                    </svg>
                </div>
                <div class="reading-details">
                    <div class="reading-label">Dissolved Oxygen</div>
                    <div class="reading-value text-primary"><?= $latest['dissolved_oxygen'] ?? 'N/A' ?> mg/L</div>
                    <div class="reading-time"><?= isset($latest['created_at'])?date('M j, H:i',strtotime($latest['created_at'])):'N/A' ?></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Real-Time Data Trends -->
    <section>
        <h5>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0Zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07Z"/>
            </svg>
            Real-Time Data Trends
        </h5>
        <div class="chart-container">
            <canvas id="lineChart"></canvas>
        </div>
    </section>

</main>

<footer>
    WaterMonitor System &copy; <?= date('Y') ?>
</footer>

<script>
window.chartData = {
    timestamps: <?= json_encode($timestamps) ?>,
    temp: <?= json_encode($temp) ?>,
    ph: <?= json_encode($ph) ?>,
    do: <?= json_encode($do) ?>
};

const ctx = document.getElementById('lineChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: window.chartData.timestamps,
        datasets: [
            {
                label: 'Temperature (°C)',
                data: window.chartData.temp,
                borderColor: '#0dcaf0',
                backgroundColor: 'rgba(13, 202, 240, 0.1)',
                tension: 0.3,
                fill: true
            },
            {
                label: 'pH Level',
                data: window.chartData.ph,
                borderColor: '#20c997',
                backgroundColor: 'rgba(32, 201, 151, 0.1)',
                tension: 0.3,
                fill: true
            },
            {
                label: 'Dissolved Oxygen (mg/L)',
                data: window.chartData.do,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.3,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            },
            x: {
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                }
            }
        }
    }
});
</script>
<script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>