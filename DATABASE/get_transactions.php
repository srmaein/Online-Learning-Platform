<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Online_Education";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'success' => false,
        'message' => "Connection failed: " . $conn->connect_error
    ]));
}

// Get email filter if provided
$email_filter = isset($_GET['email']) ? $_GET['email'] : '';

// Prepare query based on whether email filter exists
if (!empty($email_filter)) {
    $sql = "SELECT * FROM payments WHERE email LIKE ? ORDER BY transaction_date DESC";
    $stmt = $conn->prepare($sql);
    $filter_param = "%" . $email_filter . "%";
    $stmt->bind_param("s", $filter_param);
} else {
    $sql = "SELECT * FROM payments ORDER BY transaction_date DESC";
    $stmt = $conn->prepare($sql);
}

// Execute query
$stmt->execute();
$result = $stmt->get_result();

// Prepare data as array
$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = [
        'id' => $row['transaction_id'],
        'email' => $row['email'],
        'courseName' => $row['course_name'],
        'courseId' => $row['course_id'],
        'amount' => $row['amount'],
        'paymentMethod' => $row['payment_method'],
        'date' => $row['transaction_date'],
        'status' => $row['status']
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'transactions' => $transactions
]);

// Close statement and connection
$stmt->close();
$conn->close();
?> 