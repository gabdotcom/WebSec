<?php
session_start();
include 'config.php';

// Only allow access if admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle update
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

// Fetch all users
$stmt = $conn->query("SELECT id, name, email, user_type FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users Management</title>
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
        }
        h1 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 12px;
            text-align: center;
        }
        select, button {
            padding: 6px;
        }
        .message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            width: fit-content;
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
    <a href="users.php">Users</a>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="container">
    <h1>Manage Users</h1>

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
