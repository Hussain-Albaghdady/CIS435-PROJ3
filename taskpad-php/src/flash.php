<?php
/**
 * FLASH.PHP - Session Flash Messages
 * 
 * WHY? After submitting a form, we redirect the user (PRG pattern).
 * But we want to show them a success message. Flash messages let us
 * store a message that shows once and then disappears.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set a flash message
 * @param string $key Message key (e.g., 'success', 'error')
 * @param string $message The message text
 */
function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

/**
 * Get a flash message (and remove it)
 * @param string $key Message key
 * @return string|null The message or null
 */
function getFlash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

/**
 * Check if a flash message exists
 * @param string $key Message key
 * @return bool True if exists
 */
function hasFlash($key) {
    return isset($_SESSION['flash'][$key]);
}

/**
 * Display all flash messages as HTML
 * @return string HTML for flash messages
 */
function displayFlash() {
    $html = '';
    
    if (isset($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $message) {
            $class = $type === 'error' ? 'error' : 'success';
            $html .= '<div class="flash-message ' . $class . '">' . e($message) . '</div>';
            unset($_SESSION['flash'][$type]);
        }
    }
    
    return $html;
}