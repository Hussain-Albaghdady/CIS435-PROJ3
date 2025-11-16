<?php


/**
 * Validate task data
 * @param array 
 * @return array 
 */
function validateTask($data) {
    $errors = [];
    
  
    if (empty($data['title']) || trim($data['title']) === '') {
        $errors['title'] = 'Title is required';
    } elseif (strlen($data['title']) > 200) {
        $errors['title'] = 'Title must be less than 200 characters';
    }
    
   
    if (!empty($data['description']) && strlen($data['description']) > 1000) {
        $errors['description'] = 'Description must be less than 1000 characters';
    }
    
    
    $validPriorities = ['Low', 'Medium', 'High'];
    if (empty($data['priority']) || !in_array($data['priority'], $validPriorities)) {
        $errors['priority'] = 'Priority must be Low, Medium, or High';
    }
    
   
    if (!empty($data['due'])) {
      
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['due'])) {
            $errors['due'] = 'Due date must be in YYYY-MM-DD format';
        } else {
          
            $parts = explode('-', $data['due']);
            if (!checkdate($parts[1], $parts[2], $parts[0])) {
                $errors['due'] = 'Due date is not a valid date';
            }
        }
    }
    
    return $errors;
}

/**
 * 
 * @param array
 * @return array a
 */
function FormatTask($data) {
    return [
        'title' => trim(htmlspecialchars($data['title'] ?? '', ENT_QUOTES, 'UTF-8')),
        'description' => trim(htmlspecialchars($data['description'] ?? '', ENT_QUOTES, 'UTF-8')),
        'priority' => $data['priority'] ?? 'Medium',
        'due' => !empty($data['due']) ? $data['due'] : null
    ];
}

/**
 * 
 * @param string 
 * @return string 
 */
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 *
 * @param string 
 * @return string 
 */
function sanitizeQuery($query) {
    return trim(htmlspecialchars($query, ENT_QUOTES, 'UTF-8'));
}

/**
 * r
 * @param string 
 * @return string|null 
 */
function validatePriority($priority) {
    $valid = ['Low', 'Medium', 'High'];
    return in_array($priority, $valid) ? $priority : null;
}