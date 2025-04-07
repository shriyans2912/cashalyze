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

// Handle admin sign-up form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_user = $_POST["username"];
    $admin_pass = $_POST["password"];
    $sql = "SELECT * FROM admin WHERE username='$admin_user'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $error = "Username already exists! Please choose another.";
    } else {
        $sql = "INSERT INTO admin (username, password) VALUES ('$admin_user', '$admin_pass')";
        if ($conn->query($sql) === TRUE) {
            header("Location: admin_login.php?signup_success=true");
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sign-Up</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Cashalyze</h1><hr>
        <h1>Admin Sign-Up</h1><br>
        <?php
        // Display an error if redirected from login due to "not registered" status
        if (isset($_GET["error"]) && $_GET["error"] == "not_registered") {
            echo "<p class='error'>You must sign up before logging in.</p>";
        }
        if (!empty($error)) echo "<p class='error'>$error</p>";
        ?>
        <form method="POST" action="admin_signup.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="admin_login.php">Login Here</a></p>
    </div>
</body>
</html>
