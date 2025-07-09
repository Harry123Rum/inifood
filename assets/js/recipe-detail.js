// Recipe Detail Page JavaScript

document.addEventListener("DOMContentLoaded", () => {
  initializeRating()
  initializeTimer()
  initializeIngredientChecklist()
})

function initializeRating() {
  const ratingElement = document.querySelector(".rating.interactive")
  if (!ratingElement) return

  const stars = ratingElement.querySelectorAll(".star")
  const ratingInput = document.getElementById("ratingInput")
  let currentRating = Number.parseInt(ratingElement.dataset.rating) || 0

  stars.forEach((star, index) => {
    const rating = index + 1

    star.addEventListener("click", () => {
      currentRating = rating
      updateStars(stars, rating)
      ratingInput.value = rating
      submitRating(rating)
    })

    star.addEventListener("mouseenter", () => {
      updateStars(stars, rating)
    })
  })

  ratingElement.addEventListener("mouseleave", () => {
    updateStars(stars, currentRating)
  })

  // Initialize stars
  updateStars(stars, currentRating)
}

function updateStars(stars, rating) {
  stars.forEach((star, index) => {
    if (index < rating) {
      star.classList.add("filled")
    } else {
      star.classList.remove("filled")
    }
  })
}

async function submitRating(rating) {
  try {
    const formData = new FormData()
    formData.append("rating", rating)

    const response = await fetch(window.location.href, {
      method: "POST",
      body: formData,
    })

    if (response.ok) {
      showToast("Rating berhasil disimpan!", "success")
      // Reload page after a short delay to update the average rating
      setTimeout(() => {
        window.location.reload()
      }, 1500)
    } else {
      showToast("Gagal menyimpan rating. Silakan coba lagi.", "error")
    }
  } catch (error) {
    console.error("Rating submission error:", error)
    showToast("Terjadi kesalahan. Silakan coba lagi.", "error")
  }
}

function initializeTimer() {
  const timer = document.getElementById("cookingTimer")
  if (!timer) return

  const display = timer.querySelector(".timer-display")
  const input = timer.querySelector(".timer-input")
  const startBtn = timer.querySelector(".timer-start")
  const pauseBtn = timer.querySelector(".timer-pause")
  const resetBtn = timer.querySelector(".timer-reset")

  let timeLeft = 0
  let isRunning = false
  let interval = null

  startBtn.addEventListener("click", () => {
    const minutes = Number.parseInt(input.value)
    if (!minutes || minutes <= 0) {
      showToast("Masukkan waktu yang valid!", "error")
      return
    }

    if (!isRunning) {
      if (timeLeft === 0) {
        timeLeft = minutes * 60 // Convert to seconds
      }
      startTimer()
    }
  })

  pauseBtn.addEventListener("click", () => {
    pauseTimer()
  })

  resetBtn.addEventListener("click", () => {
    resetTimer()
  })

  function startTimer() {
    isRunning = true
    startBtn.disabled = true
    pauseBtn.disabled = false
    input.disabled = true

    interval = setInterval(() => {
      timeLeft--
      updateDisplay()

      if (timeLeft <= 0) {
        timerComplete()
      }
    }, 1000)
  }

  function pauseTimer() {
    isRunning = false
    startBtn.disabled = false
    pauseBtn.disabled = true
    clearInterval(interval)
  }

  function resetTimer() {
    isRunning = false
    timeLeft = 0
    startBtn.disabled = false
    pauseBtn.disabled = true
    input.disabled = false
    clearInterval(interval)
    updateDisplay()
  }

  function updateDisplay() {
    const minutes = Math.floor(timeLeft / 60)
    const seconds = timeLeft % 60
    display.textContent = `${minutes.toString().padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`
  }

  function timerComplete() {
    resetTimer()
    showNotification("Timer selesai! Waktu memasak telah habis.")
    playNotificationSound()
    showToast("Timer selesai!", "success")
  }

  function showNotification(message) {
    if ("Notification" in window && Notification.permission === "granted") {
      new Notification("IniFood Timer", {
        body: message,
        icon: "/favicon.ico",
      })
    }
  }

  function playNotificationSound() {
    // Create a simple beep sound
    const audioContext = new (window.AudioContext || window.webkitAudioContext)()
    const oscillator = audioContext.createOscillator()
    const gainNode = audioContext.createGain()

    oscillator.connect(gainNode)
    gainNode.connect(audioContext.destination)

    oscillator.frequency.value = 800
    oscillator.type = "sine"

    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime)
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5)

    oscillator.start(audioContext.currentTime)
    oscillator.stop(audioContext.currentTime + 0.5)
  }
}

function initializeIngredientChecklist() {
  const checkboxes = document.querySelectorAll(".ingredient-checkbox")

  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", (e) => {
      const ingredientItem = e.target.closest(".ingredient-item")
      const span = ingredientItem.querySelector("span")

      if (e.target.checked) {
        span.style.textDecoration = "line-through"
        span.style.opacity = "0.6"
        ingredientItem.style.background = "#e8f5e8"
      } else {
        span.style.textDecoration = "none"
        span.style.opacity = "1"
        ingredientItem.style.background = "#f8f9fa"
      }
    })
  })
}

// Print recipe functionality
function printRecipe() {
  const printWindow = window.open("", "_blank")
  const recipeContent = document.querySelector(".recipe-content").innerHTML
  const recipeHeader = document.querySelector(".recipe-info-section").innerHTML

  printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print Recipe</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .recipe-title { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
                .ingredients-section, .instructions-section { margin-bottom: 30px; }
                .ingredient-item { margin-bottom: 5px; }
                .instruction-step { margin-bottom: 15px; }
                .step-number { font-weight: bold; margin-right: 10px; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>
            <div class="recipe-header">${recipeHeader}</div>
            <div class="recipe-content">${recipeContent}</div>
        </body>
        </html>
    `)

  printWindow.document.close()
  printWindow.print()
}

// Share recipe functionality
function shareRecipe() {
  if (navigator.share) {
    navigator
      .share({
        title: document.querySelector(".recipe-title").textContent,
        text: document.querySelector(".recipe-description").textContent,
        url: window.location.href,
      })
      .catch(console.error)
  } else {
    // Fallback: copy to clipboard
    navigator.clipboard
      .writeText(window.location.href)
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

// Request notification permission on page load
if ("Notification" in window && Notification.permission === "default") {
  Notification.requestPermission()
}
