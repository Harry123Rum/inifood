// Main JavaScript functionality

// Update the toggleUserMenu function and click handler to check if elements exist first

// Toggle user menu dropdown
function toggleUserMenu() {
  const dropdown = document.getElementById("userDropdown")
  if (dropdown) {
    dropdown.classList.toggle("show")
  }
}

// Close dropdown when clicking outside - add null checks
document.addEventListener("click", (event) => {
  const userMenu = document.querySelector(".user-menu")
  const dropdown = document.getElementById("userDropdown")

  // Add null checks before using contains
  if (userMenu && dropdown && !userMenu.contains(event.target)) {
    dropdown.classList.remove("show")
  }
})

// Search functionality with live search
class SearchManager {
  constructor() {
    this.searchInput = null
    this.suggestionsContainer = null
    this.debounceTimer = null
    this.init()
  }

  init() {
    const searchInput = document.querySelector(".search-input")
    if (searchInput) {
      this.searchInput = searchInput
      this.createSuggestionsContainer()
      this.bindEvents()
    }
  }

  createSuggestionsContainer() {
    this.suggestionsContainer = document.createElement("div")
    this.suggestionsContainer.className = "search-suggestions"
    this.searchInput.parentNode.appendChild(this.suggestionsContainer)
  }

  bindEvents() {
    this.searchInput.addEventListener("input", (e) => {
      clearTimeout(this.debounceTimer)
      this.debounceTimer = setTimeout(() => {
        this.handleSearch(e.target.value)
      }, 300)
    })

    this.searchInput.addEventListener("focus", () => {
      if (this.searchInput.value.trim()) {
        this.suggestionsContainer.classList.add("show")
      }
    })

    document.addEventListener("click", (e) => {
      if (!this.searchInput.parentNode.contains(e.target)) {
        this.suggestionsContainer.classList.remove("show")
      }
    })
  }

  async handleSearch(query) {
    if (query.length < 2) {
      this.suggestionsContainer.classList.remove("show")
      return
    }

    try {
      const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}`)
      const suggestions = await response.json()
      this.displaySuggestions(suggestions)
    } catch (error) {
      console.error("Search error:", error)
    }
  }

  displaySuggestions(suggestions) {
    if (suggestions.length === 0) {
      this.suggestionsContainer.classList.remove("show")
      return
    }

    this.suggestionsContainer.innerHTML = suggestions
      .map(
        (item) =>
          `<div class="suggestion-item" onclick="window.location.href='recipe-detail.php?id=${item.id}'">${item.title}</div>`,
      )
      .join("")

    this.suggestionsContainer.classList.add("show")
  }
}

// Rating system
class RatingSystem {
  constructor() {
    this.init()
  }

  init() {
    document.querySelectorAll(".rating").forEach((rating) => {
      this.setupRating(rating)
    })
  }

  setupRating(ratingElement) {
    const stars = ratingElement.querySelectorAll(".star")
    const isInteractive = ratingElement.classList.contains("interactive")

    if (isInteractive) {
      stars.forEach((star, index) => {
        star.addEventListener("click", () => {
          this.setRating(ratingElement, index + 1)
        })

        star.addEventListener("mouseenter", () => {
          this.highlightStars(stars, index + 1)
        })
      })

      ratingElement.addEventListener("mouseleave", () => {
        const currentRating = Number.parseInt(ratingElement.dataset.rating) || 0
        this.highlightStars(stars, currentRating)
      })
    }
  }

  setRating(ratingElement, rating) {
    ratingElement.dataset.rating = rating
    const stars = ratingElement.querySelectorAll(".star")
    this.highlightStars(stars, rating)

    // Trigger custom event
    const event = new CustomEvent("ratingChanged", {
      detail: { rating: rating },
    })
    ratingElement.dispatchEvent(event)
  }

  highlightStars(stars, rating) {
    stars.forEach((star, index) => {
      if (index < rating) {
        star.classList.add("filled")
      } else {
        star.classList.remove("filled")
      }
    })
  }
}

// Timer functionality
class Timer {
  constructor(element) {
    this.element = element
    this.timeLeft = 0
    this.isRunning = false
    this.interval = null
    this.init()
  }

  init() {
    const display = this.element.querySelector(".timer-display")
    const startBtn = this.element.querySelector(".timer-start")
    const pauseBtn = this.element.querySelector(".timer-pause")
    const resetBtn = this.element.querySelector(".timer-reset")
    const input = this.element.querySelector(".timer-input")

    if (startBtn) {
      startBtn.addEventListener("click", () => {
        if (input && input.value) {
          this.timeLeft = Number.parseInt(input.value) * 60 // Convert minutes to seconds
        }
        this.start()
      })
    }

    if (pauseBtn) {
      pauseBtn.addEventListener("click", () => this.pause())
    }

    if (resetBtn) {
      resetBtn.addEventListener("click", () => this.reset())
    }
  }

  start() {
    if (this.timeLeft <= 0) return

    this.isRunning = true
    this.interval = setInterval(() => {
      this.timeLeft--
      this.updateDisplay()

      if (this.timeLeft <= 0) {
        this.complete()
      }
    }, 1000)

    this.updateButtons()
  }

  pause() {
    this.isRunning = false
    clearInterval(this.interval)
    this.updateButtons()
  }

  reset() {
    this.isRunning = false
    clearInterval(this.interval)
    this.timeLeft = 0
    this.updateDisplay()
    this.updateButtons()
  }

  complete() {
    this.isRunning = false
    clearInterval(this.interval)
    this.updateButtons()

    // Show notification
    this.showNotification("Timer selesai!")

    // Play sound if available
    this.playSound()
  }

  updateDisplay() {
    const display = this.element.querySelector(".timer-display")
    if (display) {
      const minutes = Math.floor(this.timeLeft / 60)
      const seconds = this.timeLeft % 60
      display.textContent = `${minutes.toString().padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`
    }
  }

  updateButtons() {
    const startBtn = this.element.querySelector(".timer-start")
    const pauseBtn = this.element.querySelector(".timer-pause")

    if (startBtn) startBtn.disabled = this.isRunning
    if (pauseBtn) pauseBtn.disabled = !this.isRunning
  }

  showNotification(message) {
    if ("Notification" in window && Notification.permission === "granted") {
      new Notification("IniFood Timer", {
        body: message,
        icon: "/favicon.ico",
      })
    } else {
      alert(message)
    }
  }

  playSound() {
    const audio = new Audio(
      "data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT",
    )
    audio.play().catch(() => {
      // Ignore audio play errors
    })
  }
}

// Form validation
class FormValidator {
  constructor(form) {
    this.form = form
    this.init()
  }

  init() {
    this.form.addEventListener("submit", (e) => {
      if (!this.validate()) {
        e.preventDefault()
      }
    })

    // Real-time validation
    this.form.querySelectorAll("input, textarea, select").forEach((field) => {
      field.addEventListener("blur", () => {
        this.validateField(field)
      })
    })
  }

  validate() {
    let isValid = true
    const fields = this.form.querySelectorAll("input, textarea, select")

    fields.forEach((field) => {
      if (!this.validateField(field)) {
        isValid = false
      }
    })

    return isValid
  }

  validateField(field) {
    const value = field.value.trim()
    const type = field.type
    const required = field.hasAttribute("required")
    let isValid = true
    let message = ""

    // Clear previous errors
    this.clearFieldError(field)

    // Required validation
    if (required && !value) {
      isValid = false
      message = "Field ini wajib diisi"
    }

    // Email validation
    if (type === "email" && value && !this.isValidEmail(value)) {
      isValid = false
      message = "Format email tidak valid"
    }

    // Password validation
    if (type === "password" && value && value.length < 6) {
      isValid = false
      message = "Password minimal 6 karakter"
    }

    // Show error if invalid
    if (!isValid) {
      this.showFieldError(field, message)
    }

    return isValid
  }

  isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }

  showFieldError(field, message) {
    field.classList.add("error")

    let errorElement = field.parentNode.querySelector(".field-error")
    if (!errorElement) {
      errorElement = document.createElement("div")
      errorElement.className = "field-error"
      field.parentNode.appendChild(errorElement)
    }

    errorElement.textContent = message
  }

  clearFieldError(field) {
    field.classList.remove("error")
    const errorElement = field.parentNode.querySelector(".field-error")
    if (errorElement) {
      errorElement.remove()
    }
  }
}

// Update the initialization to check if elements exist before creating instances

// Initialize components when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  // Initialize search only if search input exists
  const searchInput = document.querySelector(".search-input")
  if (searchInput) {
    new SearchManager()
  }

  // Initialize rating systems only if rating elements exist
  const ratingElements = document.querySelectorAll(".rating")
  if (ratingElements.length > 0) {
    new RatingSystem()
  }

  // Initialize timers only if timer elements exist
  const timerElements = document.querySelectorAll(".timer")
  timerElements.forEach((timer) => {
    new Timer(timer)
  })

  // Initialize form validation only if forms exist
  const forms = document.querySelectorAll("form")
  forms.forEach((form) => {
    // Skip auth forms as they have their own validation
    if (!form.classList.contains("auth-form")) {
      new FormValidator(form)
    }
  })

  // Request notification permission
  if ("Notification" in window && Notification.permission === "default") {
    Notification.requestPermission()
  }
})

// Utility functions
function formatTime(seconds) {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60

  if (hours > 0) {
    return `${hours}j ${minutes}m`
  } else if (minutes > 0) {
    return `${minutes}m`
  } else {
    return `${secs}s`
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

function showToast(message, type = "info") {
  const toast = document.createElement("div")
  toast.className = `toast toast-${type}`
  toast.textContent = message

  document.body.appendChild(toast)

  // Show toast
  setTimeout(() => toast.classList.add("show"), 100)

  // Hide toast
  setTimeout(() => {
    toast.classList.remove("show")
    setTimeout(() => toast.remove(), 300)
  }, 3000)
}

// Export for use in other files
window.RecipeApp = {
  SearchManager,
  RatingSystem,
  Timer,
  FormValidator,
  formatTime,
  formatDate,
  showToast,
}
