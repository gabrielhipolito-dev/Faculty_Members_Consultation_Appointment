<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login first");
    exit();
}
?>
