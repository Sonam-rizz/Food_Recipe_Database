// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    const ingredientInput = document.querySelector("#ingredient");
    const suggestionsList = document.getElementById("suggestions");
    const searchResultsDiv = document.getElementById("search-results");
    let debounceTimer;

    // Check if we're on the search page
    if (!ingredientInput || !searchResultsDiv) return;

    // Function to perform AJAX search
    function performSearch() {
        const keyword = document.querySelector('input[name="keyword"]')?.value || '';
        const cuisine = document.querySelector('input[name="cuisine"]')?.value || '';
        const difficulty = document.querySelector('select[name="difficulty"]')?.value || '';
        const ingredient = ingredientInput.value || '';

        // Build query string
        const params = new URLSearchParams();
        if (keyword) params.append('keyword', keyword);
        if (cuisine) params.append('cuisine', cuisine);
        if (difficulty) params.append('difficulty', difficulty);
        if (ingredient) params.append('ingredient', ingredient);

        // Show loading state
        searchResultsDiv.innerHTML = '<div class="loading">🔍 Searching recipes...</div>';

        // Fetch filtered results
        fetch(`ajax_search.php?${params.toString()}`)
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.text();
            })
            .then(html => {
                searchResultsDiv.innerHTML = html;
            })
            .catch(err => {
                console.error('Search error:', err);
                searchResultsDiv.innerHTML = '<div class="message error">Error loading results. Please try again.</div>';
            });
    }

    // Function to fetch autocomplete suggestions
    function fetchSuggestions(term) {
        if (!term.trim()) {
            suggestionsList.innerHTML = "";
            return;
        }
        
        fetch(`ajax_ingredients.php?term=${encodeURIComponent(term)}`)
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(data => {
                suggestionsList.innerHTML = "";
                
                if (data.length === 0) {
                    return;
                }
                
                data.forEach(item => {
                    const li = document.createElement("li");
                    li.textContent = item;
                    li.onclick = () => {
                        ingredientInput.value = item;
                        suggestionsList.innerHTML = "";
                        performSearch();
                    };
                    suggestionsList.appendChild(li);
                });
            })
            .catch(err => {
                console.error('Autocomplete error:', err);
                suggestionsList.innerHTML = "";
            });
    }

    // Ingredient input - autocomplete and instant search
    ingredientInput.addEventListener("keyup", () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchSuggestions(ingredientInput.value);
            performSearch();
        }, 300);
    });

    // Close suggestions when clicking outside
    document.addEventListener('click', (e) => {
        if (!ingredientInput.contains(e.target) && !suggestionsList.contains(e.target)) {
            suggestionsList.innerHTML = "";
        }
    });

    // Add listeners to other form fields for instant filtering
    const keywordInput = document.querySelector('input[name="keyword"]');
    const cuisineInput = document.querySelector('input[name="cuisine"]');
    const difficultySelect = document.querySelector('select[name="difficulty"]');

    if (keywordInput) {
        keywordInput.addEventListener('keyup', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(performSearch, 300);
        });
    }

    if (cuisineInput) {
        cuisineInput.addEventListener('keyup', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(performSearch, 300);
        });
    }

    if (difficultySelect) {
        difficultySelect.addEventListener('change', performSearch);
    }

    // Prevent form submission (since we're using AJAX)
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            performSearch();
        });
    }
});
