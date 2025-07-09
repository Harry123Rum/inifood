// Share page JavaScript

document.addEventListener("DOMContentLoaded", () => {
  initializeForm()
  setupImagePreview()
  setupAutoResize()
  setupFormProgress()
})

function initializeForm() {
  const form = document.getElementById("recipeForm")
  if (!form) return

  form.addEventListener("submit", handleFormSubmit)

  // Add real-time validation
  const inputs = form.querySelectorAll("input, textarea, select")
  inputs.forEach((input) => {
    input.addEventListener("blur", () => validateField(input))
    input.addEventListener("input", () => clearFieldError(input))
  })

  // Setup character counters
  setupCharacterCounters()
}

function handleFormSubmit(e) {
  const form = e.target
  const submitBtn = form.querySelector('button[type="submit"]')

  if (!validateForm(form)) {
    e.preventDefault()
    return
  }

  // Add loading state
  submitBtn.classList.add("loading")
  submitBtn.disabled = true

  // Form will submit normally, loading state will be cleared on page reload
}

function validateForm(form) {
  let isValid = true
  const inputs = form.querySelectorAll("input[required], textarea[required], select[required]")

  inputs.forEach((input) => {
    if (!validateField(input)) {
      isValid = false
    }
  })

  return isValid
}

function validateField(field) {
  const value = field.value.trim()
  const type = field.type
  const required = field.hasAttribute("required")
  let isValid = true
  let message = ""

  // Clear previous errors
  clearFieldError(field)

  // Required validation
  if (required && !value) {
    isValid = false
    message = "Field ini wajib diisi"
  }

  // Specific validations
  if (value) {
    switch (type) {
      case "email":
        if (!isValidEmail(value)) {
          isValid = false
          message = "Format email tidak valid"
        }
        break
      case "url":
        if (!isValidUrl(value)) {
          isValid = false
          message = "Format URL tidak valid"
        }
        break
      case "number":
        const num = Number.parseInt(value)
        if (num <= 0) {
          isValid = false
          message = "Nilai harus lebih dari 0"
        }
        break
    }

    // Text length validation
    if (field.name === "title" && value.length > 200) {
      isValid = false
      message = "Judul maksimal 200 karakter"
    }
  }

  // Show error if invalid
  if (!isValid) {
    showFieldError(field, message)
  }

  return isValid
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

function isValidUrl(url) {
  try {
    new URL(url)
    return true
  } catch {
    return false
  }
}

function showFieldError(field, message) {
  field.classList.add("error")

  let errorElement = field.parentNode.querySelector(".field-error")
  if (!errorElement) {
    errorElement = document.createElement("div")
    errorElement.className = "field-error"
    field.parentNode.appendChild(errorElement)
  }

  errorElement.textContent = message
}

function clearFieldError(field) {
  field.classList.remove("error")
  const errorElement = field.parentNode.querySelector(".field-error")
  if (errorElement) {
    errorElement.remove()
  }
}

function setupImagePreview() {
  // Remove the old URL-based preview setup since we're now using file upload
  // The preview is now handled by the previewImage function called from HTML
}

// Add this new function for file upload preview
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
        <img src="${e.target.result}" alt="Preview resep" class="image-preview">
        <p class="file-info">
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
    label.textContent = "Foto dipilih: " + file.name
  } else {
    previewContainer.innerHTML = ""
    fileUploadContainer.classList.remove("file-selected")

    // Reset label text
    const label = fileUploadContainer.querySelector(".file-upload-label span")
    label.textContent = "Pilih Foto Resep"
  }
}

function setupAutoResize() {
  const textareas = document.querySelectorAll("textarea")

  textareas.forEach((textarea) => {
    textarea.classList.add("auto-resize")

    function resize() {
      textarea.style.height = "auto"
      textarea.style.height = textarea.scrollHeight + "px"
    }

    textarea.addEventListener("input", resize)
    resize() // Initial resize
  })
}

function setupCharacterCounters() {
  const fieldsWithCounters = [
    { field: "title", max: 200 },
    { field: "description", max: 500 },
  ]

  fieldsWithCounters.forEach(({ field, max }) => {
    const input = document.getElementById(field)
    if (!input) return

    const counter = document.createElement("div")
    counter.className = "char-counter"
    input.parentNode.appendChild(counter)

    function updateCounter() {
      const length = input.value.length
      counter.textContent = `${length}/${max}`

      counter.classList.remove("warning", "error")
      if (length > max * 0.8) {
        counter.classList.add("warning")
      }
      if (length > max) {
        counter.classList.add("error")
      }
    }

    input.addEventListener("input", updateCounter)
    updateCounter() // Initial count
  })
}

function setupFormProgress() {
  const form = document.getElementById("recipeForm")
  if (!form) return

  // Create progress bar
  const progressContainer = document.createElement("div")
  progressContainer.className = "form-progress"
  progressContainer.innerHTML = '<div class="form-progress-bar"></div>'

  const firstSection = form.querySelector(".form-section-group")
  firstSection.parentNode.insertBefore(progressContainer, firstSection)

  const progressBar = progressContainer.querySelector(".form-progress-bar")
  const requiredFields = form.querySelectorAll("input[required], textarea[required], select[required]")

  function updateProgress() {
    const filledFields = Array.from(requiredFields).filter((field) => field.value.trim() !== "")
    const progress = (filledFields.length / requiredFields.length) * 100
    progressBar.style.width = `${progress}%`
  }

  requiredFields.forEach((field) => {
    field.addEventListener("input", updateProgress)
  })

  updateProgress() // Initial progress
}

function resetForm() {
  if (confirm("Apakah Anda yakin ingin mengosongkan form? Semua data akan hilang.")) {
    const form = document.getElementById("recipeForm")
    form.reset()

    // Clear all errors
    const errorElements = form.querySelectorAll(".field-error")
    errorElements.forEach((el) => el.remove())

    const errorFields = form.querySelectorAll(".error")
    errorFields.forEach((field) => field.classList.remove("error"))

    // Reset image preview
    const imagePreview = document.getElementById("imagePreview")
    if (imagePreview) {
      imagePreview.innerHTML = ""
    }

    // Reset file upload state
    const fileUploadContainer = form.querySelector(".file-upload-container")
    if (fileUploadContainer) {
      fileUploadContainer.classList.remove("file-selected")
      const label = fileUploadContainer.querySelector(".file-upload-label span")
      if (label) {
        label.textContent = "Pilih Foto Resep"
      }
    }

    // Reset progress
    const progressBar = form.querySelector(".form-progress-bar")
    if (progressBar) {
      progressBar.style.width = "0%"
    }

    // Reset character counters
    const counters = form.querySelectorAll(".char-counter")
    counters.forEach((counter) => {
      const field = counter.previousElementSibling
      if (field) {
        const max = field.id === "title" ? 200 : 500
        counter.textContent = `0/${max}`
        counter.classList.remove("warning", "error")
      }
    })

    // Focus first field
    const firstField = form.querySelector("input, textarea, select")
    if (firstField) {
      firstField.focus()
    }
  }
}

// Auto-save functionality (optional)
function setupAutoSave() {
  const form = document.getElementById("recipeForm")
  if (!form) return

  const inputs = form.querySelectorAll("input, textarea, select")
  const AUTOSAVE_KEY = "recipe_form_autosave"

  // Load saved data
  function loadSavedData() {
    try {
      const savedData = localStorage.getItem(AUTOSAVE_KEY)
      if (savedData) {
        const data = JSON.parse(savedData)
        Object.keys(data).forEach((key) => {
          const field = form.querySelector(`[name="${key}"]`)
          if (field) {
            field.value = data[key]
          }
        })
      }
    } catch (error) {
      console.error("Error loading saved data:", error)
    }
  }

  // Save form data
  function saveFormData() {
    const data = {}
    inputs.forEach((input) => {
      if (input.name) {
        data[input.name] = input.value
      }
    })
    localStorage.setItem(AUTOSAVE_KEY, JSON.stringify(data))
  }

  // Clear saved data
  function clearSavedData() {
    localStorage.removeItem(AUTOSAVE_KEY)
  }

  // Auto-save on input
  inputs.forEach((input) => {
    input.addEventListener("input", () => {
      setTimeout(saveFormData, 1000) // Debounce save
    })
  })

  // Clear on successful submit
  form.addEventListener("submit", clearSavedData)

  // Load saved data on page load
  loadSavedData()
}
