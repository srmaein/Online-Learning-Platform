<?php
try {
    // Database connection parameters
    $host = 'localhost';
    $dbname = 'online_education';
    $username = 'root';
    $password = '';

    // Create PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            // Get form data
            $admin_name = $_POST['admin_name'];
            $age = $_POST['age'];
            $date_of_birth = $_POST['date_of_birth'];
            $blood_group = $_POST['blood_group'];
            $phone_number = $_POST['phone_number'];
            $address = $_POST['address'];
            $email = $_POST['email'];
            $username = $_POST['username'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Prepare SQL statement
            $sql = "INSERT INTO admin_registration (admin_name, age, date_of_birth, blood_group, phone_number, address, email, username, password) 
                    VALUES (:admin_name, :age, :date_of_birth, :blood_group, :phone_number, :address, :email, :username, :password)";
            
            $stmt = $conn->prepare($sql);
            
            // Bind parameters
            $stmt->bindParam(':admin_name', $admin_name);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':date_of_birth', $date_of_birth);
            $stmt->bindParam(':blood_group', $blood_group);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);

            // Execute the statement
            if ($stmt->execute()) {
                echo "<script>
                        alert('Registration successful!');
                        window.location.href = 'Admin_view.php';
                      </script>";
            } else {
                echo "<script>
                        alert('Registration failed. Please try again.');
                      </script>";
            }
        } catch (PDOException $e) {
            echo "<script>
                    alert('Error: " . $e->getMessage() . "');
                  </script>";
        }
    }
} catch(PDOException $e) {
    echo "<script>
            alert('Connection failed: " . $e->getMessage() . "');
          </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2.5em;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }

        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Registration</h1>
        <form id="adminForm" action="" method="POST" class="registration-form" onsubmit="return validateForm(event)">
            <div class="form-group">
                <label for="admin_name">Full Name</label>
                <input type="text" id="admin_name" name="admin_name">
                <div id="admin_nameError" class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="age">Age</label>
                <input type="number" id="age" name="age">
                <div id="ageError" class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" id="date_of_birth" name="date_of_birth">
                <div id="date_of_birthError" class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="blood_group">Blood Group</label>
                <select id="blood_group" name="blood_group">
                    <option value="">Select Blood Group</option>
                    <option value="A-">A-</option>
                    <option value="A+">A+</option>
                    <option value="B-">B-</option>
                    <option value="B+">B+</option>
                    <option value="AB-">AB-</option>
                    <option value="AB+">AB+</option>
                    <option value="O-">O-</option>
                    <option value="O+">O+</option>
                </select>
                <div id="blood_groupError" class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="tel" id="phone_number" name="phone_number">
                <div id="phone_numberError" class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address"></textarea>
                <div id="addressError" class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email">
                <div id="emailError" class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username">
                <div id="usernameError" class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
                <div id="passwordError" class="error-message"></div>
            </div>
            <button type="submit" class="submit-btn">Register</button>
        </form>
        <a href="Admin_view.php" class="back-link">Back to Dashboard</a>
    </div>

    <script>
        function validateForm(event) {
            event.preventDefault();
            let isValid = true;

            // Reset all error messages
            document.querySelectorAll('.error-message').forEach(error => {
                error.style.display = 'none';
            });

            // Validate Name
            const name = document.getElementById('admin_name').value.trim();
            if (name === '') {
                showError('admin_nameError', 'Name is required');
                isValid = false;
            } else if (name.length < 3) {
                showError('admin_nameError', 'Name must be at least 3 characters long');
                isValid = false;
            }

            // Validate Age
            const age = document.getElementById('age').value;
            if (age === '') {
                showError('ageError', 'Age is required');
                isValid = false;
            } else if (age < 18 || age > 100) {
                showError('ageError', 'Age must be between 18 and 100');
                isValid = false;
            }

            // Validate Date of Birth
            const dob = document.getElementById('date_of_birth').value;
            if (dob === '') {
                showError('date_of_birthError', 'Date of birth is required');
                isValid = false;
            }

            // Validate Blood Group
            const bloodGroup = document.getElementById('blood_group').value;
            if (bloodGroup === '') {
                showError('blood_groupError', 'Blood group is required');
                isValid = false;
            }

            // Validate Phone Number
            const phone = document.getElementById('phone_number').value.trim();
            const phoneRegex = /^[0-9]{11}$/;
            if (phone === '') {
                showError('phone_numberError', 'Phone number is required');
                isValid = false;
            } else if (!phoneRegex.test(phone)) {
                showError('phone_numberError', 'Please enter a valid 11-digit phone number');
                isValid = false;
            }

            // Validate Address
            const address = document.getElementById('address').value.trim();
            if (address === '') {
                showError('addressError', 'Address is required');
                isValid = false;
            }

            // Validate Email
            const email = document.getElementById('email').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email === '') {
                showError('emailError', 'Email is required');
                isValid = false;
            } else if (!emailRegex.test(email)) {
                showError('emailError', 'Please enter a valid email address');
                isValid = false;
            }

            // Validate Username
            const username = document.getElementById('username').value.trim();
            if (username === '') {
                showError('usernameError', 'Username is required');
                isValid = false;
            } else if (username.length < 4) {
                showError('usernameError', 'Username must be at least 4 characters long');
                isValid = false;
            }

            // Validate Password
            const password = document.getElementById('password').value;
            if (password === '') {
                showError('passwordError', 'Password is required');
                isValid = false;
            } else if (password.length < 6) {
                showError('passwordError', 'Password must be at least 6 characters long');
                isValid = false;
            }

            if (isValid) {
                document.getElementById('adminForm').submit();
            }

            return false;
        }

        function showError(elementId, message) {
            const errorElement = document.getElementById(elementId);
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }

        // Add input event listeners for real-time validation
        document.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('input', function() {
                const errorElement = document.getElementById(this.id + 'Error');
                if (errorElement) {
                    errorElement.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html> 