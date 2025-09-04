<?php

/**
 * Generates a CSRF token and stores it in the session.
 * If a token already exists, it will not be overwritten.
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

/**
 * Returns an HTML hidden input field with the CSRF token.
 *
 * @return string
 */
function csrf_input() {
    if (empty($_SESSION['csrf_token'])) {
        return ''; // Or handle error appropriately
    }
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}

/**
 * Verifies the submitted CSRF token.
 * If the token is invalid, it terminates the script.
 */
function verify_csrf_token() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // Token is not valid, handle the error.
        // For simplicity, we'll just kill the script.
        // In a real app, you might want to show a proper error page.
        die('Invalid CSRF token.');
    }
}

?>
