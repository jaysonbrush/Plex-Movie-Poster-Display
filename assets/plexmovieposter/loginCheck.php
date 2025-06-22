<?php
/**
 * PMPD Login Check - Enhanced with session timeout
 */
include_once(__DIR__ . '/SecurityLib.php');

// Initialize session with timeout check
if (!pmpd_session_init()) {
    // Session expired - redirect to login
    header("Location: /settings/login.php?expired=1");
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['username']) || $_SESSION['username'] === null) {
    header("Location: /settings/login.php");
    exit();
}
?>
