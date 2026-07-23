<?php
// filepath: c:\xampp\htdocs\hayvan_sitem\includes\functions.php

// Utility functions for the Hayvan Sitem application

/**
 * Sanitize user input to prevent XSS attacks
 *
 * @param string $data
 * @return string
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a specified URL
 *
 * @param string $url
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Check if the user is logged in
 *
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get the current user's ID
 *
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Flash message for one-time use
 *
 * @param string $message
 */
function flashMessage($message) {
    $_SESSION['flash_message'] = $message;
}

/**
 * Display flash message
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        echo '<div class="alert alert-success">' . $_SESSION['flash_message'] . '</div>';
        unset($_SESSION['flash_message']);
    }
}
?>