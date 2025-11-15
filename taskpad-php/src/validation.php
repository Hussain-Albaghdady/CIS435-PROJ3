<?php
/**
 * VALIDATION.PHP - Validates and sanitizes user inputs
 * 
 * WHY? Users can type anything. We need to check inputs are valid
 * and clean them up to prevent security issues (like XSS attacks)
 */

/**
 * Validate task data
 * @param array $data Task data from form
 * @return array Errors (empty if valid)
 */
function validateTask($data) {
    $errors = [];
    
    // Check title exists and is not empty
    if (empty($data['title']) || trim($data['title']) === '') {
        $errors['title'] = 'Title is required';
    } elseif (strlen($data['title']) > 200) {
        $errors['title'] = 'Title must be less than 200 characters';
    }
    
    // Check description length if provided
    if (!empty($data['description']) && strlen($data['description']) > 1000) {
        $errors['description'] = 'Description must be less than 1000 characters';
    }
    
    // Check priority is valid
    $validPriorities = ['Low', 'Medium', 'High'];
    if (empty($data['priority']) || !in_array($data['priority'], $validPriorities)) {
        $errors['priority'] = 'Priority must be Low, Medium, or High';
    }
    
    // Check due date format if provided
    if (!empty($data['due'])) {
        // Regex to check YYYY-MM-DD format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['due'])) {
            $errors['due'] = 'Due date must be in YYYY-MM-DD format';
        } else {
            // Also check if it's a valid date
            $parts = explode('-', $data['due']);
            if (!checkdate($parts[1], $parts[2], $parts[0])) {
                $errors['due'] = 'Due date is not a valid date';
            }
        }
    }
    
    return $errors;
}

/**
 * Sanitize task data (clean it up)
 * @param array $data Raw task data
 * @return array Cleaned task data
 */
function sanitizeTask($data) {
    return [
        'title' => trim(htmlspecialchars($data['title'] ?? '', ENT_QUOTES, 'UTF-8')),
        'description' => trim(htmlspecialchars($data['description'] ?? '', ENT_QUOTES, 'UTF-8')),
        'priority' => $data['priority'] ?? 'Medium',
        'due' => !empty($data['due']) ? $data['due'] : null
    ];
}

/**
 * Escape output for HTML (prevents XSS attacks)
 * @param string $text Text to escape
 * @return string Escaped text
 */
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Validate and sanitize search query
 * @param string $query Search query
 * @return string Clean query
 */
function sanitizeQuery($query) {
    return trim(htmlspecialchars($query, ENT_QUOTES, 'UTF-8'));
}

/**
 * Validate priority filter
 * @param string $priority Priority value
 * @return string|null Valid priority or null
 */
function validatePriority($priority) {
    $valid = ['Low', 'Medium', 'High'];
    return in_array($priority, $valid) ? $priority : null;
}