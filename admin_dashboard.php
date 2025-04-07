<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Database connection
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "user";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle loan approval/rejection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loan_id = intval($_POST['loan_id']);
    $action = $_POST['action'];

    if ($action == "Approve") {
        $sql = "UPDATE loans SET status='Approved' WHERE id='$loan_id'";
    } elseif ($action == "Reject") {
        $sql = "UPDATE loans SET status='Rejected' WHERE id='$loan_id'";
    }

    if ($conn->query($sql) === TRUE) {
        $message = "Loan request updated successfully!";
    } else {
        $error = "Error updating loan request: " . $conn->error;
    }
}

// Fetch all pending loan requests
$sql_loans = "SELECT * FROM loans WHERE status='Pending'";
$result_loans = $conn->query($sql_loans);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Cashalyze</h1><hr><br>
        <h1>Admin Dashboard</h1>
        <h2>Manage Loan Requests</h2>

        <?php if (!empty($message)) echo "<p class='success'>$message</p>"; ?>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

        <?php if ($result_loans->num_rows > 0): ?>
            <table border="1" cellpadding="10">
                <thead>
                    <tr>
                        <th>Loan ID</th>
                        <th>Username</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_loans->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td>$<?php echo number_format($row['amount'], 2); ?></td>
                            <td>
                                <form method="POST" action="admin_dashboard.php">
                                    <input type="hidden" name="loan_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="action" value="Approve">Approve</button>
                                    <button type="submit" name="action" value="Reject">Reject</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending loan requests!</p>
        <?php endif; ?>

        <form method="POST" action="admin_logout.php">
            <button class='logout-button' type="submit">Logout</button>
        </form>
    </div>
</body>
</html>
