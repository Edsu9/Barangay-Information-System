/**
 * Form Validation and Password Strength Utilities
 * Barangay Management System
 */

// Password strength checker
const passwordStrength = {
    // Check password strength and return score (0-4)
    check: (password) => {
      if (!password) return 0
  
      let score = 0
  
      // Length check
      if (password.length >= 8) score++
      if (password.length >= 12) score++
  
      // Complexity checks
      if (/[A-Z]/.test(password)) score++ // Has uppercase
      if (/[a-z]/.test(password)) score++ // Has lowercase
      if (/[0-9]/.test(password)) score++ // Has number
      if (/[^A-Za-z0-9]/.test(password)) score++ // Has special char
  
      return Math.min(4, score)
    },
  
    // Get strength label based on score
    getLabel: (score) => {
      const labels = ["Very Weak", "Weak", "Medium", "Strong", "Very Strong"]
      return labels[score]
    },
  
    // Get color based on score
    getColor: (score) => {
      const colors = ["#ff4d4d", "#ffaa00", "#ffdd00", "#00cc00", "#00aa00"]
      return colors[score]
    },
  
    // Update password strength meter
    updateStrengthMeter: function (password, meterElement, labelElement) {
      const score = this.check(password)
      const label = this.getLabel(score)
      const color = this.getColor(score)
  
      if (meterElement) {
        meterElement.style.width = `${(score + 1) * 20}%`
        meterElement.style.backgroundColor = color
      }
  
      if (labelElement) {
        labelElement.textContent = label
        labelElement.style.color = color
      }
  
      return score
    },
  }
  
  // Form validation functions
  const formValidation = {
    // Validate email format
    isValidEmail: (email) => {
      const re =
        /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
      return re.test(String(email).toLowerCase())
    },
  
    // Validate phone number format (Philippines)
    isValidPhoneNumber: (phone) => {
      // Allow formats: +639XXXXXXXXX, 09XXXXXXXXX, 9XXXXXXXXX
      const re = /^(\+?63|0)?9\d{9}$/
      return re.test(String(phone).replace(/\s/g, ""))
    },
  
    // Show validation error
    showError: (inputElement, message) => {
      const formGroup = inputElement.closest(".form-group")
      const errorElement = formGroup.querySelector(".validation-error") || document.createElement("div")
  
      errorElement.className = "validation-error"
      errorElement.textContent = message
  
      if (!formGroup.querySelector(".validation-error")) {
        formGroup.appendChild(errorElement)
      }
  
      inputElement.classList.add("is-invalid")
    },
  
    // Clear validation error
    clearError: (inputElement) => {
      const formGroup = inputElement.closest(".form-group")
      const errorElement = formGroup.querySelector(".validation-error")
  
      if (errorElement) {
        errorElement.remove()
      }
  
      inputElement.classList.remove("is-invalid")
    },
  
    // Validate form
    validateForm: function (formElement, rules) {
      let isValid = true
  
      for (const fieldName in rules) {
        const inputElement = formElement.querySelector(`[name="${fieldName}"]`)
        if (!inputElement) continue
  
        this.clearError(inputElement)
  
        const fieldRules = rules[fieldName]
  
        // Required check
        if (fieldRules.required && !inputElement.value.trim()) {
          this.showError(inputElement, fieldRules.required === true ? "This field is required" : fieldRules.required)
          isValid = false
          continue
        }
  
        // Skip other validations if field is empty and not required
        if (!inputElement.value.trim() && !fieldRules.required) continue
  
        // Email check
        if (fieldRules.email && !this.isValidEmail(inputElement.value)) {
          this.showError(
            inputElement,
            fieldRules.email === true ? "Please enter a valid email address" : fieldRules.email,
          )
          isValid = false
        }
  
        // Phone check
        if (fieldRules.phone && !this.isValidPhoneNumber(inputElement.value)) {
          this.showError(inputElement, fieldRules.phone === true ? "Please enter a valid phone number" : fieldRules.phone)
          isValid = false
        }
  
        // Min length check
        if (fieldRules.minLength && inputElement.value.length < fieldRules.minLength.value) {
          this.showError(
            inputElement,
            fieldRules.minLength.message || `Minimum length is ${fieldRules.minLength.value} characters`,
          )
          isValid = false
        }
  
        // Max length check
        if (fieldRules.maxLength && inputElement.value.length > fieldRules.maxLength.value) {
          this.showError(
            inputElement,
            fieldRules.maxLength.message || `Maximum length is ${fieldRules.maxLength.value} characters`,
          )
          isValid = false
        }
  
        // Pattern check
        if (fieldRules.pattern && !fieldRules.pattern.value.test(inputElement.value)) {
          this.showError(inputElement, fieldRules.pattern.message || "Invalid format")
          isValid = false
        }
  
        // Custom validation
        if (fieldRules.custom && typeof fieldRules.custom.validate === "function") {
          const customResult = fieldRules.custom.validate(inputElement.value, formElement)
          if (!customResult) {
            this.showError(inputElement, fieldRules.custom.message || "Invalid value")
            isValid = false
          }
        }
      }
  
      return isValid
    },
  
    // Initialize form validation
    init: (formSelector, rules, options = {}) => {
      const form = document.querySelector(formSelector)
      if (!form) return
  
      // Add validation styles
      const style = document.createElement("style")
      style.textContent = `
        .validation-error {
          color: #dc3545;
          font-size: 0.85rem;
          margin-top: 0.25rem;
        }
        .is-invalid {
          border-color: #dc3545 !important;
          background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
          background-repeat: no-repeat;
          background-position: right calc(0.375em + 0.1875rem) center;
          background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
      `
      document.head.appendChild(style)
  
      // Add submit event listener
      form.addEventListener("submit", (e) => {
        const isValid = formValidation.validateForm(form, rules)
  
        if (!isValid) {
          e.preventDefault()
  
          // Scroll to first error
          const firstError = form.querySelector(".is-invalid")
          if (firstError) {
            firstError.focus()
            firstError.scrollIntoView({ behavior: "smooth", block: "center" })
          }
  
          // Call onInvalid callback if provided
          if (options.onInvalid && typeof options.onInvalid === "function") {
            options.onInvalid(form)
          }
        } else if (options.onValid && typeof options.onValid === "function") {
          // Call onValid callback if provided
          options.onValid(form)
        }
      })
  
      // Add real-time validation if enabled
      if (options.realtime) {
        for (const fieldName in rules) {
          const inputElement = form.querySelector(`[name="${fieldName}"]`)
          if (!inputElement) continue
  
          inputElement.addEventListener("blur", () => {
            formValidation.clearError(inputElement)
  
            const fieldRules = rules[fieldName]
            const singleFieldRules = { [fieldName]: fieldRules }
            formValidation.validateForm(form, singleFieldRules)
          })
  
          // Clear error on input if specified
          if (options.clearOnInput) {
            inputElement.addEventListener("input", () => {
              formValidation.clearError(inputElement)
            })
          }
        }
      }
    },
  }
  
  // Initialize on document load
  document.addEventListener("DOMContentLoaded", () => {
    // Initialize password strength meters
    const passwordInputs = document.querySelectorAll('input[type="password"][data-password-strength]')
    passwordInputs.forEach((input) => {
      const containerId = input.getAttribute("data-password-strength")
      const container = document.getElementById(containerId)
  
      if (container) {
        // Create strength meter elements
        const meterContainer = document.createElement("div")
        meterContainer.className = "password-strength-meter"
        meterContainer.innerHTML = `
          <div class="strength-meter-bar">
            <div class="strength-meter-fill"></div>
          </div>
          <div class="strength-meter-label">Password strength</div>
        `
  
        container.appendChild(meterContainer)
  
        const meterFill = meterContainer.querySelector(".strength-meter-fill")
        const meterLabel = meterContainer.querySelector(".strength-meter-label")
  
        // Update on input
        input.addEventListener("input", function () {
          passwordStrength.updateStrengthMeter(this.value, meterFill, meterLabel)
        })
      }
    })
  })
  