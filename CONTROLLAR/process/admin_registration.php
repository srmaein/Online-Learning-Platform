<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
if (!isset($data['name']) || !isset($data['email']) || !isset($data['phone']) || 
    !isset($data['username']) || !isset($data['password'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required'
    ]);
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'Online_Education';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_registration WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Email already exists'
        ]);
        exit;
    }

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_registration WHERE username = ?");
    $stmt->execute([$data['username']]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Username already exists'
        ]);
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

    // Insert new admin with correct column names
    $stmt = $pdo->prepare("INSERT INTO admin_registration (full_name, email, phone_number, username, password) VALUES (?, ?, ?, ?, ?)");
    $result = $stmt->execute([
        $data['name'],
        $data['email'],
        $data['phone'],
        $data['username'],
        $hashedPassword
    ]);

    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Registration failed'
        ]);
    }

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?> 