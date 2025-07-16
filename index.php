<?php
session_start();
include 'config.php';

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

// ✅ Auto-logout if user_type changed in database
if (isset($_SESSION['user_id'], $_SESSION['user_type'])) {
    $stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || $user['user_type'] !== $_SESSION['user_type']) {
        session_unset();
        session_destroy();
        header("Location: login.php?message=Role changed. Please log in again.");
        exit();
    }
}

// Session timeout logic
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login.php?message=Session expired. Please log in again.");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time
