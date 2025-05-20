<?php
// Database connection
$host = 'localhost';
$dbname = 'Online_Education';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to sanitize input data
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input data
    $name = sanitize($_POST['name']);
    $age = filter_var($_POST['age'], FILTER_VALIDATE_INT);
    $dob = sanitize($_POST['dob']);
    $bloodGroup = sanitize($_POST['bloodGroup']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $username = sanitize($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validate required fields
    if (empty($name) || empty($age) || empty($dob) || empty($bloodGroup) || 
        empty($phone) || empty($address) || empty($email) || 
        empty($username) || empty($password)) {
        die("All fields are required");
    }

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_registration WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            die("Email already registered");
        }

        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_registration WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            die("Username already taken");
        }

        // Insert data into database
        $sql = "INSERT INTO admin_registration (admin_name, age, date_of_birth, blood_group, 
                phone_number, address, email, username, password) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $name,
            $age,
            $dob,
            $bloodGroup,
            $phone,
            $address,
            $email,
            $username,
            $password
        ]);

        // Redirect to success page or login page
        header("Location: ../../login.php?registration=success");
        exit();
    } catch(PDOException $e) {
        die("Registration failed: " . $e->getMessage());
    }
} else {
    // If not POST request, redirect to registration page
    header("Location:../../VIEWS/USER/admin_registration.html");
    exit();
}
?> 