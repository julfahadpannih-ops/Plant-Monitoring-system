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

$data = json_decode(file_get_contents("php://input"), true);

if ($data) {
    $stmt = $conn->prepare("INSERT INTO system_records (action_type, soil, temp, hum, n_val, p_val, k_val) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sidddii", $data['action_type'], $data['soil'], $data['temp'], $data['hum'], $data['n_val'], $data['p_val'], $data['k_val']);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["error" => "Failed to save"]);
    }
    $stmt->close();
}
$conn->close();
?>
