<?php
session_start();
require "../config/db.php";
require "../includes/csrf.php";
require "../includes/header.php";

// Fetch all recipes initially (will be replaced by AJAX)
$stmt = $pdo->query("SELECT * FROM recipes ORDER BY created_at DESC");
$recipes = $stmt->fetchAll();
$initialCount = count($recipes);

$csrfToken = getCsrfToken();
?>

<div class="page-header">
    <h2 class="page-title">
        <span class="title-icon"></span>
        All Recipes
        <span class="result-badge">
            <span id="result-count"><?= $initialCount ?></span> recipes
        </span>
    </h2>
</div>

<div class="search-section">
    <?php include "../includes/search-form.php"; ?>
</div>

<!-- Container for AJAX-updated results -->
<div id="search-results">
    <?php if ($initialCount === 0): ?>
        <div class="empty-state">
            <div class="empty-icon"></div>
            <h3>No Recipes Yet</h3>
            <p>Start by adding your first delicious recipe!</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="add.php" class="btn btn-primary">Add Your First Recipe</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="recipes-grid">
            <?php foreach ($recipes as $recipe): ?>
            <div class="recipe-card" data-recipe-id="<?= $recipe['id'] ?>">
                <?php if ($recipe['image']): ?>
                    <div class="recipe-image">
                        <img src="../uploads/recipe_images/<?= htmlspecialchars($recipe['image']) ?>" 
                             alt="<?= htmlspecialchars($recipe['title']) ?>">
                        <div class="difficulty-badge difficulty-<?= strtolower($recipe['difficulty']) ?>">
                            <?= htmlspecialchars($recipe['difficulty']) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="recipe-image no-image">
                        <div class="no-image-placeholder"></div>
                        <div class="difficulty-badge difficulty-<?= strtolower($recipe['difficulty']) ?>">
                            <?= htmlspecialchars($recipe['difficulty']) ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="recipe-content">
                    <h3 class="recipe-title"><?= htmlspecialchars($recipe['title']) ?></h3>
                    
                    <div class="recipe-meta">
                        <span class="meta-item">
                            <span class="meta-icon"></span>
                            <?= htmlspecialchars($recipe['cuisine']) ?>
                        </span>
                    </div>

                    <div class="recipe-description">
                        <p><?= nl2br(htmlspecialchars(substr($recipe['instructions'], 0, 150))) ?>...</p>
                    </div>

                    <div class="ingredients-preview">
                        <strong>Ingredients:</strong>
                        <div class="ingredient-tags">
                            <?php
                            $ingStmt = $pdo->prepare("SELECT ingredient_name FROM ingredients WHERE recipe_id = ? LIMIT 3");
                            $ingStmt->execute([$recipe['id']]);
                            $ingredients = $ingStmt->fetchAll();
                            
                            foreach ($ingredients as $ing):
                            ?>
                                <span class="ingredient-tag"><?= htmlspecialchars($ing['ingredient_name']) ?></span>
                            <?php endforeach; ?>
                            
                            <?php
                            $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM ingredients WHERE recipe_id = ?");
                            $countStmt->execute([$recipe['id']]);
                            $totalCount = $countStmt->fetch()['total'];
                            if ($totalCount > 3):
                            ?>
                                <span class="ingredient-tag more">+<?= ($totalCount - 3) ?> more</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Edit/Delete only visible to the recipe owner -->
                    <?php if (isset($_SESSION['user_id']) && isset($recipe['user_id']) && $_SESSION['user_id'] === $recipe['user_id']): ?>
                    <div class="recipe-actions">
                        <a href="edit.php?id=<?= $recipe['id'] ?>" class="btn-action btn-edit">
                            <span class="action-icon"></span> Edit
                        </a>
                        <!-- Delete uses a POST form to carry the CSRF token -->
                        <form method="POST" action="delete.php?id=<?= $recipe['id'] ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this recipe?')">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" class="btn-action btn-delete">
                                <span class="action-icon"></span> Delete
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Include the unified search script -->
<script src="../assets/js/search.js"></script>

<?php require "../includes/footer.php"; ?>
