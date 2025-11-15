<?php
/**
 * RUN_TESTS.PHP - Automated Black-Box Test Runner
 * 
 * This script:
 * 1. Loads test cases from test_cases.json
 * 2. Executes each test case using cURL
 * 3. Validates the response
 * 4. Prints a pass/fail report
 * 
 * Usage: php tests/run_tests.php
 */

// Configuration
define('BASE_URL', 'http://localhost:8080');
define('TEST_CASES_FILE', __DIR__ . '/test_cases.json');

// ANSI color codes for terminal output
define('COLOR_GREEN', "\033[32m");
define('COLOR_RED', "\033[31m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_RESET', "\033[0m");

/**
 * Execute a single test case
 */
function executeTest($testCase) {
    $ch = curl_init();
    $url = BASE_URL . $testCase['endpoint'];
    
    // Setup request based on type
    if ($testCase['type'] === 'GET') {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    } else {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        
        // Get CSRF token if needed
        if (!isset($testCase['skip_csrf'])) {
            $csrfToken = getCsrfToken();
            $testCase['data']['csrf_token'] = $csrfToken;
        }
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testCase['data']));
    }
    
    // Common options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/test_cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/test_cookies.txt');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    
    curl_close($ch);
    
    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    return [
        'status' => $httpCode,
        'header' => $header,
        'body' => $body
    ];
}

/**
 * Get CSRF token from the server
 */
function getCsrfToken() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, BASE_URL . '/create.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/test_cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/test_cookies.txt');
    
    $html = curl_exec($ch);
    curl_close($ch);
    
    // Extract CSRF token from HTML
    if (preg_match('/name="csrf_token" value="([^"]+)"/', $html, $matches)) {
        return $matches[1];
    }
    
    return '';
}

/**
 * Validate test result
 */
function validateTest($testCase, $response) {
    $passed = true;
    $errors = [];
    
    // Check status code
    if (isset($testCase['expected_status'])) {
        if ($response['status'] !== $testCase['expected_status']) {
            $passed = false;
            $errors[] = "Expected status {$testCase['expected_status']}, got {$response['status']}";
        }
    }
    
    // Check for expected content
    if (isset($testCase['expected_content'])) {
        if (stripos($response['body'], $testCase['expected_content']) === false) {
            $passed = false;
            $errors[] = "Expected content not found: {$testCase['expected_content']}";
        }
    }
    
    // Check for redirect
    if (isset($testCase['expected_redirect'])) {
        if (stripos($response['header'], 'Location: ' . $testCase['expected_redirect']) === false) {
            $passed = false;
            $errors[] = "Expected redirect to {$testCase['expected_redirect']}";
        }
    }
    
    return [
        'passed' => $passed,
        'errors' => $errors
    ];
}

/**
 * Print test result
 */
function printResult($testCase, $result, $response) {
    $status = $result['passed'] ? COLOR_GREEN . '✓ PASS' : COLOR_RED . '✗ FAIL';
    
    echo "\n" . COLOR_BLUE . "[{$testCase['id']}]" . COLOR_RESET . " {$testCase['name']}\n";
    echo "  Status: {$status}" . COLOR_RESET . "\n";
    echo "  HTTP {$response['status']} | {$testCase['type']} {$testCase['endpoint']}\n";
    
    if (!$result['passed']) {
        foreach ($result['errors'] as $error) {
            echo "  " . COLOR_RED . "  ✗ " . $error . COLOR_RESET . "\n";
        }
    }
}

/**
 * Main test runner
 */
function runTests() {
    echo COLOR_YELLOW . "\n";
    echo "╔════════════════════════════════════════════════╗\n";
    echo "║      TaskPad PHP - Black-Box Test Suite       ║\n";
    echo "╚════════════════════════════════════════════════╝\n";
    echo COLOR_RESET;
    
    // Check if server is running
    $ch = curl_init(BASE_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($result === false) {
        echo COLOR_RED . "\n✗ ERROR: Server not reachable at " . BASE_URL . COLOR_RESET . "\n";
        echo "Make sure the server is running: php -S localhost:8080 -t public\n\n";
        exit(1);
    }
    
    // Load test cases
    if (!file_exists(TEST_CASES_FILE)) {
        echo COLOR_RED . "\n✗ ERROR: Test cases file not found\n" . COLOR_RESET;
        exit(1);
    }
    
    $json = file_get_contents(TEST_CASES_FILE);
    $data = json_decode($json, true);
    $testCases = $data['test_cases'];
    
    echo "\nRunning " . count($testCases) . " test cases...\n";
    echo str_repeat("─", 50) . "\n";
    
    // Run tests
    $passed = 0;
    $failed = 0;
    
    foreach ($testCases as $testCase) {
        $response = executeTest($testCase);
        $result = validateTest($testCase, $response);
        printResult($testCase, $result, $response);
        
        if ($result['passed']) {
            $passed++;
        } else {
            $failed++;
        }
        
        // Small delay between tests
        usleep(100000); // 0.1 seconds
    }
    
    // Print summary
    echo "\n" . str_repeat("─", 50) . "\n";
    echo COLOR_YELLOW . "\nTest Summary:\n" . COLOR_RESET;
    echo "  Total:  " . count($testCases) . "\n";
    echo "  " . COLOR_GREEN . "Passed: " . $passed . COLOR_RESET . "\n";
    echo "  " . COLOR_RED . "Failed: " . $failed . COLOR_RESET . "\n";
    
    $percentage = round(($passed / count($testCases)) * 100, 1);
    echo "\n  Success Rate: " . $percentage . "%\n\n";
    
    // Exit code
    exit($failed > 0 ? 1 : 0);
}

// Run the tests
runTests();