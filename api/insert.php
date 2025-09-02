<?php
include("../includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $temp = $_POST['temperature'];
    $ph   = $_POST['ph'];
    $do   = $_POST['do_level'];

    $sql = "INSERT INTO sensor_data (temperature, ph, do_level)
            VALUES ('$temp', '$ph', '$do')";
    if ($conn->query($sql)) {
        echo "Data inserted successfully";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Invalid request";
}
?>
