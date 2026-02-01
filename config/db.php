<<<<<<< HEAD
=======
<?php
$host = "localhost";
$dbname = "np03cs4a240261";
$username = "np03cs4a240261";
$password = "eovrQN2rMk";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
>>>>>>> 3b9df05 (adding all fixed updated codes)
