// Recipes page specific JavaScript

document.addEventListener("DOMContentLoaded", () => {
  initializeSearch()
  setupCategoryFilter()
})

function initializeSearch() {
  const searchInput = document.getElementById("searchInput")
  const suggestionsContainer = document.getElementById("searchSuggestions")
  let debounceTimer

  if (!searchInput || !suggestionsContainer) return

  searchInput.addEventListener("input", (e) => {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
      handleSearch(e.target.value, suggestionsContainer)
    }, 300)
  })

  searchInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      performSearch(searchInput.value)
    }
  })

  searchInput.addEventListener("focus", () => {
    if (searchInput.value.trim() && suggestionsContainer.children.length > 0) {
      suggestionsContainer.classList.add("show")
    }
  })

  document.addEventListener("click", (e) => {
    if (!searchInput.parentNode.contains(e.target)) {
      suggestionsContainer.classList.remove("show")
    }
  })
}

async function handleSearch(query, suggestionsContainer) {
  if (query.length < 2) {
    suggestionsContainer.classList.remove("show")
    return
  }

  try {
    const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}`)
    const suggestions = await response.json()

    displaySuggestions(suggestions, suggestionsContainer)
  } catch (error) {
    console.error("Search error:", error)
  }
}

function displaySuggestions(suggestions, container) {
  if (suggestions.length === 0) {
    container.classList.remove("show")
    return
  }

  container.innerHTML = suggestions
    .map(
      (item) =>
        `<div class="suggestion-item" onclick="window.location.href='recipe-detail.php?id=${item.id}'">
          <div class="suggestion-title">${item.title}</div>
          <div class="suggestion-category">${item.category_name}</div>
        </div>`,
    )
    .join("")

  container.classList.add("show")
}

function performSearch(query) {
  const currentUrl = new URL(window.location)
  if (query.trim()) {
    currentUrl.searchParams.set("search", query)
  } else {
    currentUrl.searchParams.delete("search")
  }
  window.location.href = currentUrl.toString()
}

function setupCategoryFilter() {
  const categoryTabs = document.querySelectorAll(".category-tab")

  categoryTabs.forEach((tab) => {
    tab.addEventListener("click", (e) => {
      e.preventDefault()

      // Remove active class from all tabs
      categoryTabs.forEach((t) => t.classList.remove("active"))

      // Add active class to clicked tab
      tab.classList.add("active")

      // Navigate to the URL
      window.location.href = tab.href
    })
  })
}

// Smooth scroll to top when changing categories
function scrollToTop() {
  window.scrollTo({
    top: 0,
    behavior: "smooth",
  })
}

// Add loading state for recipe cards
function showLoadingState() {
  const recipesGrid = document.querySelector(".recipes-grid")
  if (recipesGrid) {
    recipesGrid.innerHTML = '<div class="loading"><div class="spinner"></div></div>'
  }
}

// Filter recipes by difficulty (client-side)
function filterByDifficulty(difficulty) {
  const recipeCards = document.querySelectorAll(".recipe-card")

  recipeCards.forEach((card) => {
    const difficultyElement = card.querySelector(".recipe-difficulty")
    if (difficulty === "all" || difficultyElement.textContent.toLowerCase() === difficulty.toLowerCase()) {
      card.style.display = "block"
    } else {
      card.style.display = "none"
    }
  })
}

// Add to favorites (if user is logged in)
async function toggleFavorite(recipeId) {
  try {
    const response = await fetch("api/toggle-favorite.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ recipe_id: recipeId }),
    })

    const result = await response.json()

    if (result.success) {
      // Update UI to reflect favorite status
      const favoriteBtn = document.querySelector(`[data-recipe-id="${recipeId}"] .favorite-btn`)
      if (favoriteBtn) {
        favoriteBtn.classList.toggle("active")
      }

      showToast(result.message, "success")
    } else {
      showToast(result.message || "Gagal mengubah status favorit", "error")
    }
  } catch (error) {
    console.error("Favorite toggle error:", error)
    showToast("Terjadi kesalahan. Silakan coba lagi.", "error")
  }
}

function showToast(message, type = "info") {
  const toast = document.createElement("div")
  toast.className = `toast toast-${type}`
  toast.textContent = message

  // Add toast styles if not already present
  if (!document.querySelector("#toast-styles")) {
    const styles = document.createElement("style")
    styles.id = "toast-styles"
    styles.textContent = `
            .toast {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 20px;
                border-radius: 6px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            }
            .toast.show {
                transform: translateX(0);
            }
            .toast-success {
                background: #2ecc71;
            }
            .toast-error {
                background: #e74c3c;
            }
            .toast-info {
                background: #3498db;
            }
        `
    document.head.appendChild(styles)
  }

  document.body.appendChild(toast)

  // Show toast
  setTimeout(() => toast.classList.add("show"), 100)

  // Hide toast
  setTimeout(() => {
    toast.classList.remove("show")
    setTimeout(() => toast.remove(), 300)
  }, 3000)
}
