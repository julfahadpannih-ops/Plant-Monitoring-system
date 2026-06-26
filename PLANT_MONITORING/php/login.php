<?php
session_start();

// Database connection credentials (XAMPP defaults)
$host = "localhost";
$db_user = "root";
$db_pass = ""; // Leave blank if you haven't set a root password in XAMPP
$db_name = "iot_plant_db"; // Change this to your actual database name

// Establish connection
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify password against bcrypt hash
        if (password_verify($password, $row['password'])) {

            // Login successful
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $row['username'];

            // Redirect to your dashboard
            header("Location: Plant.php");
            exit;

        } else {
            echo "<script>alert('Incorrect password. Please try again.'); window.location.href='../frontend/login.html';</script>";
        }
    } else {
        echo "<script>alert('User not found.'); window.location.href='../frontend/login.html';</script>";
    }
    $stmt->close();
}

$conn->close();
?>