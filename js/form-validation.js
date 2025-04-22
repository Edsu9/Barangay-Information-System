/**
 * Form Validation and Password Strength Checker
 * Barangay Management System
 */

document.addEventListener("DOMContentLoaded", () => {
    // Initialize password strength meter if it exists
    initPasswordStrengthMeter()
  
    // Initialize form validation
    initFormValidation()
  
    // Initialize token expiration notification
    initTokenExpirationCheck()
  })
  
  /**
   * Password Strength Meter
   */
  function initPasswordStrengthMeter() {
    const passwordInputs = document.querySelectorAll('input[type="password"][data-password-strength]')
  
    passwordInputs.forEach((input) => {
      // Create strength meter elements
      const meterContainer = document.createElement("div")
      meterContainer.className = "password-strength-meter"
  
      const meter = document.createElement("div")
      meter.className = "strength-meter"
  
      const strengthText = document.createElement("div")
      strengthText.className = "strength-text"
  
      // Insert after password input
      input.parentNode.insertBefore(meterContainer, input.nextSibling)
      meterContainer.appendChild(meter)
      meterContainer.appendChild(strengthText)
  
      // Add event listener to password input
      input.addEventListener("input", function () {
        updatePasswordStrength(this.value, meter, strengthText)
      })
    })
  }
  
  /**
   * Update password strength meter
   */
  function updatePasswordStrength(password, meterElement, textElement) {
    // Password strength criteria
    const lengthValid = password.length >= 8
    const hasUppercase = /[A-Z]/.test(password)
    const hasLowercase = /[a-z]/.test(password)
    const hasNumbers = /[0-9]/.test(password)
    const hasSpecialChars = /[^A-Za-z0-9]/.test(password)
  
    // Calculate strength score (0-4)
    let score = 0
    if (lengthValid) score++
    if (hasUppercase) score++
    if (hasLowercase) score++
    if (hasNumbers) score++
    if (hasSpecialChars) score++
  
    // Update meter width and color
    const percentage = (score / 5) * 100
    meterElement.style.width = percentage + "%"
  
    // Update color based on score
    if (score < 2) {
      meterElement.className = "strength-meter weak"
      textElement.textContent = "Weak"
      textElement.className = "strength-text text-danger"
    } else if (score < 4) {
      meterElement.className = "strength-meter medium"
      textElement.textContent = "Medium"
      textElement.className = "strength-text text-warning"
    } else {
      meterElement.className = "strength-meter strong"
      textElement.textContent = "Strong"
      textElement.className = "strength-text text-success"
    }
  
    // Add requirements text
    const requirementsList = []
    if (!lengthValid) requirementsList.push("at least 8 characters")
    if (!hasUppercase) requirementsList.push("uppercase letter")
    if (!hasLowercase) requirementsList.push("lowercase letter")
    if (!hasNumbers) requirementsList.push("number")
    if (!hasSpecialChars) requirementsList.push("special character")
  
    if (requirementsList.length > 0) {
      const requirementsText = document.createElement("small")
      requirementsText.className = "password-requirements"
      requirementsText.innerHTML = "Password must contain: " + requirementsList.join(", ")
  
      // Remove any existing requirements text
      const existingRequirements = textElement.parentNode.querySelector(".password-requirements")
      if (existingRequirements) {
        existingRequirements.remove()
      }
  
      // Add new requirements text
      textElement.parentNode.appendChild(requirementsText)
    } else {
      // Remove requirements text if password meets all criteria
      const existingRequirements = textElement.parentNode.querySelector(".password-requirements")
      if (existingRequirements) {
        existingRequirements.remove()
      }
    }
  
    return score >= 3 // Return true if password is at least medium strength
  }
  
  /**
   * Form Validation
   */
  function initFormValidation() {
    const forms = document.querySelectorAll("form[data-validate]")
  
    forms.forEach((form) => {
      form.addEventListener("submit", (event) => {
        let isValid = true
  
        // Validate required fields
        const requiredFields = form.querySelectorAll("[required]")
        requiredFields.forEach((field) => {
          if (!field.value.trim()) {
            isValid = false
            showFieldError(field, "This field is required")
          } else {
            clearFieldError(field)
          }
        })
  
        // Validate email fields
        const emailFields = form.querySelectorAll('input[type="email"]')
        emailFields.forEach((field) => {
          if (field.value.trim() && !isValidEmail(field.value)) {
            isValid = false
            showFieldError(field, "Please enter a valid email address")
          }
        })
  
        // Validate password fields
        const passwordFields = form.querySelectorAll('input[type="password"][data-password-strength]')
        passwordFields.forEach((field) => {
          if (field.value.trim() && !isStrongPassword(field.value)) {
            isValid = false
            showFieldError(field, "Password does not meet strength requirements")
          }
        })
  
        // Validate password confirmation
        const passwordField = form.querySelector('input[name="password"]')
        const confirmField = form.querySelector('input[name="confirm_password"]')
        if (passwordField && confirmField && passwordField.value !== confirmField.value) {
          isValid = false
          showFieldError(confirmField, "Passwords do not match")
        }
  
        if (!isValid) {
          event.preventDefault()
        }
      })
    })
  }
  
  /**
   * Check if email is valid
   */
  function isValidEmail(email) {
    const re =
      /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
    return re.test(String(email).toLowerCase())
  }
  
  /**
   * Check if password is strong enough
   */
  function isStrongPassword(password) {
    const lengthValid = password.length >= 8
    const hasUppercase = /[A-Z]/.test(password)
    const hasLowercase = /[a-z]/.test(password)
    const hasNumbers = /[0-9]/.test(password)
    const hasSpecialChars = /[^A-Za-z0-9]/.test(password)
  
    // Password must meet at least 3 criteria
    let score = 0
    if (lengthValid) score++
    if (hasUppercase) score++
    if (hasLowercase) score++
    if (hasNumbers) score++
    if (hasSpecialChars) score++
  
    return score >= 3
  }
  
  /**
   * Show field error message
   */
  function showFieldError(field, message) {
    // Clear any existing error
    clearFieldError(field)
  
    // Add error class to field
    field.classList.add("is-invalid")
  
    // Create error message element
    const errorElement = document.createElement("div")
    errorElement.className = "invalid-feedback"
    errorElement.textContent = message
  
    // Insert error message after field
    field.parentNode.appendChild(errorElement)
  }
  
  /**
   * Clear field error message
   */
  function clearFieldError(field) {
    field.classList.remove("is-invalid")
  
    // Remove any existing error messages
    const existingError = field.parentNode.querySelector(".invalid-feedback")
    if (existingError) {
      existingError.remove()
    }
  }
  
  /**
   * Token Expiration Notification
   */
  function initTokenExpirationCheck() {
    const tokenElement = document.querySelector("[data-token-expires]")
    if (!tokenElement) return
  
    const expiryTime = Number.parseInt(tokenElement.getAttribute("data-token-expires"))
    if (!expiryTime) return
  
    const currentTime = Math.floor(Date.now() / 1000)
    const timeRemaining = expiryTime - currentTime
  
    if (timeRemaining <= 0) {
      showTokenExpiredNotification()
    } else if (timeRemaining < 300) {
      // Less than 5 minutes
      showTokenExpiringNotification(timeRemaining)
    }
  }
  
  /**
   * Show token expired notification
   */
  function showTokenExpiredNotification() {
    const notification = document.createElement("div")
    notification.className = "alert alert-danger token-notification"
    notification.innerHTML = `
          <i class="fas fa-exclamation-circle"></i>
          <div>
              <strong>Link Expired</strong>
              <p>This password reset link has expired. Please request a new one.</p>
              <a href="forgot_password.php" class="btn btn-sm btn-danger">Request New Link</a>
          </div>
      `
  
    document.body.appendChild(notification)
  }
  
  /**
   * Show token expiring soon notification
   */
  function showTokenExpiringNotification(timeRemaining) {
    const minutes = Math.floor(timeRemaining / 60)
    const seconds = timeRemaining % 60
  
    const notification = document.createElement("div")
    notification.className = "alert alert-warning token-notification"
    notification.innerHTML = `
          <i class="fas fa-clock"></i>
          <div>
              <strong>Link Expiring Soon</strong>
              <p>This password reset link will expire in ${minutes}m ${seconds}s. Please complete your password reset.</p>
          </div>
      `
  
    document.body.appendChild(notification)
  
    // Update countdown
    const countdownInterval = setInterval(() => {
      const currentTime = Math.floor(Date.now() / 1000)
      const expiryTime = Number.parseInt(
        document.querySelector("[data-token-expires]").getAttribute("data-token-expires"),
      )
      const newTimeRemaining = expiryTime - currentTime
  
      if (newTimeRemaining <= 0) {
        clearInterval(countdownInterval)
        notification.remove()
        showTokenExpiredNotification()
      } else {
        const newMinutes = Math.floor(newTimeRemaining / 60)
        const newSeconds = newTimeRemaining % 60
        notification.querySelector("p").textContent =
          `This password reset link will expire in ${newMinutes}m ${newSeconds}s. Please complete your password reset.`
      }
    }, 1000)
  }
  