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

// Create payments table if it doesn't exist
$sql_create_table = "CREATE TABLE IF NOT EXISTS payments (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    course_name VARCHAR(255) NOT NULL,
    course_id VARCHAR(255),
    amount VARCHAR(50) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_date DATETIME NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql_create_table)) {
    die(json_encode([
        'success' => false,
        'message' => "Error creating table: " . $conn->error
    ]));
}

// Check if this is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the raw POST data
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    // Validate required fields
    if (!isset($data['id']) || !isset($data['email']) || !isset($data['courseName']) || 
        !isset($data['amount']) || !isset($data['paymentMethod']) || !isset($data['date'])) {
        die(json_encode([
            'success' => false,
            'message' => "Missing required fields"
        ]));
    }
    
    // Prepare and bind parameters
    $stmt = $conn->prepare("INSERT INTO payments (transaction_id, email, course_name, course_id, amount, payment_method, transaction_date, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Format the transaction date
    $transaction_date = date('Y-m-d H:i:s', strtotime($data['date']));
    
    $stmt->bind_param("ssssssss", 
        $data['id'], 
        $data['email'], 
        $data['courseName'], 
        $data['courseId'], 
        $data['amount'], 
        $data['paymentMethod'], 
        $transaction_date,
        $data['status']
    );
    
    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => "Transaction saved successfully"
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Error: " . $stmt->error
        ]);
    }
    
    // Close statement
    $stmt->close();
} else {
    // Return error for non-POST requests
    echo json_encode([
        'success' => false,
        'message' => "Invalid request method"
    ]);
}

// Close connection
$conn->close();
?> 