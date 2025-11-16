<?php



if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set a flash message like "success" or "error" and so this flash is to make sure it gives the user feedback 
 * @param string 
 * @param string 
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
 * @param string 
 * @return bool 
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