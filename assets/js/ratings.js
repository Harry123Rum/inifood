// Ratings page JavaScript

document.addEventListener("DOMContentLoaded", () => {
  initializeFilters()
  addScrollAnimations()
})

function filterByCategory(category) {
  const currentUrl = new URL(window.location)
  if (category === "all") {
    currentUrl.searchParams.delete("category")
  } else {
    currentUrl.searchParams.set("category", category)
  }
  window.location.href = currentUrl.toString()
}

function initializeFilters() {
  const filterBtns = document.querySelectorAll(".filter-btn")
  const categorySelect = document.querySelector(".category-select")

  filterBtns.forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault()

      // Remove active class from all buttons
      filterBtns.forEach((b) => b.classList.remove("active"))

      // Add active class to clicked button
      btn.classList.add("active")

      // Navigate to the URL
      window.location.href = btn.href
    })
  })

  if (categorySelect) {
    categorySelect.addEventListener("change", (e) => {
      filterByCategory(e.target.value)
    })
  }
}

function addScrollAnimations() {
  const recipeCards = document.querySelectorAll(".recipe-card")

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = "1"
          entry.target.style.transform = "translateY(0)"
        }
      })
    },
    {
      threshold: 0.1,
      rootMargin: "0px 0px -50px 0px",
    },
  )

  recipeCards.forEach((card, index) => {
    card.style.opacity = "0"
    card.style.transform = "translateY(30px)"
    card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`

    observer.observe(card)
  })
}

// Add favorite functionality
async function toggleFavorite(recipeId, element) {
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
      element.classList.toggle("active")
      showToast(result.message, "success")
    } else {
      showToast(result.message || "Gagal mengubah status favorit", "error")
    }
  } catch (error) {
    console.error("Favorite toggle error:", error)
    showToast("Terjadi kesalahan. Silakan coba lagi.", "error")
  }
}

// Share recipe functionality
function shareRecipe(recipeId, title) {
  const url = `${window.location.origin}/recipe-detail.php?id=${recipeId}`

  if (navigator.share) {
    navigator
      .share({
        title: title,
        url: url,
      })
      .catch(console.error)
  } else {
    // Fallback: copy to clipboard
    navigator.clipboard
      .writeText(url)
      .then(() => {
        showToast("Link resep berhasil disalin!", "success")
      })
      .catch(() => {
        showToast("Gagal menyalin link. Silakan salin manual.", "error")
      })
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

// Smooth scroll to top when changing filters
function scrollToTop() {
  window.scrollTo({
    top: 0,
    behavior: "smooth",
  })
}

// Add loading state for filter changes
function showLoadingState() {
  const recipesGrid = document.querySelector(".recipes-grid")
  if (recipesGrid) {
    recipesGrid.innerHTML = '<div class="loading"><div class="spinner"></div></div>'
  }
}
