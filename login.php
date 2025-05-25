<?php
// Set session cookie parameters before starting the session
session_set_cookie_params([
    'lifetime' => 0, // Session cookie (expires when browser closes)
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start the session
session_start();

// Include configuration and database connection
require_once 'config.php';
require_once 'DATABASE/db_connection.php';

// Regenerate session ID periodically
if (!isset($_SESSION['last_regeneration']) || 
    time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Display errors in production (remove in actual production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$error = '';
$success = '';
$debug_info = ''; // For debugging

// Check if there's a password reset success message
if (isset($_GET['reset']) && $_GET['reset'] === 'success') {
    $success = "Password has been reset successfully. Please login with your new password.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get input values and trim whitespace
    $inputUsername = isset($_POST['un']) ? trim($_POST['un']) : '';
    $inputPassword = isset($_POST['pw']) ? $_POST['pw'] : '';

    if (empty($inputUsername) || empty($inputPassword)) {
        $error = "Please enter both username/email and password";
    } else {
        try {
            $userFound = false;
            $loginSuccessful = false;

            // Log login attempt
            error_log("Login attempt with username/email: " . $inputUsername);

            // STEP 1: Check teacher table first
            $stmt = $conn->prepare("SELECT * FROM teachers WHERE email = :input OR username = :input OR user_id = :input");
            $stmt->bindParam(':input', $inputUsername);
            $stmt->execute();
            $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($teacher) {
                $userFound = true;
                // Debug info for teacher login
                $debug_info = "Teacher found with ID: " . $teacher['id'] . " and username: " . $teacher['username'];
                error_log("Teacher found with ID: " . $teacher['id']);
                
                // Debug: Check stored password format
                $debug_info .= "<br>Stored password hash: " . substr($teacher['password'], 0, 10) . "...";
                
                // Verify password - first try direct comparison for non-hashed passwords
                if ($inputPassword === $teacher['password']) {
                    // Direct match (unhashed password)
                    $_SESSION['user_id'] = $teacher['id'];
                    $_SESSION['username'] = $teacher['username'];
                    $_SESSION['user_type'] = 'teacher';
                    $_SESSION['name'] = $teacher['teacher_name'];
                    $_SESSION['email'] = $teacher['email'];
                    $_SESSION['teacher_id'] = $teacher['user_id'];
                    $_SESSION['last_activity'] = time();
                    $_SESSION['last_regeneration'] = time();
                    
                    $loginSuccessful = true;
                    error_log("Teacher login successful (direct match): " . $teacher['username']);
                    
                    // Redirect to teacher dashboard
                    header("Location: " . model('teadas.html'));
                    exit();
                }
                // Try with password_verify for hashed passwords
                else if (password_verify($inputPassword, $teacher['password'])) {
                    // Password is correct
                    $_SESSION['user_id'] = $teacher['id'];
                    $_SESSION['username'] = $teacher['username'];
                    $_SESSION['user_type'] = 'teacher';
                    $_SESSION['name'] = $teacher['teacher_name'];
                    $_SESSION['email'] = $teacher['email'];
                    $_SESSION['teacher_id'] = $teacher['user_id'];
                    $_SESSION['last_activity'] = time();
                    $_SESSION['last_regeneration'] = time();
                    
                    $loginSuccessful = true;
                    error_log("Teacher login successful: " . $teacher['username']);
                    
                    // Redirect to teacher dashboard
                    header("Location: " . model('teadas.php'));
                    exit();
                } else {
                    // Password is incorrect
                    error_log("Password verification failed for teacher: " . $teacher['username']);
                    $error = "Invalid password for teacher account. Please try again.";
                    $debug_info .= "<br>Input password: " . substr($inputPassword, 0, 2) . "***";
                }
            }

            // STEP 2: If not a teacher, check admin table
            if (!$userFound) {
                $stmt = $conn->prepare("SELECT * FROM admin_registration WHERE email = :input OR username = :input");
                $stmt->bindParam(':input', $inputUsername);
                $stmt->execute();
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($admin) {
                    $userFound = true;
                    if (password_verify($inputPassword, $admin['password'])) {
                        $_SESSION['user_id'] = $admin['id'];
                        $_SESSION['username'] = $admin['username'];
                        $_SESSION['user_type'] = 'admin';
                        $_SESSION['name'] = $admin['admin_name'];
                        $_SESSION['last_activity'] = time();
                        $_SESSION['last_regeneration'] = time();
                        
                        $loginSuccessful = true;
                        error_log("Admin login successful: " . $admin['username']);
                        
                        // Redirect to admin dashboard
                        header("Location: " . model('dashboard.html'));
                        exit();
                    } else {
                        $error = "Invalid password. Please try again.";
                    }
                }
            }

            // STEP 3: If not an admin, check student table
            if (!$userFound) {
                // Fix: student_registration table doesn't have username column
                // Check for email, first_name + last_name, or student_id instead
                $stmt = $conn->prepare("SELECT * FROM student_registration WHERE email = :input OR CONCAT(first_name, ' ', last_name) = :input");
                $stmt->bindParam(':input', $inputUsername);
                $stmt->execute();
                $student = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($student) {
                    $userFound = true;
                    if (password_verify($inputPassword, $student['password'])) {
                        $_SESSION['user_id'] = $student['id'];
                        // Use email as username since there's no username field
                        $_SESSION['username'] = $student['email'];
                        $_SESSION['user_type'] = 'student';
                        $_SESSION['name'] = $student['first_name'] . ' ' . $student['last_name'];
                        $_SESSION['email'] = $student['email'];
                        $_SESSION['last_activity'] = time();
                        $_SESSION['last_regeneration'] = time();
                        
                        $loginSuccessful = true;
                        error_log("Student login successful: " . $student['email']);
                        
                        // Redirect to student dashboard
                        header("Location: " . model('index.html'));
                        exit();
                    } else {
                        $error = "Invalid password. Please try again.";
                    }
                }
            }

            // STEP 4: No user found with the provided credentials
            if (!$userFound) {
                $error = "No account found with this email/username/ID. Please check your credentials or register.";
                error_log("No user found with username/email: " . $inputUsername);
            }

        } catch(PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = "A database error occurred. Please try again later.";
            $debug_info = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="<?php echo CSS_PATH; ?>login_style.css">
    
    <link rel="icon" type="image/x-icon" href="<?php echo IMAGES_PATH; ?>top.png">
    <title>Login Page</title>

    <style>
        body {
            background: url('<?php echo BASE_URL; ?>PUBLIC/pic/img.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        .error-message {
            color: white;
            background-color: #dc3545;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 10px;
        }
        .success-message {
            color: white;
            background-color: #28a745;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 10px;
        }
        .debug-info {
            color: #333;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($debug_info)): ?>
            <div class="debug-info"><?php echo $debug_info; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <h1>Login</h1>
            <div class="input_box">
                <input type="text" placeholder="Username, Email or ID" id="un" name="un" required>
                <i class='bx bx-user-circle'></i>
                <span id="UsernameError" style="color: red;"></span>
            </div>
            <div class="input_box">
                <input type="password" placeholder="Password" id="pw" name="pw" required>
                <i class='bx bx-lock'></i>
                <span id="passwordError" style="color: red;"></span>
            </div>
            
            <div class="forget">
                <input type="checkbox" name="rememberMe" id="rememberMe">
                <label for="rememberMe">Remember Me</label>
                <a href="VIEWS/auth/forget-password.html">Forgot Password?</a>
            </div>

            <button type="submit" name="login" class="btn">Login</button>

            <div class="registration_link">
                <p>Don't have an account? Only Student Allowed <a href="VIEWS/USER/Student_Registration.html">Register</a></p>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const usernameInput = document.getElementById('un');
        const passwordInput = document.getElementById('pw');
        const usernameError = document.getElementById('UsernameError');
        const passwordError = document.getElementById('passwordError');
        
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Reset error messages
            usernameError.textContent = '';
            passwordError.textContent = '';
            
            // Validate username/email
            if (!usernameInput.value.trim()) {
                usernameError.textContent = 'Username or Email is required';
                isValid = false;
            }
            
            // Validate password
            if (!passwordInput.value.trim()) {
                passwordError.textContent = 'Password is required';
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    });
    </script>
</body>
</html>
