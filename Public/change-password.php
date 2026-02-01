<?php
/**
 * Change Password Page with CSRF Protection
 * Allows logged-in users to change their password
 */

// Start session
session_start();

// Include database connection and CSRF protection
require_once '../config/db.php';
require_once '../includes/csrf.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        // Get form data
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate input
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'All fields are required.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters long.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } elseif ($current_password === $new_password) {
            $error = 'New password must be different from current password.';
        } else {
            try {
                // Fetch user's current password hash
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($current_password, $user['password'])) {
                    // Current password is correct, update to new password
                    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateStmt->execute([$hashedPassword, $_SESSION['user_id']]);
                    
                    $success = 'Password changed successfully!';
                    
                    // Clear form data
                    $_POST = array();
                    
                    // Optional: Force re-login after password change
                    // header("Refresh: 2; url=logout.php");
                } else {
                    // Current password is incorrect
                    $error = 'Current password is incorrect.';
                    // Add a small delay to prevent brute force attacks
                    sleep(1);
                }
            } catch (PDOException $e) {
                $error = 'An error occurred. Please try again later.';
                error_log('Password change error: ' . $e->getMessage());
            }
        }
    }
}

// Generate CSRF token for the form
$csrfToken = getCsrfToken();
$username = $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Food Recipe Database</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .password-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .password-container h2 {
            text-align: center;
            margin-bottom: 10px;
            color: #333;
        }
        .user-info {
            text-align: center;
            margin-bottom: 30px;
            color: #666;
            font-size: 14px;
        }
        .user-info strong {
            color: #4CAF50;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #4CAF50;
        }
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 12px;
        }
        .btn-change {
            width: 100%;
            padding: 12px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-change:hover {
            background: #45a049;
        }
        .btn-change:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .error-message {
            background: #f44336;
            color: white;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-message {
            background: #4CAF50;
            color: white;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            color: #4CAF50;
            text-decoration: none;
        }
        .links a:hover {
            text-decoration: underline;
        }
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 3px;
            transition: all 0.3s;
        }
        .strength-weak { background: #f44336; width: 33%; }
        .strength-medium { background: #ff9800; width: 66%; }
        .strength-strong { background: #4CAF50; width: 100%; }
        .password-requirements {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .password-requirements h4 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }
        .password-requirements li {
            margin: 5px 0;
            color: #666;
        }
        .requirement-met {
            color: #4CAF50;
        }
        .requirement-not-met {
            color: #f44336;
        }
    </style>
    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('new_password').value;
            const strengthBar = document.getElementById('password-strength');
            const strengthText = document.getElementById('strength-text');
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            strengthBar.className = 'password-strength';
            if (password.length === 0) {
                strengthBar.className = 'password-strength';
                strengthText.textContent = '';
            } else if (strength <= 2) {
                strengthBar.className = 'password-strength strength-weak';
                strengthText.textContent = 'Weak password';
                strengthText.style.color = '#f44336';
            } else if (strength <= 4) {
                strengthBar.className = 'password-strength strength-medium';
                strengthText.textContent = 'Medium password';
                strengthText.style.color = '#ff9800';
            } else {
                strengthBar.className = 'password-strength strength-strong';
                strengthText.textContent = 'Strong password';
                strengthText.style.color = '#4CAF50';
            }
            
            // Update requirements checklist
            updateRequirements(password);
        }
        
        function updateRequirements(password) {
            const req1 = document.getElementById('req-length');
            const req2 = document.getElementById('req-uppercase');
            const req3 = document.getElementById('req-number');
            const req4 = document.getElementById('req-special');
            
            req1.className = password.length >= 6 ? 'requirement-met' : 'requirement-not-met';
            req2.className = (/[a-z]/.test(password) && /[A-Z]/.test(password)) ? 'requirement-met' : 'requirement-not-met';
            req3.className = /\d/.test(password) ? 'requirement-met' : 'requirement-not-met';
            req4.className = /[^a-zA-Z0-9]/.test(password) ? 'requirement-met' : 'requirement-not-met';
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchText = document.getElementById('match-text');
            const submitBtn = document.getElementById('submit-btn');
            
            if (confirmPassword.length === 0) {
                matchText.textContent = '';
                submitBtn.disabled = false;
            } else if (password === confirmPassword) {
                matchText.textContent = '✓ Passwords match';
                matchText.style.color = '#4CAF50';
                submitBtn.disabled = false;
            } else {
                matchText.textContent = '✗ Passwords do not match';
                matchText.style.color = '#f44336';
                submitBtn.disabled = true;
            }
        }
    </script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="password-container">
        <h2>🔐 Change Password</h2>
        <div class="user-info">
            Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="change-password.php">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            
            <div class="form-group">
                <label for="current_password">Current Password *</label>
                <input 
                    type="password" 
                    id="current_password" 
                    name="current_password" 
                    required 
                    autofocus
                    autocomplete="current-password"
                >
                <small>Enter your current password to verify it's you</small>
            </div>
            
            <div class="password-requirements">
                <h4>Password Requirements:</h4>
                <ul>
                    <li id="req-length" class="requirement-not-met">At least 6 characters</li>
                    <li id="req-uppercase" class="requirement-not-met">Mix of uppercase and lowercase (recommended)</li>
                    <li id="req-number" class="requirement-not-met">At least one number (recommended)</li>
                    <li id="req-special" class="requirement-not-met">Special character (recommended)</li>
                </ul>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password *</label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    required
                    minlength="6"
                    autocomplete="new-password"
                    oninput="checkPasswordStrength(); checkPasswordMatch();"
                >
                <div id="password-strength"></div>
                <small id="strength-text"></small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password *</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required
                    minlength="6"
                    autocomplete="new-password"
                    oninput="checkPasswordMatch();"
                >
                <small id="match-text"></small>
            </div>
            
            <button type="submit" id="submit-btn" class="btn-change">Change Password</button>
        </form>
        
        <div class="links">
            <a href="index.php">← Back to Home</a>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
