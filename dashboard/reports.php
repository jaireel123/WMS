<?php
include("../includes/auth.php");
include("../includes/db.php");

// Get current page for active navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Get report type from query parameter, default to daily
$report_type = $_GET['type'] ?? 'daily';

// Initialize arrays for chart data
$labels = [];
$temp_data = [];
$ph_data = [];
$do_data = [];

// Generate report data based on type
switch($report_type) {
    case 'weekly':
        // Weekly averages for last 12 weeks
        $query = "
            SELECT CONCAT(YEAR(created_at), '-W', LPAD(WEEK(created_at), 2, '0')) as period,
                   AVG(temperature) as avg_temp,
                   AVG(ph) as avg_ph,
                   AVG(dissolved_oxygen) as avg_do,
                   COUNT(*) as readings_count
            FROM sensor_data
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
            GROUP BY YEAR(created_at), WEEK(created_at)
            ORDER BY YEAR(created_at) DESC, WEEK(created_at) DESC
            LIMIT 12
        ";
        break;
    
    case 'annual':
        // Monthly averages for last 12 months
        $query = "
            SELECT CONCAT(YEAR(created_at), '-', LPAD(MONTH(created_at), 2, '0')) as period,
                   AVG(temperature) as avg_temp,
                   AVG(ph) as avg_ph,
                   AVG(dissolved_oxygen) as avg_do,
                   COUNT(*) as readings_count
            FROM sensor_data
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY YEAR(created_at), MONTH(created_at)
            ORDER BY YEAR(created_at) DESC, MONTH(created_at) DESC
            LIMIT 12
        ";
        break;
    
    default: // daily
        // Daily averages for last 30 days
        $query = "
            SELECT DATE(created_at) as period,
                   AVG(temperature) as avg_temp,
                   AVG(ph) as avg_ph,
                   AVG(dissolved_oxygen) as avg_do,
                   COUNT(*) as readings_count
            FROM sensor_data
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at) DESC
            LIMIT 30
        ";
        break;
}

$data = $conn->query($query);
$readings_count = [];

while ($row = $data->fetch_assoc()) {
    $labels[] = $row['period'];
    $temp_data[] = round($row['avg_temp'], 2);
    $ph_data[] = round($row['avg_ph'], 2);
    $do_data[] = round($row['avg_do'], 2);
    $readings_count[] = $row['readings_count'];
}

// Reverse arrays to show chronological order
$labels = array_reverse($labels);
$temp_data = array_reverse($temp_data);
$ph_data = array_reverse($ph_data);
$do_data = array_reverse($do_data);
$readings_count = array_reverse($readings_count);

// Get summary statistics
$summary_query = "
    SELECT 
        AVG(temperature) as avg_temp,
        MIN(temperature) as min_temp,
        MAX(temperature) as max_temp,
        AVG(ph) as avg_ph,
        MIN(ph) as min_ph,
        MAX(ph) as max_ph,
        AVG(dissolved_oxygen) as avg_do,
        MIN(dissolved_oxygen) as min_do,
        MAX(dissolved_oxygen) as max_do,
        COUNT(*) as total_readings
    FROM sensor_data
    WHERE ";

switch($report_type) {
    case 'weekly':
        $summary_query .= "created_at >= DATE_SUB(NOW(), INTERVAL 12 WEEK)";
        break;
    case 'annual':
        $summary_query .= "created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)";
        break;
    default:
        $summary_query .= "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        break;
}

$summary = $conn->query($summary_query)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports - WaterMonitor</title>

<link rel="stylesheet" href="../assets/bootstrap/bootstrap-5.3.8-dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/home.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
<header class="dashboard-header">
    <div class="header-left">
        <div class="header-titles">
            <h1>WaterMonitor Reports</h1>
            <p>Comprehensive water quality analysis</p>
        </div>
        <div class="header-center">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link" href="home.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="m8 3.293 6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293l6-6zm5-.793V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z"/>
                            <path d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z"/>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="reports.php">
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
    <!-- Report Type Selection -->
    <section class="mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9V5.5z"/>
                        <path d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1h-3zm1.038 3.018a6.093 6.093 0 0 1 .924 0 6 6 0 1 1-.924 0zM0 3.5c0 .753.333 1.429.86 1.887A8.035 8.035 0 0 1 4.387 1.86 2.5 2.5 0 0 0 0 3.5zM13.5 1c-.753 0-1.429.333-1.887.86a8.035 8.035 0 0 1 3.527 3.527A2.5 2.5 0 0 0 13.5 1z"/>
                    </svg>
                    <span class="d-none d-sm-inline">Report Period</span>
                    <span class="d-sm-none">Period</span>
                </h5>
                <div class="d-grid d-md-flex gap-2" role="group">
                    <a href="?type=daily" class="btn <?= $report_type === 'daily' ? 'btn-primary' : 'btn-outline-primary' ?>">
                        <span class="d-none d-sm-inline">Daily (Last 30 Days)</span>
                        <span class="d-sm-none">Daily</span>
                    </a>
                    <a href="?type=weekly" class="btn <?= $report_type === 'weekly' ? 'btn-primary' : 'btn-outline-primary' ?>">
                        <span class="d-none d-sm-inline">Weekly (Last 12 Weeks)</span>
                        <span class="d-sm-none">Weekly</span>
                    </a>
                    <a href="?type=annual" class="btn <?= $report_type === 'annual' ? 'btn-primary' : 'btn-outline-primary' ?>">
                        <span class="d-none d-sm-inline">Annual (Last 12 Months)</span>
                        <span class="d-sm-none">Annual</span>
                    </a>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <div class="dropdown">
                    <button class="btn btn-success dropdown-toggle w-100 w-md-auto" type="button" id="downloadDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                        </svg>
                        <span class="d-none d-sm-inline">Download Report</span>
                        <span class="d-sm-none">Download</span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="downloadDropdown">
                        <li>
                            <a class="dropdown-item" href="export.php?type=<?= $report_type ?>&format=excel">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.781l2.116 2.54a.5.5 0 0 0 .768-.641L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.219l-2.116-2.54z"/>
                                    <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                </svg>
                                Excel (.xlsx)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="export.php?type=<?= $report_type ?>&format=csv">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V9H3V2a1 1 0 0 1 1-1h5.5v2zM3 12v-2h2v2H3zm0 1h2v2H4a1 1 0 0 1-1-1v-1zm3 2v-2h3v2H6zm4 0v-2h3v1a1 1 0 0 1-1 1h-2zm3-3h-3v-2h3v2zm-7 0v-2h3v2H6z"/>
                                </svg>
                                CSV (.csv)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="export.php?type=<?= $report_type ?>&format=pdf">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                                    <path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.712 5.712 0 0 1-.911-.95 11.651 11.651 0 0 0-1.997.406 11.307 11.307 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.266.266 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.71 12.71 0 0 1 1.01-.193 11.744 11.744 0 0 1-.51-.858 20.801 20.801 0 0 1-.5 1.05zm2.446.45c.15.163.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.876 3.876 0 0 0-.612-.053zM8.078 7.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.737.024.084.05.167.089.24a3.517 3.517 0 0 1 .006-.09z"/>
                                </svg>
                                PDF (.pdf)
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Summary Statistics -->
    <section class="mb-4">
        <h5>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                <path d="M3 3.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zM3 6a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 6zm0 2.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
            </svg>
            Summary Statistics (<?= ucfirst($report_type) ?> Period)
        </h5>
        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">Temperature (°C)</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col">
                                <div class="small text-muted">Average</div>
                                <div class="h5 text-info"><?= round($summary['avg_temp'], 1) ?></div>
                            </div>
                            <div class="col">
                                <div class="small text-muted">Min</div>
                                <div class="h6"><?= round($summary['min_temp'], 1) ?></div>
                            </div>
                            <div class="col">
                                <div class="small text-muted">Max</div>
                                <div class="h6"><?= round($summary['max_temp'], 1) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">pH Level</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col">
                                <div class="small text-muted">Average</div>
                                <div class="h5 text-success"><?= round($summary['avg_ph'], 2) ?></div>
                            </div>
                            <div class="col">
                                <div class="small text-muted">Min</div>
                                <div class="h6"><?= round($summary['min_ph'], 2) ?></div>
                            </div>
                            <div class="col">
                                <div class="small text-muted">Max</div>
                                <div class="h6"><?= round($summary['max_ph'], 2) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">Dissolved Oxygen (mg/L)</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col">
                                <div class="small text-muted">Average</div>
                                <div class="h5 text-primary"><?= round($summary['avg_do'], 2) ?></div>
                            </div>
                            <div class="col">
                                <div class="small text-muted">Min</div>
                                <div class="h6"><?= round($summary['min_do'], 2) ?></div>
                            </div>
                            <div class="col">
                                <div class="small text-muted">Max</div>
                                <div class="h6"><?= round($summary['max_do'], 2) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <strong>Total Readings:</strong> <?= number_format($summary['total_readings']) ?> data points collected during this period
                </div>
            </div>
        </div>
    </section>

    <!-- Chart Section -->
    <section class="mb-4">
        <h5>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0Zm14.817 3.113a.5.5 0 0 1 .07.704l-4.5 5.5a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61 4.15-5.073a.5.5 0 0 1 .704-.07Z"/>
            </svg>
            <?= ucfirst($report_type) ?> Trends
        </h5>
        <div class="chart-container">
            <canvas id="reportChart"></canvas>
        </div>
    </section>

    <!-- Data Table -->
    <section>
        <h5>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm15 2h-4v3h4V4zm0 4h-4v3h4V8zm0 4h-4v3h3a1 1 0 0 0 1-1v-2zm-5 3v-3H6v3h4zm-5 0v-3H1v2a1 1 0 0 0 1 1h3zm-4-4h4V8H1v3zm0-4h4V4H1v3zm5-3v3h4V4H6zm4 4H6v3h4V8z"/>
            </svg>
            Detailed Data
        </h5>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Period</th>
                        <th>Avg Temperature (°C)</th>
                        <th>Avg pH Level</th>
                        <th>Avg Dissolved Oxygen (mg/L)</th>
                        <th>Readings Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for($i = 0; $i < count($labels); $i++): ?>
                    <tr>
                        <td><?= $labels[$i] ?></td>
                        <td><span class="badge bg-info"><?= $temp_data[$i] ?></span></td>
                        <td><span class="badge bg-success"><?= $ph_data[$i] ?></span></td>
                        <td><span class="badge bg-primary"><?= $do_data[$i] ?></span></td>
                        <td><?= $readings_count[$i] ?></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<footer>
    WaterMonitor System &copy; <?= date('Y') ?>
</footer>

    <script src="../assets/bootstrap/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Chart.js configuration for reports
    const ctx = document.getElementById('reportChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [
                {
                    label: 'Temperature (°C)',
                    data: <?= json_encode($temp_data) ?>,
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.15)',
                    borderWidth: 3,
                    pointBackgroundColor: '#17a2b8',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y'
                },
                {
                    label: 'pH Level',
                    data: <?= json_encode($ph_data) ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.15)',
                    borderWidth: 3,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y1'
                },
                {
                    label: 'Dissolved Oxygen (mg/L)',
                    data: <?= json_encode($do_data) ?>,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.15)',
                    borderWidth: 3,
                    pointBackgroundColor: '#007bff',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    fill: true,
                    tension: 0.3,
                    yAxisID: 'y'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                x: {
                    display: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                        borderDash: [2, 4]
                    },
                    title: {
                        display: true,
                        text: '<?= ucfirst($report_type) ?> Period',
                        color: '#666',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        color: '#666',
                        maxTicksLimit: 8
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                        borderDash: [2, 4]
                    },
                    title: {
                        display: true,
                        text: 'Temperature (°C) / DO (mg/L)',
                        color: '#666',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        color: '#666'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    title: {
                        display: true,
                        text: 'pH Level',
                        color: '#28a745',
                        font: {
                            size: 14,
                            weight: 'bold'
                        }
                    },
                    ticks: {
                        color: '#28a745',
                        min: 6,
                        max: 8.5,
                        stepSize: 0.5
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: '<?= ucfirst($report_type) ?> Water Quality Trends',
                    color: '#333',
                    font: {
                        size: 18,
                        weight: 'bold'
                    },
                    padding: 20
                },
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.9)',
                    titleColor: 'white',
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyColor: 'white',
                    bodyFont: {
                        size: 12
                    },
                    borderColor: 'rgba(255,255,255,0.3)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true,
                    callbacks: {
                        title: function(context) {
                            return 'Period: ' + context[0].label;
                        },
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                if (context.dataset.label.includes('Temperature')) {
                                    label += context.parsed.y + '°C';
                                } else if (context.dataset.label.includes('pH')) {
                                    label += context.parsed.y;
                                } else if (context.dataset.label.includes('Oxygen')) {
                                    label += context.parsed.y + ' mg/L';
                                }
                            }
                            return label;
                        },
                        afterBody: function(context) {
                            return 'Click to view detailed data';
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html>
