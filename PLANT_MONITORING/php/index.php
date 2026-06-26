<?php
session_start();

$message = '';
$message_type = ''; // 'success' or 'error'

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection credentials (XAMPP defaults)
    $host = "localhost";
    $db_user = "root";
    $db_pass = ""; 
    $db_name = "iot_plant_db"; 

    // Establish connection
    $conn = new mysqli($host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if ($password !== $confirm_password) {
        $message = "Passwords do not match. Please try again.";
        $message_type = "error";
    } else {
        // Check if username already exists (prepared statement)
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $message = "Username already exists. Please choose a different one.";
            $message_type = "error";
        } else {
            // Hash password before storing
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $insert_stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $insert_stmt->bind_param("ss", $username, $hashed_password);

            if ($insert_stmt->execute()) {
                $message = "Registration successful! You can now log in.";
                $message_type = "success";
            } else {
                $message = "Error: " . $conn->error;
                $message_type = "error";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IOT PLANT | Register</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="../frontend/js/tailwind.register.config.js"></script>

    <link rel="stylesheet" href="../frontend/css/register.css">
</head>
<body class="min-h-screen d-flex align-items-center justify-content-center p-3 md:p-4 relative overflow-hidden">

    <div class="position-absolute top-0 start-0 w-100 h-100 overflow-hidden z-n1 pointer-events-none opacity-20">
        <div class="position-absolute rounded-circle bg-panel-light" style="width: 400px; height: 400px; top: -100px; left: -100px; filter: blur(50px);"></div>
        <div class="position-absolute rounded-circle bg-accent-green" style="width: 300px; height: 300px; bottom: -50px; right: -50px; filter: blur(60px);"></div>
    </div>

    <div class="bg-panel-light rounded-4xl p-4 md:p-5 soft-shadow w-100 animate-fade-in-up relative z-10" style="max-width: 420px;">
        
        <div class="text-center mb-4">
            <div class="mx-auto bg-white rounded-circle soft-shadow d-flex align-items-center justify-content-center mb-3 transition-transform hover-lift" style="width: 80px; height: 80px;">
                <i class="fa-solid fa-droplet text-accent-green fs-1"></i>
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-text-main m-0">Create Account</h1>
            <p class="text-xs font-semibold text-text-muted mt-2 tracking-wide uppercase">Jose Rizal Siocon Campus</p>
        </div>

        <?php if ($message != ''): ?>
            <div class="alert <?= $message_type == 'success' ? 'alert-success bg-accent-green text-white border-0' : 'alert-danger border-0 text-white bg-accent-orange' ?> p-2 text-center text-sm rounded-xl mb-3" role="alert">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="d-flex flex-column gap-3">
            
            <div class="position-relative">
                <label for="username" class="text-xs font-bold text-text-muted mb-1 ms-1 block">Username</label>
                <div class="position-relative">
                    <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-text-muted">
                        <i class="fa-solid fa-user text-sm"></i>
                    </span>
                    <input type="text" id="username" name="username" class="w-100 form-control-custom rounded-2xl py-2.5 pe-3 ps-5 inner-shadow transition-all duration-300" placeholder="Choose a username" required>
                </div>
            </div>

            <div class="position-relative">
                <label for="password" class="text-xs font-bold text-text-muted mb-1 ms-1 block">Password</label>
                <div class="position-relative">
                    <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-text-muted">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </span>
                    <input type="password" id="password" name="password" class="w-100 form-control-custom rounded-2xl py-2.5 pe-3 ps-5 inner-shadow transition-all duration-300" placeholder="Create a password" required>
                </div>
            </div>

            <div class="position-relative mb-2">
                <label for="confirm_password" class="text-xs font-bold text-text-muted mb-1 ms-1 block">Confirm Password</label>
                <div class="position-relative">
                    <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-text-muted">
                        <i class="fa-solid fa-check text-sm"></i>
                    </span>
                    <input type="password" id="confirm_password" name="confirm_password" class="w-100 form-control-custom rounded-2xl py-2.5 pe-3 ps-5 inner-shadow transition-all duration-300" placeholder="Type password again" required>
                </div>
            </div>

            <button type="submit" class="btn w-100 bg-accent-green text-white rounded-2xl font-bold py-3 hover:bg-[#4a6643] active:scale-95 transition-all border-0 shadow-sm hover-lift mt-2">
                Register <i class="fa-solid fa-user-plus ms-2 text-sm"></i>
            </button>
            
        </form>

        <div class="text-center mt-4 pt-3 border-t border-panel-dark">
            <p class="text-xs text-text-muted font-medium m-0">
                Already have an account? <a href="../frontend/login.html" class="text-accent-green font-bold text-decoration-none ms-1 hover:text-widget-brown transition-colors">Log In Here</a>
            </p>
        </div>

    </div>

</body>
</html>