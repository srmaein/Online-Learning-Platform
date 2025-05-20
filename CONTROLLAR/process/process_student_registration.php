<?php
require_once '../../DATABASE/student_db_connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set proper headers
header('Content-Type: application/json');

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate required fields
        $required_fields = ['first', 'last', 'mobile', 'gender', 'blood', 'user', 'email', 'password'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        $first_name = $_POST['first'];
        $last_name = $_POST['last'];
        $contact = $_POST['mobile'];
        $gender = $_POST['gender'];
        $blood_group = $_POST['blood'];
        $user_type = $_POST['user'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Check if email already exists
        $check_email = $pdo->prepare("SELECT COUNT(*) FROM student_registration WHERE email = ?");
        $check_email->execute([$email]);
        if ($check_email->fetchColumn() > 0) {
            throw new Exception("Email already registered");
        }

        $sql = "INSERT INTO student_registration (first_name, last_name, contact, gender, blood_group, user_type, email, password)
                VALUES (:first_name, :last_name, :contact, :gender, :blood_group, :user_type, :email, :password)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':contact', $contact);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':blood_group', $blood_group);
        $stmt->bindParam(':user_type', $user_type);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);

        if (!$stmt->execute()) {
            throw new Exception("Database error: " . implode(" ", $stmt->errorInfo()));
        }

        echo json_encode([
            'status' => 'success', 
            'message' => 'Registration successful!',
            'redirect' => '../../login.php'
        ]);
        exit();
    } else {
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    // Log the error
    error_log("Registration error: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Registration failed: ' . $e->getMessage()
    ]);
    exit();
}
?> 