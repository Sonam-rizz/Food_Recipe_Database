<?php
session_start();
require "../config/db.php";
require "../includes/csrf.php";

header('Content-Type: application/json');

$keyword    = $_GET['keyword'] ?? '';
$cuisine    = $_GET['cuisine'] ?? '';
$difficulty = $_GET['difficulty'] ?? '';
$ingredient = $_GET['ingredient'] ?? '';

$sql = "
SELECT DISTINCT recipes.*
FROM recipes
LEFT JOIN ingredients ON recipes.id = ingredients.recipe_id
WHERE 1=1
";

$params = [];

if ($keyword) {
    $sql .= " AND recipes.title LIKE ?";
    $params[] = "%$keyword%";
}

if ($cuisine) {
    $sql .= " AND recipes.cuisine LIKE ?";
    $params[] = "%$cuisine%";
}

if ($difficulty) {
    $sql .= " AND recipes.difficulty = ?";
    $params[] = $difficulty;
}

if ($ingredient) {
    $sql .= " AND ingredients.ingredient_name LIKE ?";
    $params[] = "%$ingredient%";
}

$sql .= " ORDER BY recipes.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

$csrfToken = getCsrfToken();

// Start output buffering to capture HTML
ob_start();

if (count($results) === 0): ?>
    <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <h3>No Recipes Found</h3>
        <p>Try adjusting your search filters or explore our collection</p>
        <a href="index.php" class="btn btn-outline">View All Recipes</a>
    </div>
<?php else: ?>
    <div class="recipes-grid">
        <?php foreach ($results as $recipe): ?>
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
                    <div class="no-image-placeholder">🍽️</div>
                    <div class="difficulty-badge difficulty-<?= strtolower($recipe['difficulty']) ?>">
                        <?= htmlspecialchars($recipe['difficulty']) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="recipe-content">
                <h3 class="recipe-title"><?= htmlspecialchars($recipe['title']) ?></h3>
                
                <div class="recipe-meta">
                    <span class="meta-item">
                        <span class="meta-icon">🌍</span>
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
                        <span class="action-icon">✏️</span> Edit
                    </a>
                    <form method="POST" action="delete.php?id=<?= $recipe['id'] ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this recipe?')">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <button type="submit" class="btn-action btn-delete">
                            <span class="action-icon">🗑️</span> Delete
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif;

$html = ob_get_clean();

echo json_encode([
    'html' => $html,
    'count' => count($results)
]);
