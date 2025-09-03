<?php
include("../includes/auth.php");
include("../includes/db.php");

// Get report type and format from query parameters
$report_type = $_GET['type'] ?? 'daily';
$format = $_GET['format'] ?? 'excel';

// Generate the same query as in reports.php
switch($report_type) {
    case 'weekly':
        $query = "
            SELECT CONCAT(YEAR(created_at), '-W', LPAD(WEEK(created_at), 2, '0')) as period,
                   AVG(temperature) as avg_temp,
                   AVG(ph) as avg_ph,
                   AVG(dissolved_oxygen) as avg_do,
                   COUNT(*) as readings_count,
                   MIN(created_at) as period_start,
                   MAX(created_at) as period_end
            FROM sensor_data
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
            GROUP BY YEAR(created_at), WEEK(created_at)
            ORDER BY YEAR(created_at) ASC, WEEK(created_at) ASC
        ";
        $filename = "water_quality_weekly_report_" . date('Y-m-d');
        break;
    
    case 'annual':
        $query = "
            SELECT CONCAT(YEAR(created_at), '-', LPAD(MONTH(created_at), 2, '0')) as period,
                   AVG(temperature) as avg_temp,
                   AVG(ph) as avg_ph,
                   AVG(dissolved_oxygen) as avg_do,
                   COUNT(*) as readings_count,
                   MIN(created_at) as period_start,
                   MAX(created_at) as period_end
            FROM sensor_data
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY YEAR(created_at), MONTH(created_at)
            ORDER BY YEAR(created_at) ASC, MONTH(created_at) ASC
        ";
        $filename = "water_quality_annual_report_" . date('Y-m-d');
        break;
    
    default: // daily
        $query = "
            SELECT DATE(created_at) as period,
                   AVG(temperature) as avg_temp,
                   AVG(ph) as avg_ph,
                   AVG(dissolved_oxygen) as avg_do,
                   COUNT(*) as readings_count,
                   MIN(created_at) as period_start,
                   MAX(created_at) as period_end
            FROM sensor_data
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY DATE(created_at) ASC
        ";
        $filename = "water_quality_daily_report_" . date('Y-m-d');
        break;
}

$result = $conn->query($query);
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'Period' => $row['period'],
        'Average Temperature (°C)' => round($row['avg_temp'], 2),
        'Average pH Level' => round($row['avg_ph'], 2),
        'Average Dissolved Oxygen (mg/L)' => round($row['avg_do'], 2),
        'Total Readings' => $row['readings_count'],
        'Period Start' => $row['period_start'],
        'Period End' => $row['period_end']
    ];
}

// Handle different export formats
switch($format) {
    case 'csv':
        exportCSV($data, $filename);
        break;
    case 'excel':
        exportExcel($data, $filename);
        break;
    case 'pdf':
        exportPDF($data, $filename, $report_type);
        break;
    default:
        header("Location: reports.php");
        exit;
}

function exportCSV($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add header row
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
        
        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}

function exportExcel($data, $filename) {
    // Simple Excel XML format
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    
    echo '<?xml version="1.0"?>';
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet">';
    echo '<Worksheet ss:Name="Water Quality Report">';
    echo '<Table>';
    
    // Header row
    if (!empty($data)) {
        echo '<Row>';
        foreach (array_keys($data[0]) as $header) {
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($header) . '</Data></Cell>';
        }
        echo '</Row>';
        
        // Data rows
        foreach ($data as $row) {
            echo '<Row>';
            foreach ($row as $value) {
                $type = is_numeric($value) ? 'Number' : 'String';
                echo '<Cell><Data ss:Type="' . $type . '">' . htmlspecialchars($value) . '</Data></Cell>';
            }
            echo '</Row>';
        }
    }
    
    echo '</Table>';
    echo '</Worksheet>';
    echo '</Workbook>';
    exit;
}

function exportPDF($data, $filename, $report_type) {
    // Simple HTML to PDF conversion (basic implementation)
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
    
    // For a proper PDF, you would use a library like TCPDF or DOMPDF
    // This is a simplified HTML version that browsers can print to PDF
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Water Quality <?= ucfirst($report_type) ?> Report</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .header { text-align: center; margin-bottom: 20px; }
            .metadata { margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>WaterMonitor System</h1>
            <h2><?= ucfirst($report_type) ?> Water Quality Report</h2>
            <p>Generated on: <?= date('Y-m-d H:i:s') ?></p>
        </div>
        
        <div class="metadata">
            <p><strong>Report Type:</strong> <?= ucfirst($report_type) ?></p>
            <p><strong>Total Records:</strong> <?= count($data) ?></p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <?php if (!empty($data)): ?>
                        <?php foreach (array_keys($data[0]) as $header): ?>
                            <th><?= htmlspecialchars($header) ?></th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <?php foreach ($row as $value): ?>
                            <td><?= htmlspecialchars($value) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 30px; text-align: center; font-size: 12px; color: #666;">
            <p>WaterMonitor System &copy; <?= date('Y') ?> - Water Quality Monitoring Report</p>
        </div>
        
        <script>
            window.print();
        </script>
    </body>
    </html>
    <?php
    exit;
}
?>
