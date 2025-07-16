<?php
session_start();
include 'config.php';

// ✅ Only allow access if admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// ✅ Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_type'])) {
    $userId = $_POST['user_id'];
    $newType = $_POST['new_user_type'];

    $validTypes = ['admin', 'staff', 'student'];
    if (in_array($newType, $validTypes)) {
        $stmt = $conn->prepare("UPDATE users SET user_type = ? WHERE id = ?");
        $stmt->execute([$newType, $userId]);
        $message = "User role updated successfully!";
    } else {
        $message = "Invalid role selected.";
    }
}

// ✅ Exclude the currently logged-in user from the users list
$currentUserId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, name, email, user_type FROM users WHERE id != ?");
$stmt->execute([$currentUserId]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
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
            max-width: 1000px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            margin-bottom: 30px;
            font-size: 28px;
            color: #2c3e50;
        }

        .message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }

        table th {
            background-color: #f4f6f8;
            color: #2c3e50;
        }

        select {
            padding: 5px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #3498db;
            border: none;
            color: white;
            padding: 8px 14px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #2980b9;
        }

        form {
            margin: 0;
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
        <a href="users.php">Users</a>
    </div>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="container">
    <h1>Manage Registered Users</h1>

    <?php if (isset($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>User Type</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <form method="POST" action="users.php">
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <select name="new_user_type">
                                <option value="student" <?= $user['user_type'] == 'student' ? 'selected' : '' ?>>Student</option>
                                <option value="staff" <?= $user['user_type'] == 'staff' ? 'selected' : '' ?>>Staff</option>
                                <option value="admin" <?= $user['user_type'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </td>
                        <td>
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button type="submit" name="update_user_type">Update</button>
                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
