<?php
session_start();
require "../config/db.php";
require "../includes/csrf.php";

// Auth guard — must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require "../includes/header.php";

$id = $_GET['id'] ?? null;
$message = '';
$messageType = '';

if (!$id || !is_numeric($id)) {
    die("Invalid recipe ID");
}

// Fetch recipe — only allow editing if user owns it
$stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$recipe = $stmt->fetch();

if (!$recipe) {
    die("Recipe not found or you do not have permission to edit it.");
}

// Fetch ingredients
$ingStmt = $pdo->prepare("SELECT ingredient_name FROM ingredients WHERE recipe_id = ?");
$ingStmt->execute([$id]);
$ingredients = $ingStmt->fetchAll(PDO::FETCH_COLUMN);
$ingredientList = implode(", ", $ingredients);

// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verify CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = "Invalid security token. Please try again.";
        $messageType = "error";
    } else {
        $title = trim($_POST["title"]);
        $cuisine = trim($_POST["cuisine"]);
        $difficulty = $_POST["difficulty"];
        $instructions = trim($_POST["instructions"]);
        $newIngredients = array_filter(array_map('trim', explode(",", $_POST["ingredients"])));

        if (empty($title) || empty($cuisine) || empty($difficulty) || empty($instructions) || empty($newIngredients)) {
            $message = "All fields are required!";
            $messageType = "error";
        } else {
            try {
                // Update recipe — WHERE includes user_id for extra safety
                $stmt = $pdo->prepare(
                    "UPDATE recipes SET title=?, cuisine=?, difficulty=?, instructions=? WHERE id=? AND user_id=?"
                );
                $stmt->execute([$title, $cuisine, $difficulty, $instructions, $id, $_SESSION['user_id']]);

                // Update ingredients (delete old ones and insert new)
                $pdo->prepare("DELETE FROM ingredients WHERE recipe_id=?")->execute([$id]);

                $ingStmt = $pdo->prepare(
                    "INSERT INTO ingredients (recipe_id, ingredient_name) VALUES (?, ?)"
                );
                
                foreach ($newIngredients as $ing) {
                    if (!empty($ing)) {
                        $ingStmt->execute([$id, $ing]);
                    }
                }

                $message = "Recipe updated successfully! 🎉";
                $messageType = "success";
                
                // Refresh recipe data
                $stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = ?");
                $stmt->execute([$id]);
                $recipe = $stmt->fetch();
                
                $ingStmt = $pdo->prepare("SELECT ingredient_name FROM ingredients WHERE recipe_id = ?");
                $ingStmt->execute([$id]);
                $ingredients = $ingStmt->fetchAll(PDO::FETCH_COLUMN);
                $ingredientList = implode(", ", $ingredients);
                
            } catch (Exception $e) {
                $message = "Error: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
}

$csrfToken = getCsrfToken();
?>

<h2>✏️ Edit Recipe</h2>

<?php if ($message): ?>
    <div class="message <?= $messageType ?>">
        <?= htmlspecialchars($message) ?>
        <?php if ($messageType === 'success'): ?>
            <br><a href="index.php">Back to all recipes</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<form method="POST" class="search-form">
    <?= csrfTokenField() ?>
    <div class="search-grid">
        <div class="form-group">
            <label for="title">Recipe Title *</label>
            <input type="text" 
                   id="title" 
                   name="title"
                   value="<?= htmlspecialchars($recipe['title']) ?>" 
                   required>
        </div>

        <div class="form-group">
            <label for="cuisine">Cuisine *</label>
            <input type="text" 
                   id="cuisine" 
                   name="cuisine"
                   value="<?= htmlspecialchars($recipe['cuisine']) ?>" 
                   required>
        </div>

        <div class="form-group">
            <label for="difficulty">Difficulty *</label>
            <select id="difficulty" name="difficulty" required>
                <option value="Easy" <?= $recipe['difficulty']=="Easy" ? "selected" : "" ?>>Easy</option>
                <option value="Medium" <?= $recipe['difficulty']=="Medium" ? "selected" : "" ?>>Medium</option>
                <option value="Hard" <?= $recipe['difficulty']=="Hard" ? "selected" : "" ?>>Hard</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="instructions">Cooking Instructions *</label>
        <textarea id="instructions" 
                  name="instructions" 
                  required><?= htmlspecialchars($recipe['instructions']) ?></textarea>
    </div>

    <div class="form-group">
        <label for="ingredients">Ingredients *</label>
        <input type="text" 
               id="ingredients" 
               name="ingredients"
               value="<?= htmlspecialchars($ingredientList) ?>" 
               required>
        <small style="color: #999; margin-top: 5px; display: block;">Separate each ingredient with a comma</small>
    </div>

    <?php if ($recipe['image']): ?>
        <div class="form-group">
            <label>Current Image:</label>
            <img src="../uploads/recipe_images/<?= htmlspecialchars($recipe['image']) ?>" 
                 alt="Current recipe image" 
                 style="max-width: 300px; border-radius: 8px;">
        </div>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary">Update Recipe</button>
    <a href="index.php" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
</form>

<?php require "../includes/footer.php"; ?>
