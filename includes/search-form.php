<form method="GET" action="search.php" class="search-form" id="searchForm">
    <div class="search-header">
        <h3 class="search-title">
            <span class="search-icon">🔎</span>
            Filter Recipes
        </h3>
        <button type="button" class="btn-clear" id="clearFilters">
            <span>✕</span> Clear All
        </button>
    </div>

    <div class="search-grid">
        <div class="form-group">
            <label for="keyword">
                <span class="label-icon">📖</span>
                Recipe Title
            </label>
            <input type="text" 
                   id="keyword" 
                   name="keyword"
                   placeholder="Search by title..."
                   value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="cuisine">
                <span class="label-icon">🌍</span>
                Cuisine Type
            </label>
            <input type="text" 
                   id="cuisine" 
                   name="cuisine"
                   placeholder="e.g., Italian, Chinese..."
                   value="<?= htmlspecialchars($_GET['cuisine'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="difficulty">
                <span class="label-icon">⭐</span>
                Difficulty Level
            </label>
            <select id="difficulty" name="difficulty">
                <option value="">Any Difficulty</option>
                <option value="Easy" <?= ($_GET['difficulty'] ?? '') == "Easy" ? "selected" : "" ?>>Easy</option>
                <option value="Medium" <?= ($_GET['difficulty'] ?? '') == "Medium" ? "selected" : "" ?>>Medium</option>
                <option value="Hard" <?= ($_GET['difficulty'] ?? '') == "Hard" ? "selected" : "" ?>>Hard</option>
            </select>
        </div>

        <div class="form-group autocomplete-wrapper">
            <label for="ingredient">
                <span class="label-icon">🥗</span>
                Ingredient
            </label>
            <input type="text" 
                   id="ingredient" 
                   name="ingredient"
                   placeholder="Type ingredient..."
                   autocomplete="off"
                   value="<?= htmlspecialchars($_GET['ingredient'] ?? '') ?>">
            <ul id="suggestions" class="suggestions-list"></ul>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">
        <span class="btn-icon">🔍</span>
        Search Recipes
    </button>
</form>
