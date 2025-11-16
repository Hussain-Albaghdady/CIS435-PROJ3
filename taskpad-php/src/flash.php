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
 * 
 * @param string 
 * @return string|null T
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
 * 
 * @param string 
 * @return bool 
 */
function hasFlash($key) {
    return isset($_SESSION['flash'][$key]);
}

/**
 * 
 * @return string 
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