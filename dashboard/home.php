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

<link rel="stylesheet" href="../assets/bootstrap/bootstrap-5.3.8-dist/css/bootstrap.min.css">
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
                    <a class="nav-link active" href="home.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="m8 3.293 6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293l6-6zm5-.793V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z"/>
                            <path d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z"/>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0Zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07Z"/>
                        </svg>
                        Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z"/>
                        </svg>
                        Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="settings.php">
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
    <section class="mb-4">
        <div class="horizontal-readings">
            <div class="reading-item">
                <div class="reading-icon temp-icon">
                    <img src="../assets/img/thermometer.png" alt="Temperature" width="32" height="32">
                </div>
                <div class="reading-details">
                    <div class="reading-info">
                        <div class="reading-label">Temperature</div>
                        <div class="reading-value text-info"><?= $latest['temperature'] ?? 'N/A' ?> °C</div>
                    </div>
                    <div class="reading-meta">
                        <div class="reading-time"><?= isset($latest['created_at'])?date('M j, H:i',strtotime($latest['created_at'])):'N/A' ?></div>
                        <div class="reading-status">
                            <?php 
                            $temp = $latest['temperature'] ?? 0;
                            if($temp >= 20 && $temp <= 30) {
                                echo '<span class="badge bg-success">Normal</span>';
                            } elseif($temp > 30) {
                                echo '<span class="badge bg-warning">High</span>';
                            } else {
                                echo '<span class="badge bg-info">Low</span>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="reading-item">
                <div class="reading-icon ph-icon">
                    <img src="../assets/img/ph-balance.png" alt="pH Level" width="32" height="32">
                </div>
                <div class="reading-details">
                    <div class="reading-info">
                        <div class="reading-label">pH Level</div>
                        <div class="reading-value text-success"><?= $latest['ph'] ?? 'N/A' ?></div>
                    </div>
                    <div class="reading-meta">
                        <div class="reading-time"><?= isset($latest['created_at'])?date('M j, H:i',strtotime($latest['created_at'])):'N/A' ?></div>
                        <div class="reading-status">
                            <?php 
                            $ph = $latest['ph'] ?? 0;
                            if($ph >= 6.5 && $ph <= 8.5) {
                                echo '<span class="badge bg-success">Normal</span>';
                            } elseif($ph > 8.5) {
                                echo '<span class="badge bg-warning">Alkaline</span>';
                            } else {
                                echo '<span class="badge bg-danger">Acidic</span>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="reading-item">
                <div class="reading-icon do-icon">
                    <img src="../assets/img/oxygen.png" alt="Dissolved Oxygen" width="32" height="32">
                </div>
                <div class="reading-details">
                    <div class="reading-info">
                        <div class="reading-label">Dissolved Oxygen</div>
                        <div class="reading-value text-primary"><?= $latest['dissolved_oxygen'] ?? 'N/A' ?> mg/L</div>
                    </div>
                    <div class="reading-meta">
                        <div class="reading-time"><?= isset($latest['created_at'])?date('M j, H:i',strtotime($latest['created_at'])):'N/A' ?></div>
                        <div class="reading-status">
                            <?php 
                            $do = $latest['dissolved_oxygen'] ?? 0;
                            if($do >= 5) {
                                echo '<span class="badge bg-success">Good</span>';
                            } elseif($do >= 3) {
                                echo '<span class="badge bg-warning">Fair</span>';
                            } else {
                                echo '<span class="badge bg-danger">Poor</span>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Real-Time Data Trends -->
    <section>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0Zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07Z"/>
                </svg>
                <span class="d-none d-sm-inline">Real-Time Data Trends</span>
                <span class="d-sm-none">Live Data</span>
            </h5>
            <small class="text-muted d-none d-md-inline">Last 20 readings</small>
        </div>
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
                borderColor: '#ff6b6b',
                backgroundColor: 'rgba(255, 107, 107, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#ff6b6b',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 6,
                tension: 0.4,
                fill: false
            },
            {
                label: 'pH Level',
                data: window.chartData.ph,
                borderColor: '#4ecdc4',
                backgroundColor: 'rgba(78, 205, 196, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#4ecdc4',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 6,
                tension: 0.4,
                fill: false
            },
            {
                label: 'Dissolved Oxygen (mg/L)',
                data: window.chartData.do,
                borderColor: '#45b7d1',
                backgroundColor: 'rgba(69, 183, 209, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#45b7d1',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 6,
                tension: 0.4,
                fill: false
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 800,
            easing: 'easeOutQuart'
        },
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            title: {
                display: true,
                text: 'Last 20 Readings - Live Data Trends',
                color: '#333',
                font: {
                    size: 16,
                    weight: 'bold'
                },
                padding: 20
            },
            legend: {
                position: 'top',
                labels: {
                    usePointStyle: true,
                    pointStyle: 'line',
                    padding: 20,
                    font: {
                        size: 12,
                        weight: '500'
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(255,255,255,0.95)',
                titleColor: '#333',
                titleFont: {
                    size: 14,
                    weight: 'bold'
                },
                bodyColor: '#666',
                bodyFont: {
                    size: 12
                },
                borderColor: 'rgba(0,0,0,0.1)',
                borderWidth: 1,
                cornerRadius: 8,
                displayColors: true,
                padding: 12,
                callbacks: {
                    title: function(context) {
                        const date = new Date(context[0].label);
                        return date.toLocaleString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    },
                    label: function(context) {
                        let label = context.dataset.label || '';
                        let value = context.parsed.y;
                        let status = '';
                        
                        if (label.includes('Temperature')) {
                            status = value >= 20 && value <= 30 ? ' ✓ Normal' : 
                                    value > 30 ? ' ⚠ High' : ' ❄ Low';
                            return `🌡️ ${value}°C${status}`;
                        } else if (label.includes('pH')) {
                            status = value >= 6.5 && value <= 8.5 ? ' ✓ Normal' : 
                                    value > 8.5 ? ' ⚠ Alkaline' : ' ⚠ Acidic';
                            return `⚖️ pH ${value}${status}`;
                        } else if (label.includes('Oxygen')) {
                            status = value >= 5 ? ' ✓ Good' : 
                                    value >= 3 ? ' ⚠ Fair' : ' ❌ Poor';
                            return `💧 ${value} mg/L${status}`;
                        }
                        return label + ': ' + value;
                    },
                    footer: function(context) {
                        return 'Reading #' + (context[0].dataIndex + 1) + ' of 20';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)',
                    borderDash: [5, 5]
                },
                title: {
                    display: true,
                    text: 'Parameter Values',
                    color: '#666',
                    font: {
                        size: 13,
                        weight: 'bold'
                    }
                },
                ticks: {
                    color: '#666',
                    font: {
                        size: 11
                    },
                    callback: function(value, index, ticks) {
                        return Number(value).toFixed(1);
                    }
                }
            },
            x: {
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)',
                    borderDash: [5, 5]
                },
                title: {
                    display: true,
                    text: 'Time (Most Recent → Oldest)',
                    color: '#666',
                    font: {
                        size: 13,
                        weight: 'bold'
                    }
                },
                ticks: {
                    color: '#666',
                    maxTicksLimit: 6,
                    font: {
                        size: 10
                    },
                    callback: function(value, index, ticks) {
                        const label = this.getLabelForValue(value);
                        const date = new Date(label);
                        return date.toLocaleTimeString('en-US', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    }
                }
            }
        }
    }
});
</script>
<script src="../assets/bootstrap/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>