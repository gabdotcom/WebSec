<?php

include 'config.php';

session_start();

class User
{
    private $conn;
    private $userId;

    public function __construct($conn, $userId)
    {
        $this->conn = $conn;
        $this->userId = $userId;
    }

    public function getUserDetails()
    {
        $stmt = $this->conn->prepare("SELECT * FROM `users` WHERE id = ?");
        $stmt->execute([$this->userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUser($name, $email, $password, $image)
    {
        $updateQuery = "UPDATE `users` SET name = ?, email = ?";
        $params = [$name, $email];

        if (!empty($password)) {
            $updateQuery .= ", password = ?";
            $params[] = md5($password);
        }

        if (!empty($image['name'])) {
            $imagePath = 'uploaded_img/' . $image['name'];
            if ($image['size'] > 2000000) {
                throw new Exception('Image size is too large!');
            }
            move_uploaded_file($image['tmp_name'], $imagePath);
            $updateQuery .= ", image = ?";
            $params[] = $image['name'];
        }

        $updateQuery .= " WHERE id = ?";
        $params[] = $this->userId;

        $stmt = $this->conn->prepare($updateQuery);
        $stmt->execute($params);
    }
}

if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$userObj = new User($conn, $userId);

$user = $userObj->getUserDetails();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['pass'] === $_POST['cpass'] ? $_POST['pass'] : null;

        if ($_POST['pass'] !== $_POST['cpass']) {
            throw new Exception('Confirm password does not match!');
        }

        $userObj->updateUser($name, $email, $password, $_FILES['image']);
        $message[] = 'Profile updated successfully!';
        header('location:update_profile.php');
        exit();
    } catch (Exception $e) {
        $message[] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php 
if (isset($message)) {
    foreach ($message as $msg) {
        echo '
        <div class="message">
            <span>' . $msg . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>';
    }
}
?>

<section class="form-container">
    <form action="" method="post" enctype="multipart/form-data">
        <h3>Update Your Profile</h3>
        <div class="profile_picture_preview_container">
            <img class="profile_picture_preview" src="uploaded_img/<?php echo isset($user['image']) ? $user['image'] : "sample_profile.webp"; ?>">
        </div>
        <input type="text" value="<?php echo htmlspecialchars($user['faculty_id']); ?>" class="box" name="faculty_id" readonly>
        <input type="text" required placeholder="Enter your name" class="box" name="name" value="<?php echo htmlspecialchars($user['name']); ?>"> 
        <input type="email" required placeholder="Enter your email" class="box" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"> 
        <input type="password" placeholder="Enter your new password" class="box" name="pass"> 
        <input type="password" placeholder="Confirm your new password" class="box" name="cpass"> 
        <input type="file" name="image" class="box file_input" accept="image/jpg, image/png, image/jpeg">
        <p>Don't want to change your image? Leave it blank.</p>
        <a href="index.php" class="btn">Back</a>
        <input type="submit" value="Update Profile" class="btn">
    </form>
</section>

<script>
document.querySelector('.file_input').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('.profile_picture_preview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>
