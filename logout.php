<?php
session_start();
session_unset();
session_destroy();
session_start(); // Restart session for flash message
$_SESSION['flash'] = [
    'type' => 'success',
    'message' => 'You have been successfully logged out.'
];
header("Location: login.php");
exit();
?>