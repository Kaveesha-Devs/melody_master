<?php
// Authentication and role management functions

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function get_current_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

function get_current_user_role()
{
    return $_SESSION['role'] ?? 'guest';
}

function is_admin()
{
    return get_current_user_role() === 'admin';
}

function is_staff()
{
    return get_current_user_role() === 'staff' || is_admin();
}

function require_login()
{
    if (!is_logged_in()) {
        $base_url = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'admin') ? '../' : '';
        header("Location: " . $base_url . "login.php");
        exit();
    }
}

function require_admin()
{
    require_login();
    if (!is_admin()) {
        die("Access denied: Admin privileges required.");
    }
}

function require_staff()
{
    require_login();
    if (!is_staff()) {
        die("Access denied: Staff privileges required.");
    }
}
?>