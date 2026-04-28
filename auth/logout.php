<?php
require_once '../includes/config.php';

session_unset();
session_destroy();

header("Location: " . BASE_URL . "/auth/login.php");
exit();
?>
