<?php
/**
 * INDEX.PHP - Main page that lists and filters tasks
 * 
 * This page:
 * 1. Shows all tasks
 * 2. Allows filtering by search text and priority
 * 3. Shows Complete/Delete buttons for each task
 */

// Include our helper files
require_once '../src/storage.php';
require_once '../src/validation.php';
require_once '../src/csrf.php';
require_once '../src/flash.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token for forms
$csrfToken = generateCsrfToken();

// Load all tasks
$allTasks = loadTasks();

// Get filter parameters from URL (?q=search&priority=High)
$searchQuery = isset($_GET['q']) ? sanitizeQuery($_GET['q']) : '';
$priorityFilter = isset($_GET['priority']) ? validatePriority($_GET['priority']) : null;

// Filter tasks based on search and priority
$tasks = $allTasks;

if (!empty($searchQuery)) {
    $tasks = array_filter($tasks, function($task) use ($searchQuery) {
        $query = strtolower($searchQuery);
        $title = strtolower($task['title']);
        $desc = strtolower($task['description'] ?? '');
        
        // Check if query appears in title or description
        return strpos($title, $query) !== false || strpos($desc, $query) !== false;
    });
}

if ($priorityFilter) {
    $tasks = array_filter($tasks, function($task) use ($priorityFilter) {
        return $task['priority'] === $priorityFilter;
    });
}

// Count tasks
$totalTasks = count($allTasks);
$displayedTasks = count($tasks);
$completedCount = count(array_filter($allTasks, function($t) { return $t['completed']; }));
$openCount = $totalTasks - $completedCount;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskPad PHP - Task List</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>TaskPad PHP</h1>
            <p>A minimal task tracker</p>
        </header>

        <?php echo displayFlash(); ?>

        <div class="actions">
            <a href="create.php" class="btn btn-primary">+ Add New Task</a>
        </div>

        <div class="stats">
            <span class="stat">Total: <?php echo $totalTasks; ?></span>
            <span class="stat">Open: <?php echo $openCount; ?></span>
            <span class="stat">Completed: <?php echo $completedCount; ?></span>
        </div>

        <div class="filters">
            <form method="GET" action="index.php">
                <input 
                    type="text" 
                    name="q" 
                    placeholder="Search tasks..." 
                    value="<?php echo e($searchQuery); ?>"
                >
                
                <select name="priority">
                    <option value="">All Priorities</option>
                    <option value="Low" <?php echo $priorityFilter === 'Low' ? 'selected' : ''; ?>>Low</option>
                    <option value="Medium" <?php echo $priorityFilter === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="High" <?php echo $priorityFilter === 'High' ? 'selected' : ''; ?>>High</option>
                </select>
                
                <button type="submit" class="btn">Filter</button>
                <a href="index.php" class="btn btn-secondary">Clear</a>
            </form>
        </div>

        <div class="task-list">
            <?php if (empty($tasks)): ?>
                <div class="empty-state">
                    <?php if (empty($allTasks)): ?>
                        <p>No tasks yet! Click "Add New Task" to get started.</p>
                    <?php else: ?>
                        <p>No tasks match your filters.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task-card <?php echo $task['completed'] ? 'completed' : ''; ?>">
                        <div class="task-header">
                            <h3><?php echo e($task['title']); ?></h3>
                            <span class="priority-badge priority-<?php echo strtolower($task['priority']); ?>">
                                <?php echo e($task['priority']); ?>
                            </span>
                        </div>
                        
                        <?php if (!empty($task['description'])): ?>
                            <p class="task-description"><?php echo e($task['description']); ?></p>
                        <?php endif; ?>
                        
                        <div class="task-meta">
                            <?php if (!empty($task['due'])): ?>
                                <span class="due-date">📅 Due: <?php echo e($task['due']); ?></span>
                            <?php endif; ?>
                            
                            <span class="status-badge">
                                <?php echo $task['completed'] ? '✓ Completed' : '○ Open'; ?>
                            </span>
                        </div>
                        
                        <div class="task-actions">
                            <?php if (!$task['completed']): ?>
                                <form method="POST" action="actions.php" style="display: inline;">
                                    <input type="hidden" name="action" value="complete">
                                    <input type="hidden" name="id" value="<?php echo e($task['id']); ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                                    <button type="submit" class="btn btn-success">✓ Complete</button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" action="actions.php" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo e($task['id']); ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('Are you sure you want to delete this task?')">
                                    🗑 Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <footer>
            <p>Showing <?php echo $displayedTasks; ?> of <?php echo $totalTasks; ?> tasks</p>
        </footer>
    </div>
</body>
</html>