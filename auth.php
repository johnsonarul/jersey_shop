<?php
// auth.php
session_start();

function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirectIfNotLoggedIn() {
    if (!isUserLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function redirectIfNotAdmin() {
    if (!isAdminLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}
?>
