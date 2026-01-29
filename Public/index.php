<?php
require "../config/db.php";
require "../includes/header.php";

// Fetch all recipes
$stmt = $pdo->query("SELECT * FROM recipes ORDER BY created_at DESC");
$recipes = $stmt->fetchAll();
?>

<h2>📚 All Recipes</h2>

<div style="margin-bottom: 30px;">
    <?php include "../includes/search-form.php"; ?>
</div>

<?php if (count($recipes) === 0): ?>
    <div class="empty-state">
        <h3>🍽️ No Recipes Yet</h3>
        <p>Start by adding your first recipe!</p>
        <a href="add.php" class="btn btn-primary" style="display: inline-block; margin-top: 20px;">Add Your First Recipe</a>
    </div>
<?php else: ?>
    <div class="recipes-container">
        <?php foreach ($recipes as $recipe): ?>
        <div class="recipe-card">
            <h3><?= htmlspecialchars($recipe['title']) ?></h3>
            
            <div class="recipe-meta">
                <p><strong>🌍 Cuisine:</strong> <?= htmlspecialchars($recipe['cuisine']) ?></p>
                <p><strong>⭐ Difficulty:</strong> <?= htmlspecialchars($recipe['difficulty']) ?></p>
            </div>

            <?php if ($recipe['image']): ?>
                <img src="../uploads/recipe_images/<?= htmlspecialchars($recipe['image']) ?>" 
                     alt="<?= htmlspecialchars($recipe['title']) ?>">
            <?php endif; ?>

            <p><strong>📝 Instructions:</strong></p>
            <p><?= nl2br(htmlspecialchars(substr($recipe['instructions'], 0, 200))) ?>...</p>

            <p><strong>🥗 Ingredients:</strong></p>
            <ul class="ingredients-list">
                <?php
                $ingStmt = $pdo->prepare("SELECT ingredient_name FROM ingredients WHERE recipe_id = ?");
                $ingStmt->execute([$recipe['id']]);
                $ingredients = $ingStmt->fetchAll();
                
                foreach ($ingredients as $ing):
                ?>
                    <li><?= htmlspecialchars($ing['ingredient_name']) ?></li>
                <?php endforeach; ?>
            </ul>

            <div class="recipe-actions">
                <a href="edit.php?id=<?= $recipe['id'] ?>">✏️ Edit</a>
                <a href="delete.php?id=<?= $recipe['id'] ?>"
                   onclick="return confirm('Are you sure you want to delete this recipe?')">
                   🗑️ Delete
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require "../includes/footer.php"; ?>
