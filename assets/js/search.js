// Unified AJAX Search for Recipe App
// Works on both index.php and search.php

document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const searchResultsDiv = document.getElementById('search-results');
    const ingredientInput = document.querySelector("#ingredient");
    const suggestionsList = document.getElementById("suggestions");
    let debounceTimer;

    // Exit if required elements don't exist
    if (!searchResultsDiv) return;

    // Function to perform AJAX search
    function performSearch() {
        const keyword = document.querySelector('input[name="keyword"]')?.value || '';
        const cuisine = document.querySelector('input[name="cuisine"]')?.value || '';
        const difficulty = document.querySelector('select[name="difficulty"]')?.value || '';
        const ingredient = ingredientInput?.value || '';

        // Build query string
        const params = new URLSearchParams();
        if (keyword) params.append('keyword', keyword);
        if (cuisine) params.append('cuisine', cuisine);
        if (difficulty) params.append('difficulty', difficulty);
        if (ingredient) params.append('ingredient', ingredient);

        // Show loading state
        searchResultsDiv.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Searching delicious recipes...</p>
            </div>
        `;

        // Update result count display
        updateResultCount('...');

        // Fetch filtered results
        fetch(`ajax_search.php?${params.toString()}`)
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(data => {
                searchResultsDiv.innerHTML = data.html;
                updateResultCount(data.count);
            })
            .catch(err => {
                console.error('Search error:', err);
                searchResultsDiv.innerHTML = `
                    <div class="error-state">
                        <div class="error-icon">⚠️</div>
                        <h3>Oops! Something went wrong</h3>
                        <p>Unable to load recipes. Please try again.</p>
                    </div>
                `;
                updateResultCount(0);
            });
    }

    // Update result count in header
    function updateResultCount(count) {
        const countDisplay = document.getElementById('result-count');
        if (countDisplay) {
            countDisplay.textContent = count;
        }
    }

    // Function to fetch autocomplete suggestions
    function fetchSuggestions(term) {
        if (!ingredientInput || !suggestionsList) return;
        
        if (!term.trim()) {
            suggestionsList.innerHTML = "";
            suggestionsList.classList.remove('active');
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
                    suggestionsList.classList.remove('active');
                    return;
                }
                
                suggestionsList.classList.add('active');
                data.forEach(item => {
                    const li = document.createElement("li");
                    li.innerHTML = `<span class="suggestion-icon">🥗</span>${item}`;
                    li.onclick = () => {
                        ingredientInput.value = item;
                        suggestionsList.innerHTML = "";
                        suggestionsList.classList.remove('active');
                        performSearch();
                    };
                    suggestionsList.appendChild(li);
                });
            })
            .catch(err => {
                console.error('Autocomplete error:', err);
                suggestionsList.innerHTML = "";
                suggestionsList.classList.remove('active');
            });
    }

    // Ingredient input - autocomplete and instant search
    if (ingredientInput) {
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
                suggestionsList.classList.remove('active');
            }
        });
    }

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

    // Prevent form submission and use AJAX instead
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            performSearch();
        });
    }

    // Clear all filters button
    const clearBtn = document.getElementById('clearFilters');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            if (searchForm) searchForm.reset();
            performSearch();
        });
    }
});
