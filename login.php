<?php
include 'config.php';

// Define constants for brute force protection
define('MAX_ATTEMPTS', 3);
define('LOCKOUT_MINUTES', 5);
define('DELAY_AFTER_FAIL', 10);

// reCAPTCHA secret key
$recaptchaSecretKey = '6LdRUIIrAAAAAIOtK6A_ip3THevf4iHI67kPMZQP'; // Replace this with your secret key from Google

class User {
    private $conn;  

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateFailedLogin($email, $attempts, $lockoutTime = null) {
        $stmt = $this->conn->prepare("UPDATE users SET failed_attempts = ?, lockout_time = ? WHERE email = ?");
        $stmt->execute([$attempts, $lockoutTime, $email]);
    }

    public function resetLoginAttempts($email) {
        $stmt = $this->conn->prepare("UPDATE users SET failed_attempts = 0, lockout_time = NULL WHERE email = ?");
        $stmt->execute([$email]);
    }
}

class SessionManager {
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function setSession($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
    }

    public static function redirect($location) {
        header("Location: $location");
        exit;
    }

    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

SessionManager::startSession();

if (SessionManager::isLoggedIn() && !isset($_GET['from'])) {
    $type = $_SESSION['user_type'];
    if ($type === 'admin') {
        SessionManager::redirect('admin_page.php');
    } elseif ($type === 'staff') {
        SessionManager::redirect('staff_page.php');
    } elseif ($type === 'student') {
        SessionManager::redirect('student_page.php');
    }
}

if (isset($_POST['submit'])) {
    if (!SessionManager::validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $message[] = 'Invalid CSRF token!';
    } else {
        // Verify reCAPTCHA
        $captcha = $_POST['g-recaptcha-response'] ?? '';
        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptchaSecretKey}&response={$captcha}");
        $captchaSuccess = json_decode($verify);

        if (!$captchaSuccess->success) {
            $message[] = 'Captcha verification failed!';
        } else {
            $email = trim($_POST['email']);
            $password = $_POST['pass'];

            $user = new User($conn);
            $userData = $user->getUserByEmail($email);

            if ($userData) {
                $now = new DateTime();

                if ($userData['lockout_time'] && $now < new DateTime($userData['lockout_time'])) {
                    $message[] = 'Account is locked. Try again later.';
                } else {
                    if (password_verify($password, $userData['password'])) {
                        $user->resetLoginAttempts($email);
                        SessionManager::setSession('user_id', $userData['id']);
                        SessionManager::setSession('user_type', $userData['user_type']);

                        if ($userData['user_type'] == 'admin') {
                            SessionManager::redirect('admin_page.php');
                        } elseif ($userData['user_type'] == 'staff') {
                            SessionManager::redirect('staff_page.php');
                        } elseif ($userData['user_type'] == 'student') {
                            SessionManager::redirect('student_page.php');
                        } else {
                            $message[] = 'Invalid user type!';
                        }
                    } else {
                        sleep(DELAY_AFTER_FAIL);
                        $attempts = $userData['failed_attempts'] + 1;
                        if ($attempts >= MAX_ATTEMPTS) {
                            $lockoutTime = $now->modify("+" . LOCKOUT_MINUTES . " minutes")->format('Y-m-d H:i:s');
                            $user->updateFailedLogin($email, $attempts, $lockoutTime);
                            $message[] = 'Too many failed attempts. Account is locked for ' . LOCKOUT_MINUTES . ' minutes.';
                        } else {
                            $user->updateFailedLogin($email, $attempts);
                            $remaining = MAX_ATTEMPTS - $attempts;
                            $message[] = "Incorrect password! {$remaining} attempt(s) remaining.";
                        }
                    }
                }
            } else {
                $message[] = 'Email not found!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

<?php
if (isset($message)) {
    foreach ($message as $msg) {
        echo '<div class="message"><span>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
    }
}
?>

<section class="form-container">
    <form action="" method="post">
        <h3>Login Now</h3>

        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(SessionManager::generateCSRFToken(), ENT_QUOTES, 'UTF-8'); ?>">

        <input type="email" required placeholder="Enter your email" class="box" name="email">
        <input type="password" required placeholder="Enter your password" class="box" name="pass">
        <div class="g-recaptcha" data-sitekey="6LdRUIIrAAAAAKEya7i2BvK6mJgBa59xzOR4QtBu"></div>
        <p>Don't have an account? <a href="register.php">Register now</a></p>
        <input type="submit" value="Login now" class="btn" name="submit">
    </form>
</section>

<script>
    document.querySelector("form").addEventListener("submit", function (e) {
        const email = document.querySelector("input[name='email']").value;
        const pass = document.querySelector("input[name='pass']").value;

        if (!email.includes('@')) {
            alert("Invalid email address.");
            e.preventDefault();
        }

        if (pass.length < 8) {
            alert("Password must be at least 8 characters.");
            e.preventDefault();
        }
    });
</script>

</body>
</html>
