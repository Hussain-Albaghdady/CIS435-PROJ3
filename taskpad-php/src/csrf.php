<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate a CSRF token and store in session to protec and make site data more unique
 * @return string The token
 */


function generateCsrfToken() {
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Get the current CSRF token
 * @return string|null The token or null
 */
function getCsrfToken() {
    return $_SESSION['csrf_token'] ?? null;
}

/**
 * 
 * @param string
 * @return bool 
 */
function verifyCsrfToken($token) {
    $sessionToken = getCsrfToken();
    
    return !empty($sessionToken) && !empty($token) && hash_equals($sessionToken, $token);
}

/**
 * 
 * @return string 
 */
function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}


function requireCsrfToken() {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Invalid CSRF token. Please refresh the page and try again.');
    }
}