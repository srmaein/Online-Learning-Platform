<?php
session_start();
include_once __DIR__ . '/../../MODELS/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Fetch all student information
$query = "SELECT * FROM student_registration ORDER BY first_name ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #4caf50;
            --secondary-color: #45a049;
            --text-color: #333;
            --bg-color: #f2f2f2;
            --white: #ffffff;
            --shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            --sidebar-color: #9a47f8;
            --sidebar-hover: #b47af9;
        }

        body {
            background: var(--bg-color);
            min-height: 100vh;
            display: flex;
            position: relative;
            padding-bottom: 60px;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 70px;
            background: var(--sidebar-color);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px 10px;
            box-shadow: var(--shadow);
            transition: width 0.3s ease;
            overflow: hidden;
        }

        .sidebar:hover {
            width: 250px;
        }

        .sidebar h2 {
            color: var(--white);
            margin-bottom: 30px;
            text-align: center;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar:hover h2 {
            opacity: 1;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar ul li {
            margin-bottom: 15px;
        }

        .sidebar ul li a {
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .sidebar ul li a:hover {
            background: var(--sidebar-hover);
            color: var(--white);
        }

        .sidebar ul li a i {
            margin-right: 10px;
            font-size: 20px;
            min-width: 30px;
            text-align: center;
        }

        .sidebar ul li a span {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar:hover ul li a span {
            opacity: 1;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 70px;
            width: calc(100% - 70px);
            padding: 20px;
            transition: margin-left 0.3s ease, width 0.3s ease;
            min-height: 100vh;
            position: relative;
        }

        .sidebar:hover + .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
        }

        /* Header Styles */
        .header {
            background: var(--white);
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .search-box {
            position: relative;
            display: flex;
            gap: 10px;
        }

        .search-box input {
            padding: 10px 40px 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
        }

        .search-box i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .filter-group {
            display: flex;
            gap: 10px;
        }

        .filter-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background: #f8f9fa;
            color: #333;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .filter-btn:hover {
            background: #e9ecef;
        }

        .filter-btn.active {
            background: var(--primary-color);
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background 0.3s;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        /* Table Styles */
        .student-table {
            width: 100%;
            background: var(--white);
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-top: 20px;
            overflow: hidden;
        }

        .student-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-table th,
        .student-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .student-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .student-table tr:hover {
            background: #f8f9fa;
        }

        .action-cell {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        .edit-btn {
            background: #3498db;
            color: white;
        }

        .delete-btn {
            background: #e74c3c;
            color: white;
        }

        .action-btn:hover {
            opacity: 0.9;
        }

        .no-records {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-title {
            font-size: 20px;
            color: #333;
        }

        /* Footer Styles */
        .footer {
            background: var(--white);
            padding: 15px;
            text-align: center;
            position: fixed;
            bottom: 0;
            left: 70px;
            right: 0;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            transition: left 0.3s ease;
            z-index: 1000;
        }

        .sidebar:hover + .main-content .footer {
            left: 250px;
        }

        .footer p {
            color: #666;
            margin: 0;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 10px;
            }

            .sidebar h2,
            .sidebar ul li a span {
                display: none;
            }

            .main-content {
                margin-left: 70px;
            }

            .search-box input {
                width: 200px;
            }

            .action-buttons {
                flex-wrap: wrap;
            }

            .footer {
                left: 70px;
            }

            .sidebar:hover + .main-content .footer {
                left: 70px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>Dashboard</h2>
        <ul>
            <li><a href="../../MODELS/dashboard.html"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
            <li><a href="student_view.php"><i class="fas fa-user"></i> <span>Students</span></a></li>
            <li><a href="courses.php"><i class="fas fa-book"></i> <span>Courses</span></a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> <span>Reports</span></a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> <span>Settings</span></a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search students...">
                <i class="fas fa-search"></i>
                <div class="filter-group">
                    <button class="filter-btn" data-sort="name">
                        <i class="fas fa-sort-alpha-down"></i> Name
                    </button>
                    <button class="filter-btn" data-sort="date">
                        <i class="fas fa-calendar-alt"></i> Date
                    </button>
                </div>
            </div>
            <div class="action-buttons">
                <a href="Student_Registration.html" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Student
                </a>
            </div>
        </div>

        <div class="student-table">
            <div class="table-header">
                <h2 class="table-title">Student List</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Contact</th>
                        <th>Gender</th>
                        <th>Blood Group</th>
                        <th>User Type</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<tr data-id="' . $row['id'] . '">
                                <td>' . htmlspecialchars($row['first_name']) . '</td>
                                <td>' . htmlspecialchars($row['last_name']) . '</td>
                                <td>' . htmlspecialchars($row['contact']) . '</td>
                                <td>' . htmlspecialchars($row['gender']) . '</td>
                                <td>' . htmlspecialchars($row['blood_group']) . '</td>
                                <td>' . htmlspecialchars($row['user_type']) . '</td>
                                <td>' . htmlspecialchars($row['email']) . '</td>
                                <td class="action-cell">
                                    <a href="edit_student.php?id=' . $row['id'] . '" class="action-btn edit-btn">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button onclick="deleteStudent(' . $row['id'] . ')" class="action-btn delete-btn">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>';
                        }
                    } else {
                        echo '<tr>
                            <td colspan="8" class="no-records">
                                <i class="fas fa-info-circle"></i> No student records found
                            </td>
                        </tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="footer">
            <p>&copy; 2024 Student Dashboard. All rights reserved.</p>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Search functionality
            function searchStudents() {
                const searchTerm = $('#searchInput').val().toLowerCase();
                $('.student-table tbody tr').each(function() {
                    const text = $(this).text().toLowerCase();
                    $(this).toggle(text.includes(searchTerm));
                });
            }

            // Sort functionality
            let currentSort = {
                column: 'name',
                direction: 'asc'
            };

            function sortTable(column) {
                const tbody = $('.student-table tbody');
                const rows = tbody.find('tr').toArray();

                rows.sort((a, b) => {
                    let aVal, bVal;

                    if (column === 'name') {
                        aVal = $(a).find('td:first').text();
                        bVal = $(b).find('td:first').text();
                    } else if (column === 'date') {
                        aVal = new Date($(a).find('td:nth-child(3)').text());
                        bVal = new Date($(b).find('td:nth-child(3)').text());
                    }

                    if (currentSort.direction === 'asc') {
                        return aVal > bVal ? 1 : -1;
                    } else {
                        return aVal < bVal ? 1 : -1;
                    }
                });

                tbody.empty().append(rows);
            }

            // Event listeners
            $('#searchInput').on('keyup', searchStudents);

            $('.filter-btn').on('click', function() {
                const column = $(this).data('sort');
                
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');

                if (currentSort.column === column) {
                    currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSort.column = column;
                    currentSort.direction = 'asc';
                }

                sortTable(column);
            });

            // Delete student functionality
            window.deleteStudent = function(id) {
                if (confirm('Are you sure you want to delete this student?')) {
                    $.ajax({
                        url: '../../CONTROLLAR/process/delete_student.php',
                        type: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                $(`tr[data-id="${id}"]`).fadeOut(400, function() {
                                    $(this).remove();
                                    if ($('.student-table tbody tr').length === 0) {
                                        $('.student-table tbody').html(`
                                            <tr>
                                                <td colspan="8" class="no-records">
                                                    <i class="fas fa-info-circle"></i> No student records found
                                                </td>
                                            </tr>
                                        `);
                                    }
                                });
                            } else {
                                alert(response.message);
                            }
                        },
                        error: function() {
                            alert('An error occurred while deleting the student.');
                        }
                    });
                }
            };
        });
    </script>
</body>
</html>
