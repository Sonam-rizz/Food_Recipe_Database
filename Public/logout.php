<?php
/**
 * Logout Page with CSRF Protection
 * Handles user logout and session destruction
 */

// Start session
session_start();

// Include CSRF protection
require_once '../includes/csrf.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle POST request (logout form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (validateCsrfToken($_POST['csrf_token'] ?? '')) {
        // Clear all session data
        $_SESSION = array();
        
        // Delete the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destroy the session
        session_destroy();
        
        // Redirect to login page with success message
        header('Location: login.php?logged_out=1');
        exit();
    } else {
        // Invalid CSRF token
        $error = 'Invalid security token. Please try again.';
    }
}

// Handle GET request (show confirmation page)
$csrfToken = getCsrfToken();
$username = $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Food Recipe Database</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .logout-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .logout-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .logout-container p {
            margin-bottom: 30px;
            color: #666;
            line-height: 1.6;
        }
        .logout-container .username {
            font-weight: bold;
            color: #4CAF50;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
        }
        .btn-logout {
            background: #f44336;
            color: white;
        }
        .btn-logout:hover {
            background: #da190b;
        }
        .btn-cancel {
            background: #ddd;
            color: #333;
        }
        .btn-cancel:hover {
            background: #ccc;
        }
        .error-message {
            background: #f44336;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logout-container">
            <h2>🚪 Logout</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <p>
                Are you sure you want to logout, 
                <span class="username"><?php echo htmlspecialchars($username); ?></span>?
            </p>
            
            <form method="POST" action="logout.php">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="btn-group">
                    <button type="submit" class="btn btn-logout">Yes, Logout</button>
                    <a href="index.php" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
