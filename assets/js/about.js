// About page JavaScript

document.addEventListener("DOMContentLoaded", () => {
  initializeScrollAnimations()
  initializeTeamInteractions()
})

function initializeScrollAnimations() {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("animate-in")
      }
    })
  }, observerOptions)

  // Observe all animated elements
  const animatedElements = document.querySelectorAll(".team-card, .value-card, .vm-card")
  animatedElements.forEach((el) => {
    el.style.opacity = "0"
    el.style.transform = "translateY(30px)"
    el.style.transition = "opacity 0.6s ease, transform 0.6s ease"
    observer.observe(el)
  })
}

function initializeTeamInteractions() {
  const teamCards = document.querySelectorAll(".team-card")

  teamCards.forEach((card) => {
    card.addEventListener("mouseenter", () => {
      // Add subtle animation to other cards
      teamCards.forEach((otherCard) => {
        if (otherCard !== card) {
          otherCard.style.transform = "scale(0.95)"
          otherCard.style.opacity = "0.7"
        }
      })
    })

    card.addEventListener("mouseleave", () => {
      // Reset all cards
      teamCards.forEach((otherCard) => {
        otherCard.style.transform = ""
        otherCard.style.opacity = ""
      })
    })
  })
}

// Add CSS class for scroll animations
const style = document.createElement("style")
style.textContent = `
    .animate-in {
        opacity: 1 !important;
        transform: translateY(0) !important;
    }
`
document.head.appendChild(style)

// Smooth scroll for internal links
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

// Add parallax effect to hero section
window.addEventListener("scroll", () => {
  const scrolled = window.pageYOffset
  const hero = document.querySelector(".about-hero")
  if (hero) {
    const rate = scrolled * -0.5
    hero.style.transform = `translateY(${rate}px)`
  }
})
