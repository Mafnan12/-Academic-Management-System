<?php
session_start();

// Dynamically define BASE_URL to work everywhere
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_dirname = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$script_dirname = str_replace(array('/auth', '/pages', '/includes'), '', $script_dirname); // adjust if accessed from subfolder
define('BASE_URL', $protocol . $host . $script_dirname);

// Error Reporting (Development Mode)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Reusable function to check if user is logged in
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "/auth/login.php");
        exit();
    }
}

// Function to check if user is admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Function to check if user is instructor
function is_instructor() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'instructor';
}

// Function to check if user is student
function is_student() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

// Function to check if user is parent
function is_parent() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'parent';
}

// Function to check role permissions
function has_permission($required_role) {
    $user_role = $_SESSION['role'] ?? '';
    $role_hierarchy = ['parent' => 1, 'student' => 2, 'instructor' => 3, 'admin' => 4];
    return ($role_hierarchy[$user_role] ?? 0) >= ($role_hierarchy[$required_role] ?? 0);
}

// Flash messages function
function set_flash_message($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function display_flash_messages() {
    if (isset($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $message) {
            $color = $type === 'success' ? 'bg-emerald-100 text-emerald-800 border-emerald-500' : 'bg-rose-100 text-rose-800 border-rose-500';
            echo "<div class='p-4 mb-4 text-sm rounded-lg border $color' role='alert'>$message</div>";
        }
        unset($_SESSION['flash']);
    }
}
?>
