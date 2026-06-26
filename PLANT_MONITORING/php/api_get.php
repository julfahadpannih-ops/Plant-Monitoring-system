<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

header("Content-Type: application/json");
$conn = new mysqli("localhost", "root", "", "iot_plant_db");

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}

// Fetch the 10 most recent records
$result = $conn->query("SELECT id, action_type, soil, temp, hum, n_val, p_val, k_val, record_time FROM system_records ORDER BY id DESC LIMIT 10");
$records = [];

while ($row = $result->fetch_assoc()) {
    // Format the timestamp nicely for the UI
    $row['formatted_time'] = date("M d, H:i", strtotime($row['record_time']));
    $records[] = $row;
}

echo json_encode($records);
$conn->close();
?>
