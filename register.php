<?php
include 'config.php';

class UserRegistration
{
    private $conn;
    private $faculty_id;
    private $encryption_key = 'a1b2c3d4e5f60718293a4b5c6d7e8f90';
    private $encryption_iv = '1234567890abcdef';       
    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->faculty_id = rand(10000000, 99999999);
    }

    public function registerUser($data)
    {
        $faculty_id = $this->faculty_id;

        // Sanitize inputs using safer alternatives
        $name = htmlspecialchars(trim($data['name']), ENT_QUOTES, 'UTF-8');
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $user_type = isset($data['user_type']) ? $data['user_type'] : 'student';

        if (!in_array($user_type, ['admin', 'staff', 'student'])) {
            return ['Invalid user type selected!'];
        }

        $pass_raw = htmlspecialchars(trim($data['pass']), ENT_QUOTES, 'UTF-8');
        $cpass_raw = htmlspecialchars(trim($data['cpass']), ENT_QUOTES, 'UTF-8');

        if ($pass_raw !== $cpass_raw) {
            return ['Confirm password does not match!'];
        }

        // Hash the password securely
        $hashedPassword = password_hash($pass_raw, PASSWORD_DEFAULT);

        // Check if email is already in use
        if ($this->isUserExists($email)) {
            return ['User already exists!'];
        }

        // Encryp using AES-256-CBC
        $encryptedName = $this->encrypt($name);

        // Save user data into the database
        if ($this->saveUser($faculty_id, $encryptedName, $email, $hashedPassword, $user_type)) {
            session_start();
            session_unset();
            session_destroy();

            header('Location: login.php?from=register');
            exit;
        }

        return ['Registration failed!'];
    }

    // This method performs encryption of the name
    private function encrypt($plainText)
    {
        return openssl_encrypt($plainText, 'AES-256-CBC', $this->encryption_key, 0, $this->encryption_iv);
    }

    private function isUserExists($email)
    {
        $select = $this->conn->prepare("SELECT * FROM `users` WHERE email = ?");
        $select->execute([$email]);
        return $select->rowCount() > 0;
    }

    private function saveUser($faculty_id, $encryptedName, $email, $password, $user_type)
    {
        $insert = $this->conn->prepare(
            "INSERT INTO `users` (faculty_id, name, email, password, user_type) VALUES (?, ?, ?, ?, ?)"
        );
        return $insert->execute([$faculty_id, $encryptedName, $email, $password, $user_type]);
    }
}

$messages = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $userRegistration = new UserRegistration($conn);
    $messages = $userRegistration->registerUser($_POST);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php 
if (!empty($messages)) {
    foreach ($messages as $message) {
        echo '
        <div class="message">
        <span>' . htmlspecialchars($message) . '</span>
        <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>';
    }
}
?>

<section class="form-container">
    <form action="" method="post">
        <h3>Register</h3>
        <input type="text" required placeholder="Enter your name" class="box" name="name"> 
        <input type="email" required placeholder="Enter your email" class="box" name="email"> 
        <input type="password" required placeholder="Enter your password" class="box" name="pass"> 
        <input type="password" required placeholder="Confirm your password" class="box" name="cpass"> 

        <select name="user_type" required class="box">
            <option value="" disabled selected>Select user type</option>
            <option value="student">Student</option>
            <option value="staff">Staff</option>
            <option value="admin">Admin</option>
        </select>

        <p>Already have an account? <a href="login.php?from=register">Login now</a></p>
        <input type="submit" value="Register Now" class="btn" name="submit">
    </form>
</section>

</body>
</html>
