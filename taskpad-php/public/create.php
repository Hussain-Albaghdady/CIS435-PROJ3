<?php



require_once '../src/storage.php';
require_once '../src/validation.php';
require_once '../src/csrf.php';
require_once '../src/flash.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$errors = [];
$formData = [
    'title' => '',
    'description' => '',
    'priority' => 'Medium',
    'due' => ''
];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    requireCsrfToken();
    

    $formData = [
        'title' => $_POST['title'] ?? '',
        'description' => $_POST['description'] ?? '',
        'priority' => $_POST['priority'] ?? 'Medium',
        'due' => $_POST['due'] ?? ''
    ];
    
  
    $cleanData = FormatTask($formData);
    
  
    $errors = validateTask($cleanData);
    
 
    if (empty($errors)) {
        if (addTask($cleanData)) {
            // success message
            setFlash('success', 'Task created successfully!');
            
           
            header('Location: index.php');
            exit;
        } else {
            $errors['general'] = 'Failed to save task. Please try again.';
        }
    }
    
}

// Generate CSRF token
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Task</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>New Task</h1>
            <p><a href="index.php">Back to list</a></p>
        </header>

        <?php if (!empty($errors['general'])): ?>
            <div class="flash-message error">
                <?php echo e($errors['general']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="create.php" class="task-form">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
            
            <!-- Title Field -->
            <div class="form-group">
                <label for="title">Title <span class="required">**</span></label>
                <input 
                    type="text" 
                    id="title" 
                    name="title" 
                    value="<?php echo e($formData['title']); ?>"
                    placeholder="Enter task title"
                    maxlength="200"
                    required
                >
                <?php if (!empty($errors['title'])): ?>
                    <span class="error-message"><?php echo e($errors['title']); ?></span>
                <?php endif; ?>
            </div>

            <!-- Description Field -->
            <div class="form-group">
                <label for="description">Description (optional)</label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="4"
                    placeholder="Enter task description"
                    maxlength="1000"
                ><?php echo e($formData['description']); ?></textarea>
                <?php if (!empty($errors['description'])): ?>
                    <span class="error-message"><?php echo e($errors['description']); ?></span>
                <?php endif; ?>
            </div>

            <!-- Priority Field -->
            <div class="form-group">
                <label for="priority">Priority <span class="required">*</span></label>
                <select id="priority" name="priority" required>
                    <option value="Low" <?php echo $formData['priority'] === 'Low' ? 'selected' : ''; ?>>
                        Low
                    </option>
                    <option value="Medium" <?php echo $formData['priority'] === 'Medium' ? 'selected' : ''; ?>>
                        Medium
                    </option>
                    <option value="High" <?php echo $formData['priority'] === 'High' ? 'selected' : ''; ?>>
                        High
                    </option>
                </select>
                <?php if (!empty($errors['priority'])): ?>
                    <span class="error-message"><?php echo e($errors['priority']); ?></span>
                <?php endif; ?>
            </div>

            <!-- Due Date Field -->
            <div class="form-group">
                <label for="due">Due Date (optional)</label>
                <input 
                    type="date" 
                    id="due" 
                    name="due" 
                    value="<?php echo e($formData['due']); ?>"
                >
                
                <?php if (!empty($errors['due'])): ?>
                    <span class="error-message"><?php echo e($errors['due']); ?></span>
                <?php endif; ?>
            </div>

            <!-- Submit Button -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Task</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>