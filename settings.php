<?php
session_start();
include 'config.php'; // Make sure this provides $conn as a PDO connection

// Protect this page so only logged-in users can access
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Auto-logout if user_type changed in the database
if (isset($_SESSION['user_id'], $_SESSION['user_type'])) {
    $stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['user_type'] !== $_SESSION['user_type']) {
        session_unset();
        session_destroy();
        header("Location: login.php?message=Role changed. Please log in again.");
        exit;
    }
}

// Still show role after checking
$userType = $_SESSION['user_type'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9fafb;
            color: #333;
        }

        .navbar {
            background-color: #2c3e50;
            padding: 10px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .navbar a {
            color: #ecf0f1;
            margin-right: 20px;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
        }

        .navbar a:hover {
            background-color: #34495e;
        }

        .navbar .logout {
            background-color: #e74c3c;
        }

        .navbar .logout:hover {
            background-color: #c0392b;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .info {
            font-size: 18px;
            margin-top: 20px;
        }

        .info strong {
            color: #34495e;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div>
        <a href="admin_page.php">Admin Page</a>
        <a href="staff_page.php">Staff Page</a>
        <a href="student_page.php">Student Page</a>
        <a href="index.php">Dashboard</a>
        <a href="settings.php">Settings</a>
    </div>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="container">
    <h1>Settings</h1>
    <div class="info">
        <p><strong>User Type:</strong> <?= htmlspecialchars($userType); ?></p>
    </div>
</div>

</body>
</html>
