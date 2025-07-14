<?php
session_start();

// Set timeout duration in seconds (2 minutes = 120 seconds)
$timeout_duration = 120;

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Session timeout logic
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?message=Session expired. Please log in again.");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time

class Database {
    private $conn;

    public function __construct($host, $username, $password, $dbname) {
        $this->conn = new mysqli($host, $username, $password, $dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function query($sql) {
        return $this->conn->query($sql);
    }

    public function close() {
        $this->conn->close();
    }
}

class Student {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addStudent($student_name, $school_id, $course, $year) {
        $sql = "INSERT INTO students (student_name, school_id, course, year) 
                VALUES ('$student_name', '$school_id', '$course', '$year')";
        return $this->db->query($sql);
    }

    public function editStudent($id, $student_name, $school_id, $course, $year) {
        $sql = "UPDATE students SET student_name='$student_name', school_id='$school_id', course='$course', year='$year' WHERE id=$id";
        return $this->db->query($sql);
    }

    public function deleteStudent($id) {
        $sql = "DELETE FROM students WHERE id=$id";
        return $this->db->query($sql);
    }

    public function getAllStudents() {
        return $this->db->query("SELECT * FROM students");
    }
}

$db = new Database("localhost", "root", "", "neil_mid");
$student = new Student($db);

if (isset($_POST['add'])) {
    $student_name = $_POST['student_name'];
    $school_id = $_POST['school_id'];
    $course = $_POST['course'];
    $year = $_POST['year'];

    if ($student->addStudent($student_name, $school_id, $course, $year)) {
        header("Location: index.php");
    } else {
        echo "Error adding student.";
    }
}

if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $student_name = $_POST['student_name'];
    $school_id = $_POST['school_id'];
    $course = $_POST['course'];
    $year = $_POST['year'];

    if ($student->editStudent($id, $student_name, $school_id, $course, $year)) {
        header("Location: index.php");
    } else {
        echo "Error updating student.";
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($student->deleteStudent($id)) {
        header("Location: index.php");
    } else {
        echo "Error deleting student.";
    }
}

$students_result = $student->getAllStudents();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document Request System</title>
    <link rel="stylesheet" href="css/s.css">
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
            max-width: 900px;
            margin: auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            color: #333;
            margin-top: 20px;
        }

        form input, form button {
            display: block;
            width: 100%;
            padding: 8px;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            text-align: center;
            padding: 12px;
        }

        th {
            background-color: #333;
            color: white;
        }

        .new button {
            margin-top: 10px;
            padding: 8px 15px;
            background-color: #333;
            color: white;
            border: none;
            cursor: pointer;
            margin-right: 10px;
        }

        .new button:hover {
            background-color: #555;
        }

        .new {
            text-align: center;
            margin-top: 30px;
        }

        a {
            color: #007BFF;
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
    <h2>Document Request</h2>
    <form action="" method="POST">
        <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>">
        <input type="text" name="student_name" placeholder="Resident Name" value="<?php echo isset($_GET['student_name']) ? $_GET['student_name'] : ''; ?>" required>
        <input type="text" name="school_id" placeholder="Purok" value="<?php echo isset($_GET['school_id']) ? $_GET['school_id'] : ''; ?>" required>
        <input type="text" name="course" placeholder="Type of Document" value="<?php echo isset($_GET['course']) ? $_GET['course'] : ''; ?>" required>
        <input type="number" name="year" placeholder="Copies" value="<?php echo isset($_GET['year']) ? $_GET['year'] : ''; ?>" required>
        
        <?php if (isset($_GET['edit'])): ?>
            <button type="submit" name="edit">Update</button>
        <?php else: ?>
            <button type="submit" name="add">Add</button>
        <?php endif; ?>
    </form>

    <h2>Your Request</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Resident Name</th>
                <th>Purok</th>
                <th>Type of Document</th>
                <th>Copies</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $students_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['student_name']; ?></td>
                    <td><?php echo $row['school_id']; ?></td>
                    <td><?php echo $row['course']; ?></td>
                    <td><?php echo $row['year']; ?></td>
                    <td>
                        <a href="index.php?edit=<?php echo $row['id']; ?>&id=<?php echo $row['id']; ?>&student_name=<?php echo urlencode($row['student_name']); ?>&school_id=<?php echo $row['school_id']; ?>&course=<?php echo urlencode($row['course']); ?>&year=<?php echo $row['year']; ?>">Edit</a>
                        |
                        <a href="index.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="new">
        <form action="logout.php" method="POST">
            <button type="submit">Logout</button>
            <button type="button" onclick="location.href='update_profile.php'">Update Profile</button>
        </form>
    </div>
</div>

</body>
</html>

<?php $db->close(); ?>
