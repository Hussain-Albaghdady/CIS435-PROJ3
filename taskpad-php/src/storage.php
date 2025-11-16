<?php

define('TASKS_FILE', __DIR__ . '/../data/tasks.json');

/**
 * Load all tasks from the JSON file and puts in storage
 * @return array All tasks
 */

function loadTasks() {
    // Check if file exists will load into index
    if (!file_exists(TASKS_FILE)) {
        // If not, return empty array
        return [];
    }
    
  
    $json = file_get_contents(TASKS_FILE);
    
    // Convert JSON string to array
    $tasks = json_decode($json, true);
    
    // Return tasks or empty array if something went wrong
    return $tasks ?: [];
}

/**
 * Save tasks to the JSON file
 * @param array
 * @return bool l
 */
function saveTasks($tasks) {
    
    $json = json_encode($tasks, JSON_PRETTY_PRINT);
    
   
    return file_put_contents(TASKS_FILE, $json) !== false;
}

/**
 * Get a single task by ID
 * @param string 
 * @return array|null 
 */
function getTaskById($id) {
    $tasks = loadTasks();
    
    // Loop through tasks to find matching ID
    foreach ($tasks as $task) {
        if ($task['id'] === $id) {
            return $task;
        }
    }
    
    return null;
}

/**
 * Add a new task
 * @param array 
 * @return bool 
 */
function addTask($taskData) {
    $tasks = loadTasks();
    
    // Generate unique ID (timestamp + random number)
    $taskData['id'] = time() . '_' . rand(1000, 9999);
    
    // Set completed to false by default
    $taskData['completed'] = false;
    
    // Add to array
    $tasks[] = $taskData;
    
    // Save
    return saveTasks($tasks);
}

/**
 * Update a task (for marking complete)
 * @param string
 * @param array 
 * @return bool 
 */
function updateTask($id, $updates) {
    $tasks = loadTasks();
    
    // Find and update the task
    foreach ($tasks as $index => $task) {
        if ($task['id'] === $id) {
            // Merge updates into existing task
            $tasks[$index] = array_merge($task, $updates);
            return saveTasks($tasks);
        }
    }
    
    return false;
}

/**
 * Delete a task
 * @param string 
 * @return bool 
 */
function deleteTask($id) {
    $tasks = loadTasks();
    
    // Filter out the task with matching ID
    $tasks = array_filter($tasks, function($task) use ($id) {
        return $task['id'] !== $id;
    });
    
    // Re-index array (removes gaps in array keys)
    $tasks = array_values($tasks);
    
    return saveTasks($tasks);
}