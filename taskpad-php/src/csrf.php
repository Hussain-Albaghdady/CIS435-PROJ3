<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate a CSRF token and store in session to protec and make site data more unique
 * @return string The token
 */

// generates it 
function generateCsrfToken() {
    // Generate random token if doesn't exist
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
 * Verify a CSRF token matches the session token
 * @param string $token Token to verify
 * @return bool True if valid
 */
function verifyCsrfToken($token) {
    $sessionToken = getCsrfToken();
    
    // Both must exist and match
    return !empty($sessionToken) && !empty($token) && hash_equals($sessionToken, $token);
}

/**
 * Generate HTML for hidden CSRF field
 * @return string HTML input field
 */
function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Check CSRF token from POST request and die if invalid
 */
function requireCsrfToken() {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Invalid CSRF token. Please refresh the page and try again.');
    }
}