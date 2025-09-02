<?php
include("../includes/auth.php");
include("../includes/db.php");

// Daily averages
$data = $conn->query("
    SELECT DATE(created_at) as day,
           AVG(temperature) as avg_temp,
           AVG(ph) as avg_ph,
           AVG(dissolved_oxygen) as avg_do
    FROM sensor_data
    GROUP BY DATE(created_at)
    ORDER BY day DESC
    LIMIT 7
");

$days = [];
$temp = [];
$ph = [];
$do = [];
while ($row = $data->fetch_assoc()) {
    $days[] = $row['day'];
    $temp[] = $row['avg_temp'];
    $ph[] = $row['avg_ph'];
    $do[] = $row['avg_do'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daily Reports - WaterMonitor</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h2>Daily Reports</h2>
    <p><a href="home.php">Back to Home</a></p>
    <canvas id="dailyChart"></canvas>

    <script>
    const ctx = document.getElementById('dailyChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($days) ?>,
            datasets: [
                {
                    label: 'Avg Temp',
                    data: <?= json_encode($temp) ?>,
                    borderColor: 'red',
                    fill: false
                },
                {
                    label: 'Avg pH',
                    data: <?= json_encode($ph) ?>,
                    borderColor: 'blue',
                    fill: false
                },
                {
                    label: 'Avg DO',
                    data: <?= json_encode($do) ?>,
                    borderColor: 'green',
                    fill: false
                }
            ]
        }
    });
    </script>
</body>
</html>
