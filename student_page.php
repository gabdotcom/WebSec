<?php
session_start();
include 'config.php'; // Ensure this defines $conn as a PDO instance

// Protect this page so only student, staff, or admin can access
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['student', 'staff', 'admin'])) {
    header('Location: login.php');
    exit;
}

// ✅ Auto-logout if user_type changed in the database
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Page</title>
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
            max-width: 900px;
            margin: 60px auto;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            font-size: 32px;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        p {
            font-size: 16px;
            color: #555;
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
    <h1>Welcome, Student!</h1>
</div>

</body>
</html>
