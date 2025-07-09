// Authentication page JavaScript

document.addEventListener("DOMContentLoaded", () => {
  initializeAuthForms()
  initializePasswordStrength()
})

function initializeAuthForms() {
  const loginForm = document.getElementById("loginForm")
  const registerForm = document.getElementById("registerForm")

  if (loginForm) {
    loginForm.addEventListener("submit", handleFormSubmit)
  }

  if (registerForm) {
    registerForm.addEventListener("submit", handleFormSubmit)
    setupPasswordConfirmation()
  }
}

function showLoginForm() {
  const loginForm = document.getElementById("loginForm")
  const registerForm = document.getElementById("registerForm")
  const tabs = document.querySelectorAll(".auth-tab")

  loginForm.classList.remove("hidden")
  registerForm.classList.add("hidden")

  tabs[0].classList.add("active")
  tabs[1].classList.remove("active")
}

function showRegisterForm() {
  const loginForm = document.getElementById("loginForm")
  const registerForm = document.getElementById("registerForm")
  const tabs = document.querySelectorAll(".auth-tab")

  loginForm.classList.add("hidden")
  registerForm.classList.remove("hidden")

  tabs[0].classList.remove("active")
  tabs[1].classList.add("active")
}

function handleFormSubmit(e) {
  const form = e.target
  const submitBtn = form.querySelector('button[type="submit"]')

  // Add loading state
  submitBtn.classList.add("loading")
  submitBtn.disabled = true

  // Remove loading state after form submission
  setTimeout(() => {
    submitBtn.classList.remove("loading")
    submitBtn.disabled = false
  }, 2000)
}

function setupPasswordConfirmation() {
  const passwordInput = document.getElementById("reg_password")
  const confirmPasswordInput = document.getElementById("reg_confirm_password")

  if (!passwordInput || !confirmPasswordInput) return

  function validatePasswordMatch() {
    const password = passwordInput.value
    const confirmPassword = confirmPasswordInput.value

    if (confirmPassword && password !== confirmPassword) {
      confirmPasswordInput.setCustomValidity("Password tidak sama")
      showFieldError(confirmPasswordInput, "Password tidak sama")
    } else {
      confirmPasswordInput.setCustomValidity("")
      clearFieldError(confirmPasswordInput)
    }
  }

  passwordInput.addEventListener("input", validatePasswordMatch)
  confirmPasswordInput.addEventListener("input", validatePasswordMatch)
}

function initializePasswordStrength() {
  const passwordInput = document.getElementById("reg_password")
  if (!passwordInput) return

  // Create password strength indicator
  const strengthIndicator = document.createElement("div")
  strengthIndicator.className = "password-strength"
  strengthIndicator.innerHTML = '<div class="password-strength-bar"></div>'

  passwordInput.parentNode.appendChild(strengthIndicator)

  const strengthBar = strengthIndicator.querySelector(".password-strength-bar")

  passwordInput.addEventListener("input", (e) => {
    const password = e.target.value
    const strength = calculatePasswordStrength(password)

    strengthBar.className = "password-strength-bar"

    if (password.length === 0) {
      strengthBar.style.width = "0%"
    } else if (strength < 3) {
      strengthBar.classList.add("strength-weak")
    } else if (strength < 5) {
      strengthBar.classList.add("strength-medium")
    } else {
      strengthBar.classList.add("strength-strong")
    }
  })
}

function calculatePasswordStrength(password) {
  let strength = 0

  // Length check
  if (password.length >= 6) strength++
  if (password.length >= 8) strength++

  // Character variety checks
  if (/[a-z]/.test(password)) strength++
  if (/[A-Z]/.test(password)) strength++
  if (/[0-9]/.test(password)) strength++
  if (/[^A-Za-z0-9]/.test(password)) strength++

  return strength
}

function showFieldError(field, message) {
  clearFieldError(field)

  field.classList.add("error")

  const errorElement = document.createElement("span")
  errorElement.className = "field-error"
  errorElement.textContent = message

  field.parentNode.appendChild(errorElement)
}

function clearFieldError(field) {
  field.classList.remove("error")

  const errorElement = field.parentNode.querySelector(".field-error")
  if (errorElement) {
    errorElement.remove()
  }
}

// Email validation
function validateEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

// Real-time validation
document.addEventListener("DOMContentLoaded", () => {
  const emailInputs = document.querySelectorAll('input[type="email"]')
  const passwordInputs = document.querySelectorAll('input[type="password"]')

  emailInputs.forEach((input) => {
    input.addEventListener("blur", (e) => {
      const email = e.target.value.trim()
      if (email && !validateEmail(email)) {
        showFieldError(e.target, "Format email tidak valid")
      } else {
        clearFieldError(e.target)
      }
    })
  })

  passwordInputs.forEach((input) => {
    if (input.id === "reg_password") {
      input.addEventListener("blur", (e) => {
        const password = e.target.value
        if (password && password.length < 6) {
          showFieldError(e.target, "Password minimal 6 karakter")
        } else {
          clearFieldError(e.target)
        }
      })
    }
  })
})
