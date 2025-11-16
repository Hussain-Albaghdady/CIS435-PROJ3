<?php

// Include helpers
require_once '../src/storage.php';
require_once '../src/validation.php';
require_once '../src/csrf.php';
require_once '../src/flash.php';

// Start session but this is needed so we dont have mor then once instance of session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    die('Only POST requests are allowed');
}

// Verify CSRF token
requireCsrfToken();

// Get action and task ID
$action = $_POST['action'] ?? '';
$taskId = $_POST['id'] ?? '';

// see if we hae the data or not 
if (empty($action) || empty($taskId)) {
    setFlash('errorr', 'Invalid request');
    header('Location: index.php');
    exit;
}


switch ($action) {
    case 'complete':
        // Mark task as complete
        if (updateTask($taskId, ['completed' => true])) {
            setFlash('success', 'Task marked as complete!');
        } else {
            setFlash('error', 'Failed to complete task');
        }
        break;
        
    case 'delete':
        // Delete the task
        if (deleteTask($taskId)) {
            setFlash('success', 'Task deleted successfully!');
        } else {
            setFlash('error', 'Failed to delete task');
        }
        break;
        
    default:
        setFlash('error', 'Invalid action');
}

// Redirect back to list page
header('Location: index.php');
exit;