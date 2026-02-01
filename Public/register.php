<?php
/**
 * Registration Page with CSRF Protection
 * Allows new users to create accounts
 */

// Start session
session_start();

// Include database connection and CSRF protection
require_once '../config/db.php';
require_once '../includes/csrf.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        // Get form data
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            $error = 'All fields are required.';
        } elseif (strlen($username) < 3) {
            $error = 'Username must be at least 3 characters long.';
        } elseif (strlen($username) > 50) {
            $error = 'Username must be less than 50 characters.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            try {
                // Check if username already exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                
                if ($stmt->fetch()) {
                    $error = 'Username already exists. Please choose another.';
                } else {
                    // Check if email already exists
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                    $stmt->execute([$email]);
                    
                    if ($stmt->fetch()) {
                        $error = 'Email already registered. Please use another email or login.';
                    } else {
                        // Hash the password
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Insert new user
                        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
                        $stmt->execute([$username, $email, $hashedPassword]);
                        
                        $success = 'Registration successful! You can now login.';
                        
                        // Clear form data
                        $_POST = array();
                        
                        // Redirect to login after 2 seconds
                        header("Refresh: 2; url=login.php");
                    }
                }
            } catch (PDOException $e) {
                $error = 'An error occurred. Please try again later.';
                error_log('Registration error: ' . $e->getMessage());
            }
        }
    }
}

// Generate CSRF token for the form
$csrfToken = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Food Recipe Database</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .register-container {
            max-width: 450px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .register-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
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
        .btn-register {
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
        .btn-register:hover {
            background: #45a049;
        }
        .error-message {
            background: #f44336;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        .success-message {
            background: #4CAF50;
            color: white;
            padding: 10px;
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
    </style>
    <script>
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
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
        }
        
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchText = document.getElementById('match-text');
            
            if (confirmPassword.length === 0) {
                matchText.textContent = '';
            } else if (password === confirmPassword) {
                matchText.textContent = '✓ Passwords match';
                matchText.style.color = '#4CAF50';
            } else {
                matchText.textContent = '✗ Passwords do not match';
                matchText.style.color = '#f44336';
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <h2>🍳 Create Account</h2>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?><br>Redirecting to login...</div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
            <form method="POST" action="register.php">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        autofocus
                        minlength="3"
                        maxlength="50"
                        autocomplete="username"
                        value="<?php echo htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    >
                    <small>3-50 characters, letters and numbers only</small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        autocomplete="email"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    >
                    <small>We'll never share your email</small>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        minlength="6"
                        autocomplete="new-password"
                        oninput="checkPasswordStrength(); checkPasswordMatch();"
                    >
                    <div id="password-strength"></div>
                    <small id="strength-text"></small>
                    <small>At least 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
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
                
                <button type="submit" class="btn-register">Create Account</button>
            </form>
            <?php endif; ?>
            
            <div class="links">
                Already have an account? <a href="login.php">Login here</a><br>
                <a href="index.php">← Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>
