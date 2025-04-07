<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user balance
$user = $_SESSION["username"];
$sql_balance = "SELECT balance FROM users WHERE username='$user'";
$result_balance = $conn->query($sql_balance);
$row_balance = $result_balance->fetch_assoc();
$balance = $row_balance["balance"];

// Fetch the latest loan status
$sql_loan = "SELECT amount, status FROM loans WHERE username='$user' ORDER BY id DESC LIMIT 1";
$result_loan = $conn->query($sql_loan);
if ($result_loan->num_rows > 0) {
    $row_loan = $result_loan->fetch_assoc();
    $loan_amount = $row_loan["amount"];
    $loan_status = $row_loan["status"];
} else {
    $loan_status = "No loan requests found";
}

// Handle transactions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["transaction_type"])) {
    $transaction_type = $_POST["transaction_type"];
    $amount = floatval($_POST["amount"]);

    if ($transaction_type === "Deposit") {
        $balance += $amount;
    } elseif ($transaction_type === "Withdraw") {
        if ($amount > $balance) {
            $error = "Insufficient balance!";
        } else {
            $balance -= $amount;
        }
    }

    // Update balance in the database
    $sql_update_balance = "UPDATE users SET balance='$balance' WHERE username='$user'";
    $conn->query($sql_update_balance);
}

// Handle loan requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["loan_amount"])) {
    $loan_amount = floatval($_POST["loan_amount"]);
    $sql_loan_request = "INSERT INTO loans (username, amount, status) VALUES ('$user', '$loan_amount', 'Pending')";
    $conn->query($sql_loan_request);
    $loan_status = "Pending";
}

// Generate invoice details
$invoice_html = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["generate_invoice"])) {
    $current_datetime = date('Y-m-d H:i:s');
    $invoice_html = "
        <div class='invoice'>
            <h2>E-Invoice</h2>
            <p><strong>Username:</strong> $user</p>
            <p><strong>Account Balance:</strong> ₹" . number_format($balance, 2) . "</p>
            <p><strong>Loan Status:</strong> $loan_status</p>
            <p><strong>Date and Time:</strong> $current_datetime</p>
        </div>
    ";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .status {
            font-weight: bold;
        }
        .status.rejected {
            color: red;
        }
        .status.approved {
            color: green;
        }
        .status.pending {
            color: grey;
        }
        .generate-invoice button {
            margin-top: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }
        .invoice {
            border: 2px solid #4CAF50;
            padding: 20px;
            margin-top: 20px;
            background-color: #f9f9f9;
            color: #333;
            font-family: Arial, sans-serif;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .invoice h2 {
            text-align: center;
            color: #4CAF50;
        }
        .container {
            max-width: 600px;
            margin: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cashalyze</h1>
        <h1>Welcome, <?php echo htmlspecialchars($user); ?>!</h1><br><hr>
        <h2>A/c Balance: ₹<?php echo number_format($balance, 2); ?></h2><hr>

        <?php if ($loan_status !== "No loan requests found"): ?>
            <h3>Loan Status</h3>
            <p>Amount: ₹<?php echo number_format($loan_amount, 2); ?></p>
            <p class="status <?php echo strtolower($loan_status); ?>">Status: <?php echo htmlspecialchars($loan_status); ?></p>
        <?php else: ?>
            <h3><?php echo htmlspecialchars($loan_status); ?></h3>
        <?php endif; ?><hr>

        <div class="transaction-form">
            <h2>Initiate Transaction</h2><br>
            <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST" action="dashboard.php">
                <label for="transaction_type">Transaction Type</label>
                <select id="transaction_type" name="transaction_type">
                    <option value="Select"></option>
                    <option value="Deposit">Deposit</option>
                    <option value="Withdraw">Withdraw</option>
                </select>

                <label for="amount">Amount</label>
                <input type="number" id="amount" name="amount" required>

                <button type="submit">Submit</button>
            </form>
        </div><br><hr>

        <div class="loan-form">
            <h2>Initiate Loan</h2><br>
            <form method="POST" action="dashboard.php">
                <label for="loan_amount">Loan Amount</label>
                <input type="number" id="loan_amount" name="loan_amount" required>

                <button type="submit">Request Loan</button>
            </form>
        </div><br><hr>

        <!-- Generate Invoice Section -->
        <div class="generate-invoice">
            <h2>Generate E-Invoice</h2>
            <form method="POST" action="dashboard.php">
                <button type="submit" name="generate_invoice">Generate E-Invoice</button>
            </form>
        </div><br>

        <!-- Display Invoice -->
        <?php echo $invoice_html; ?><br><hr>

        <form method="POST" action="logout.php">
            <button class="logout-button" type="submit">Logout</button>
        </form>
    </div>
</body>
</html>
