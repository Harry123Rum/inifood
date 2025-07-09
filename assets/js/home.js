// Home page specific JavaScript

document.addEventListener("DOMContentLoaded", () => {
  loadPopularRecipes()
  setupNewsletterForm()
})

async function loadPopularRecipes() {
  const container = document.getElementById("popularRecipes")
  if (!container) return

  try {
    // Show loading
    container.innerHTML = '<div class="loading"><div class="spinner"></div></div>'

    const response = await fetch("api/popular-recipes.php")
    const recipes = await response.json()

    if (recipes.length === 0) {
      container.innerHTML = '<p class="text-center">Belum ada resep populer.</p>'
      return
    }

    container.innerHTML = recipes
      .map(
        (recipe) => `
            <div class="recipe-card" onclick="window.location.href='recipe-detail.php?id=${recipe.id}'">
                <img src="${recipe.image_url || "/placeholder.svg?height=200&width=300"}" 
                     alt="${recipe.title}" class="recipe-image">
                <div class="recipe-info">
                    <h3 class="recipe-title">${recipe.title}</h3>
                    <p class="recipe-description">${recipe.description}</p>
                    <div class="recipe-meta">
                        <div class="recipe-rating">
                            <span class="star ${recipe.avg_rating >= 1 ? "filled" : ""}">★</span>
                            <span class="star ${recipe.avg_rating >= 2 ? "filled" : ""}">★</span>
                            <span class="star ${recipe.avg_rating >= 3 ? "filled" : ""}">★</span>
                            <span class="star ${recipe.avg_rating >= 4 ? "filled" : ""}">★</span>
                            <span class="star ${recipe.avg_rating >= 5 ? "filled" : ""}">★</span>
                            <span>(${recipe.rating_count})</span>
                        </div>
                        <div class="recipe-time">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12,6 12,12 16,14"></polyline>
                            </svg>
                            ${recipe.prep_time + recipe.cook_time} min
                        </div>
                    </div>
                </div>
            </div>
        `,
      )
      .join("")
  } catch (error) {
    console.error("Error loading popular recipes:", error)
    container.innerHTML = '<p class="text-center">Gagal memuat resep populer.</p>'
  }
}

function setupNewsletterForm() {
  const form = document.querySelector(".newsletter-form")
  if (!form) return

  form.addEventListener("submit", async (e) => {
    e.preventDefault()

    const email = form.querySelector('input[type="email"]').value
    const button = form.querySelector("button")
    const originalText = button.textContent

    try {
      button.textContent = "Berlangganan..."
      button.disabled = true

      const response = await fetch("api/newsletter.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ email: email }),
      })

      const result = await response.json()

      if (result.success) {
        showToast("Berhasil berlangganan newsletter!", "success")
        form.reset()
      } else {
        showToast(result.message || "Gagal berlangganan newsletter.", "error")
      }
    } catch (error) {
      console.error("Newsletter subscription error:", error)
      showToast("Terjadi kesalahan. Silakan coba lagi.", "error")
    } finally {
      button.textContent = originalText
      button.disabled = false
    }
  })
}

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault()
    const target = document.querySelector(this.getAttribute("href"))
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      })
    }
  })
})

// Add scroll effect to navbar
window.addEventListener("scroll", () => {
  const header = document.querySelector(".header")
  if (window.scrollY > 100) {
    header.classList.add("scrolled")
  } else {
    header.classList.remove("scrolled")
  }
})

function showToast(message, type = "success") {
  const toastContainer = document.getElementById("toast-container")

  if (!toastContainer) {
    const container = document.createElement("div")
    container.id = "toast-container"
    document.body.appendChild(container)
  }

  const toast = document.createElement("div")
  toast.classList.add("toast", `toast-${type}`)
  toast.textContent = message

  toastContainer.appendChild(toast)

  setTimeout(() => {
    toast.remove()
  }, 3000)
}
