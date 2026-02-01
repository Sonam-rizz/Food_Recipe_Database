<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Recipe Database</title>
    <link rel="stylesheet" href="../assets/css/style.css">
<<<<<<< HEAD
=======
    <style>
        .user-info {
            display: inline-block;
            margin: 0 10px;
            color: #4CAF50;
            font-weight: bold;
        }
    </style>
>>>>>>> 3b9df05 (adding all fixed updated codes)
</head>
<body>
    <div class="container">
        <header>
            <h1>🍳 Food Recipe Database</h1>
            <nav>
                <a href="index.php">Home</a>
<<<<<<< HEAD
                <a href="add.php">Add Recipe</a>
                <a href="search.php">Search</a>
=======
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="add.php">Add Recipe</a>
                <?php endif; ?>
                <a href="search.php">Search</a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="user-info">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="change-password.php">Change Password</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                <?php endif; ?>
>>>>>>> 3b9df05 (adding all fixed updated codes)
            </nav>
        </header>
        <hr>
