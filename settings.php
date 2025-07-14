<?php
session_start();

// Protect this page so only logged-in users can access
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Optional: You can customize settings based on role
$userType = $_SESSION['user_type'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f2f2f2;
        }

        .navbar {
            background-color: #333;
            overflow: hidden;
            padding: 10px 20px;
        }

        .navbar a {
            float: left;
            color: white;
            text-align: center;
            padding: 10px 16px;
            text-decoration: none;
        }

        .navbar a.logout {
            float: right;
            background-color: #d9534f;
        }

        .navbar a:hover {
            background-color: #575757;
        }

        .navbar a.logout:hover {
            background-color: #c9302c;
        }

        .container {
            padding: 40px;
            text-align: center;
        }

        h1 {
            color: #333;
        }

        .settings-box {
            background-color: white;
            border-radius: 10px;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .settings-box p {
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="navbar">
    <a href="admin_page.php">Admin Page</a>
    <a href="staff_page.php">Staff Page</a>
    <a href="student_page.php">Student Page</a>
    <a href="index.php">Dashboard</a>
    <a href="settings.php">Settings</a>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="container">
    <div class="settings-box">
        <h1>Settings</h1>
        <p><strong>User Type:</strong> <?php echo htmlspecialchars($userType); ?></p>
        <!-- Add more settings options as needed -->
    </div>
</div>

</body>
</html>
