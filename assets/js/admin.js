// Admin dashboard JavaScript

document.addEventListener("DOMContentLoaded", () => {
  initializeAdmin()
})

function initializeAdmin() {
  // Add confirmation to action buttons
  const actionForms = document.querySelectorAll('form[method="POST"]')
  actionForms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const action = e.target.querySelector('button[type="submit"][name="action"]')
      if (action && action.value === "reject") {
        if (!confirm("Yakin ingin menolak resep ini?")) {
          e.preventDefault()
        }
      } else if (action && action.value === "delete") {
        if (!confirm("Yakin ingin menghapus resep ini? Tindakan ini tidak dapat dibatalkan!")) {
          e.preventDefault()
        }
      }
    })
  })

  // Auto-refresh stats every 30 seconds
  setInterval(refreshStats, 30000)
}

async function viewRecipe(recipeId) {
  const modal = document.getElementById("recipeModal")
  const modalBody = document.getElementById("modalBody")
  const modalTitle = document.getElementById("modalTitle")

  // Show loading
  modalBody.innerHTML = '<div class="loading"><div class="spinner"></div></div>'
  modal.classList.add("show")

  try {
    const response = await fetch(`../api/recipe-detail.php?id=${recipeId}`)
    const recipe = await response.json()

    if (recipe.error) {
      modalBody.innerHTML = `<p class="error">Error: ${recipe.error}</p>`
      return
    }

    modalTitle.textContent = recipe.title

    // Construct image path dengan pengecekan yang lebih baik
    let imageSrc = "/placeholder.svg?height=300&width=400"
    if (recipe.image_url) {
      // Jika path sudah dimulai dengan /, gunakan langsung
      if (recipe.image_url.startsWith("/")) {
        imageSrc = recipe.image_url
      } else {
        // Jika path relatif, tambahkan ../
        imageSrc = "../" + recipe.image_url
      }
    }

    modalBody.innerHTML = `
    <div class="recipe-detail-modal">
        <div class="recipe-header">
            <img src="${imageSrc}" 
                 alt="${recipe.title}" 
                 class="recipe-image"
                 onerror="this.src='/placeholder.svg?height=300&width=400';">
            <div class="recipe-info">
                <h3>${recipe.title}</h3>
                <p class="recipe-category">Kategori: ${recipe.category_name}</p>
                <p class="recipe-author">Oleh: ${recipe.author}</p>
                <p class="recipe-date">Dibuat: ${formatDate(recipe.created_at)}</p>
                <span class="recipe-status status-${recipe.status}">
                    ${getStatusText(recipe.status)}
                </span>
            </div>
        </div>
        
        <div class="recipe-meta">
            <div class="meta-item">
                <strong>Waktu Persiapan:</strong> ${recipe.prep_time} menit
            </div>
            <div class="meta-item">
                <strong>Waktu Memasak:</strong> ${recipe.cook_time} menit
            </div>
            <div class="meta-item">
                <strong>Porsi:</strong> ${recipe.servings} orang
            </div>
            <div class="meta-item">
                <strong>Kesulitan:</strong> ${recipe.difficulty}
            </div>
        </div>
        
        <div class="recipe-description">
            <h4>Deskripsi</h4>
            <p>${recipe.description}</p>
        </div>
        
        <div class="recipe-ingredients">
            <h4>Bahan-bahan</h4>
            <ul>
                ${recipe.ingredients
                  .split("\n")
                  .filter((ingredient) => ingredient.trim())
                  .map((ingredient) => `<li>${ingredient.trim()}</li>`)
                  .join("")}
            </ul>
        </div>
        
        <div class="recipe-instructions">
            <h4>Cara Membuat</h4>
            <ol>
                ${recipe.instructions
                  .split("\n")
                  .filter((instruction) => instruction.trim())
                  .map((instruction) => `<li>${instruction.trim()}</li>`)
                  .join("")}
            </ol>
        </div>
    </div>
`
  } catch (error) {
    console.error("Error loading recipe:", error)
    modalBody.innerHTML = '<p class="error">Gagal memuat detail resep.</p>'
  }
}

function closeModal() {
  const modal = document.getElementById("recipeModal")
  modal.classList.remove("show")
}

// Close modal when clicking outside
document.addEventListener("click", (e) => {
  const modal = document.getElementById("recipeModal")
  if (e.target === modal) {
    closeModal()
  }
})

// Close modal with Escape key
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    closeModal()
  }
})

async function refreshStats() {
  try {
    const response = await fetch("../api/admin-stats.php")
    const stats = await response.json()

    // Update stat cards
    const pendingCard = document.querySelector(".stat-card.pending .stat-info h3")
    const approvedCard = document.querySelector(".stat-card.approved .stat-info h3")
    const usersCard = document.querySelector(".stat-card.users .stat-info h3")
    const ratingsCard = document.querySelector(".stat-card.ratings .stat-info h3")

    if (pendingCard) pendingCard.textContent = stats.pending_recipes
    if (approvedCard) approvedCard.textContent = stats.approved_recipes
    if (usersCard) usersCard.textContent = stats.total_users
    if (ratingsCard) ratingsCard.textContent = stats.total_ratings
  } catch (error) {
    console.error("Error refreshing stats:", error)
  }
}

function formatDate(dateString) {
  const date = new Date(dateString)
  const options = {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  }
  return date.toLocaleDateString("id-ID", options)
}

function getStatusText(status) {
  switch (status) {
    case "pending":
      return "Menunggu"
    case "approved":
      return "Disetujui"
    case "rejected":
      return "Ditolak"
    default:
      return status
  }
}

// Bulk actions
function selectAllRecipes() {
  const checkboxes = document.querySelectorAll('.recipe-row input[type="checkbox"]')
  checkboxes.forEach((checkbox) => {
    checkbox.checked = true
  })
}

function deselectAllRecipes() {
  const checkboxes = document.querySelectorAll('.recipe-row input[type="checkbox"]')
  checkboxes.forEach((checkbox) => {
    checkbox.checked = false
  })
}

async function bulkApprove() {
  const selectedRecipes = getSelectedRecipes()
  if (selectedRecipes.length === 0) {
    alert("Pilih resep yang ingin disetujui")
    return
  }

  if (!confirm(`Setujui ${selectedRecipes.length} resep yang dipilih?`)) {
    return
  }

  try {
    const response = await fetch("../api/bulk-action.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: "approve",
        recipe_ids: selectedRecipes,
      }),
    })

    const result = await response.json()
    if (result.success) {
      location.reload()
    } else {
      alert("Gagal memproses resep: " + result.message)
    }
  } catch (error) {
    console.error("Bulk approve error:", error)
    alert("Terjadi kesalahan saat memproses resep")
  }
}

async function bulkReject() {
  const selectedRecipes = getSelectedRecipes()
  if (selectedRecipes.length === 0) {
    alert("Pilih resep yang ingin ditolak")
    return
  }

  if (!confirm(`Tolak ${selectedRecipes.length} resep yang dipilih?`)) {
    return
  }

  try {
    const response = await fetch("../api/bulk-action.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: "reject",
        recipe_ids: selectedRecipes,
      }),
    })

    const result = await response.json()
    if (result.success) {
      location.reload()
    } else {
      alert("Gagal memproses resep: " + result.message)
    }
  } catch (error) {
    console.error("Bulk reject error:", error)
    alert("Terjadi kesalahan saat memproses resep")
  }
}

function getSelectedRecipes() {
  const checkboxes = document.querySelectorAll('.recipe-row input[type="checkbox"]:checked')
  return Array.from(checkboxes).map((checkbox) => Number.parseInt(checkbox.value))
}

// Image preview function for edit form
function previewImage(input) {
  const previewContainer = document.getElementById("imagePreview")
  const fileUploadContainer = input.closest(".file-upload-container")

  if (input.files && input.files[0]) {
    const file = input.files[0]

    // Validate file type
    const allowedTypes = ["image/jpeg", "image/jpg", "image/png", "image/gif", "image/webp"]
    if (!allowedTypes.includes(file.type)) {
      alert("Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.")
      input.value = ""
      previewContainer.innerHTML = ""
      fileUploadContainer.classList.remove("file-selected")
      return
    }

    // Validate file size (5MB)
    if (file.size > 5 * 1024 * 1024) {
      alert("Ukuran file terlalu besar. Maksimal 5MB.")
      input.value = ""
      previewContainer.innerHTML = ""
      fileUploadContainer.classList.remove("file-selected")
      return
    }

    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      previewContainer.innerHTML = `
        <img src="${e.target.result}" alt="Preview resep" class="image-preview" style="max-width: 300px; height: 200px; object-fit: cover; border-radius: 8px; margin-top: 1rem;">
        <p class="file-info" style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">
          <strong>${file.name}</strong><br>
          <small>${(file.size / 1024 / 1024).toFixed(2)} MB</small>
        </p>
      `
    }
    reader.readAsDataURL(file)

    // Add selected state
    fileUploadContainer.classList.add("file-selected")

    // Update label text
    const label = fileUploadContainer.querySelector(".file-upload-label span")
    if (label) {
      label.textContent = "Foto dipilih: " + file.name
    }
  } else {
    previewContainer.innerHTML = ""
    fileUploadContainer.classList.remove("file-selected")

    // Reset label text
    const label = fileUploadContainer.querySelector(".file-upload-label span")
    if (label) {
      label.textContent = "Pilih Foto Baru"
    }
  }
}
