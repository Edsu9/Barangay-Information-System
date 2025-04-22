/**
 * Barangay Management System
 * Main JavaScript file
 */

document.addEventListener("DOMContentLoaded", () => {
    // Initialize all components
    initSidebar()
    initCards()
    initForms()
    initTables()
    initAlerts()
  })
  
  /**
   * Sidebar functionality
   */
  function initSidebar() {
    const menuItems = document.querySelectorAll(".menu-item")
    const toggleBtn = document.querySelector(".toggle-sidebar")
    const dashboardContainer = document.querySelector(".dashboard-container")
  
    // Active menu item
    menuItems.forEach((item) => {
      item.addEventListener("click", function () {
        menuItems.forEach((i) => i.classList.remove("active"))
        this.classList.add("active")
      })
    })
  
    // Toggle sidebar on mobile
    if (toggleBtn) {
      toggleBtn.addEventListener("click", function () {
        dashboardContainer.classList.toggle("sidebar-collapsed")
  
        // Change icon
        const icon = this.querySelector("i")
        if (dashboardContainer.classList.contains("sidebar-collapsed")) {
          icon.classList.remove("fa-bars")
          icon.classList.add("fa-expand")
        } else {
          icon.classList.remove("fa-expand")
          icon.classList.add("fa-bars")
        }
      })
    }
  
    // Auto-collapse sidebar on small screens
    function checkScreenSize() {
      if (window.innerWidth <= 768) {
        dashboardContainer.classList.add("sidebar-collapsed")
      }
    }
  
    // Check on load
    checkScreenSize()
  
    // Check on resize
    window.addEventListener("resize", checkScreenSize)
  }
  
  /**
   * Card animations and interactions
   */
  function initCards() {
    const cards = document.querySelectorAll(".card")
  
    cards.forEach((card) => {
      // Add hover effect
      card.addEventListener("mouseenter", function () {
        this.style.transform = "translateY(-10px)"
      })
  
      card.addEventListener("mouseleave", function () {
        this.style.transform = "translateY(0)"
      })
    })
  }
  
  /**
   * Form validations and enhancements
   */
  function initForms() {
    const forms = document.querySelectorAll("form")
  
    forms.forEach((form) => {
      // Add loading state on submit
      form.addEventListener("submit", function () {
        const submitBtn = this.querySelector('button[type="submit"]')
        if (submitBtn) {
          const originalText = submitBtn.innerHTML
          submitBtn.disabled = true
          submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...'
  
          // Store original text for restoration
          submitBtn.dataset.originalText = originalText
  
          // Add loading overlay
          const overlay = document.createElement("div")
          overlay.className = "loading-overlay"
          overlay.innerHTML = '<div class="spinner"></div>'
          document.body.appendChild(overlay)
        }
      })
    })
  
    // Enhanced form controls
    const formControls = document.querySelectorAll(".form-control")
    formControls.forEach((control) => {
      // Add focus effect
      control.addEventListener("focus", function () {
        this.parentElement.classList.add("focused")
      })
  
      control.addEventListener("blur", function () {
        this.parentElement.classList.remove("focused")
      })
    })
  }
  
  /**
   * Table enhancements
   */
  function initTables() {
    const tables = document.querySelectorAll(".data-table table")
  
    tables.forEach((table) => {
      const rows = table.querySelectorAll("tbody tr")
  
      // Add row hover effect
      rows.forEach((row, index) => {
        row.style.transition = `background-color 0.3s, transform 0.3s ${index * 0.05}s`
  
        row.addEventListener("mouseenter", function () {
          this.style.transform = "scale(1.01)"
          this.style.backgroundColor = "rgba(0, 0, 0, 0.02)"
        })
  
        row.addEventListener("mouseleave", function () {
          this.style.transform = "scale(1)"
          this.style.backgroundColor = ""
        })
      })
    })
  }
  
  /**
   * Alert dismissal
   */
  function initAlerts() {
    const alerts = document.querySelectorAll(".alert")
  
    alerts.forEach((alert) => {
      // Add close button if not present
      if (!alert.querySelector(".alert-close")) {
        const closeBtn = document.createElement("button")
        closeBtn.className = "alert-close"
        closeBtn.innerHTML = '<i class="fas fa-times"></i>'
        closeBtn.style.background = "none"
        closeBtn.style.border = "none"
        closeBtn.style.float = "right"
        closeBtn.style.cursor = "pointer"
        closeBtn.style.color = "inherit"
        closeBtn.style.fontSize = "1rem"
  
        closeBtn.addEventListener("click", () => {
          alert.style.opacity = "0"
          setTimeout(() => {
            alert.style.display = "none"
          }, 300)
        })
  
        alert.appendChild(closeBtn)
      }
    })
  }
  
  /**
   * Print functionality
   */
  function printContent(elementId) {
    const element = document.getElementById(elementId)
    if (!element) return
  
    const originalContent = document.body.innerHTML
    const printContent = element.innerHTML
  
    document.body.innerHTML = `
          <div class="print-container">
              ${printContent}
          </div>
      `
  
    window.print()
    document.body.innerHTML = originalContent
  
    // Reinitialize all components after restoring content
    initSidebar()
    initCards()
    initForms()
    initTables()
    initAlerts()
  }
  
  /**
   * Confirmation dialogs
   */
  function confirmAction(message, callback) {
    if (confirm(message)) {
      callback()
    }
  }
  
  /**
   * Format date to readable format
   */
  function formatDate(dateString) {
    const date = new Date(dateString)
    const options = { year: "numeric", month: "long", day: "numeric" }
    return date.toLocaleDateString(undefined, options)
  }
  
  /**
   * Format currency
   */
  function formatCurrency(amount) {
    return new Intl.NumberFormat("en-PH", {
      style: "currency",
      currency: "PHP",
    }).format(amount)
  }
  