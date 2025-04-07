<?php
session_start();

// Database connection
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "user";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle admin login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_user = $_POST["username"];
    $admin_pass = $_POST["password"];
    $sql = "SELECT * FROM admin WHERE username='$admin_user' AND password='$admin_pass'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Successful login
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        // If credentials are invalid, redirect to sign-up page
        header("Location: admin_signup.php?error=not_registered");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Cashalyze</h1><hr>
        <h1>Admin Login</h1><br>
        <?php
        // Display a success message if redirected from sign-up after successful account creation
        if (isset($_GET["signup_success"]) && $_GET["signup_success"] == "true") {
            echo "<p class='success'>Account created successfully! Please log in.</p>";
        }
        ?>
        <form method="POST" action="admin_login.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="admin_signup.php">Sign Up Here</a></p>
    </div>
</body>
</html>
