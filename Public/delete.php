<?php
require "../config/db.php";

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("Invalid recipe ID");
}

try {
    // Fetch the recipe to get image name
    $stmt = $pdo->prepare("SELECT image FROM recipes WHERE id = ?");
    $stmt->execute([$id]);
    $recipe = $stmt->fetch();
    
    if ($recipe) {
        // Delete the image file if it exists
        if ($recipe['image'] && file_exists("../uploads/recipe_images/" . $recipe['image'])) {
            unlink("../uploads/recipe_images/" . $recipe['image']);
        }
        
        // Delete recipe (ingredients will be deleted automatically due to CASCADE)
        $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = ?");
        $stmt->execute([$id]);
    }
    
    header("Location: index.php");
    exit;
    
} catch (Exception $e) {
    die("Error deleting recipe: " . $e->getMessage());
}
