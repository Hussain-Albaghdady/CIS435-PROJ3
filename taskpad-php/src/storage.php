<?php
define('TASKS_FILE', __DIR__ . '/../data/tasks.json');

function loadTasks() {
    if (!file_exists(TASKS_FILE)) {
        return [];
    }
    $json = file_get_contents(TASKS_FILE);
    return json_decode($json, true) ?: [];
}

function saveTasks($tasks) {
    $json = json_encode($tasks, JSON_PRETTY_PRINT);
    return file_put_contents(TASKS_FILE, $json) !== false;
}

function getTaskById($id) {
    $tasks = loadTasks();
    foreach ($tasks as $task) {
        if ($task['id'] === $id) {
            return $task;
        }
    }
    return null;
}

function addTask($data) {
    $tasks = loadTasks();
    $data['id'] = time() . '_' . rand(1000, 9999);
    $data['completed'] = false;
    $tasks[] = $data;
    return saveTasks($tasks);
}

function updateTask($id, $updates) {
    $tasks = loadTasks();
    foreach ($tasks as $i => $task) {
        if ($task['id'] === $id) {
            $tasks[$i] = array_merge($task, $updates);
            return saveTasks($tasks);
        }
    }
    return false;
}

function deleteTask($id) {
    $tasks = loadTasks();
    $tasks = array_filter($tasks, fn($t) => $t['id'] !== $id);
    $tasks = array_values($tasks);
    return saveTasks($tasks);
}