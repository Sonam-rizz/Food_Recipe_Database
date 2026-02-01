<?php
/**
 * CSRF Protection Utilities
 * Generates and validates CSRF tokens for form protection
 */

/**
 * Generate a CSRF token and store it in the session
 * @return string The generated CSRF token
 */
function generateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Generate a random token
    $token = bin2hex(random_bytes(32));
    
    // Store in session
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    
    return $token;
}

/**
 * Get the current CSRF token from session, or generate a new one
 * @return string The CSRF token
 */
function getCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if token exists and is not expired (1 hour expiry)
    if (isset($_SESSION['csrf_token']) && isset($_SESSION['csrf_token_time'])) {
        $tokenAge = time() - $_SESSION['csrf_token_time'];
        if ($tokenAge < 3600) { // 1 hour
            return $_SESSION['csrf_token'];
        }
    }
    
    // Generate new token if expired or doesn't exist
    return generateCsrfToken();
}

/**
 * Validate the CSRF token from a POST request
 * @param string $token The token to validate
 * @return bool True if valid, false otherwise
 */
function validateCsrfToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if session token exists
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    // Check if token has expired (1 hour)
    if (isset($_SESSION['csrf_token_time'])) {
        $tokenAge = time() - $_SESSION['csrf_token_time'];
        if ($tokenAge >= 3600) {
            return false;
        }
    }
    
    // Compare tokens using timing-safe comparison
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate a hidden input field with CSRF token
 * @return string HTML input field
 */
function csrfTokenField() {
    $token = getCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verify CSRF token from POST request and die with error if invalid
 * @param string $errorMessage Custom error message (optional)
 */
function verifyCsrfToken($errorMessage = 'Invalid CSRF token. Please try again.') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        
        if (!validateCsrfToken($token)) {
            http_response_code(403);
            die($errorMessage);
        }
    }
}
?>
