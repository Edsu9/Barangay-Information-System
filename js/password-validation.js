/**
 * Password validation and strength meter
 */

document.addEventListener("DOMContentLoaded", () => {
    // Find password fields
    const passwordFields = document.querySelectorAll('input[type="password"][data-validate="password"]')
  
    passwordFields.forEach((passwordField) => {
      // Create strength meter elements
      const strengthMeter = document.createElement("div")
      strengthMeter.className = "password-strength-meter"
  
      const strengthBar = document.createElement("div")
      strengthBar.className = "strength-bar"
  
      const strengthText = document.createElement("div")
      strengthText.className = "strength-text"
  
      // Add strength meter after password field
      strengthMeter.appendChild(strengthBar)
      strengthMeter.appendChild(strengthText)
  
      // Insert after password field
      passwordField.parentNode.insertBefore(strengthMeter, passwordField.nextSibling)
  
      // Create requirements list
      const requirementsList = document.createElement("ul")
      requirementsList.className = "password-requirements"
  
      const requirements = [
        { id: "length", text: "At least 8 characters", regex: /.{8,}/ },
        { id: "lowercase", text: "At least one lowercase letter", regex: /[a-z]/ },
        { id: "uppercase", text: "At least one uppercase letter", regex: /[A-Z]/ },
        { id: "number", text: "At least one number", regex: /[0-9]/ },
        { id: "special", text: "At least one special character", regex: /[^A-Za-z0-9]/ },
      ]
  
      requirements.forEach((requirement) => {
        const li = document.createElement("li")
        li.id = "req-" + requirement.id
        li.textContent = requirement.text
        requirementsList.appendChild(li)
      })
  
      // Insert requirements list after strength meter
      strengthMeter.parentNode.insertBefore(requirementsList, strengthMeter.nextSibling)
  
      // Add event listener to password field
      passwordField.addEventListener("input", function () {
        const password = this.value
        let strength = 0
        let meetsAllRequirements = true
  
        // Check each requirement
        requirements.forEach((requirement) => {
          const meets = requirement.regex.test(password)
          const reqElement = document.getElementById("req-" + requirement.id)
  
          if (meets) {
            reqElement.classList.add("met")
            reqElement.classList.remove("unmet")
            strength += 20 // Each requirement is worth 20% of total strength
          } else {
            reqElement.classList.add("unmet")
            reqElement.classList.remove("met")
            meetsAllRequirements = false
          }
        })
  
        // Update strength bar
        strengthBar.style.width = strength + "%"
  
        // Update strength text
        if (strength <= 20) {
          strengthText.textContent = "Very Weak"
          strengthBar.className = "strength-bar very-weak"
        } else if (strength <= 40) {
          strengthText.textContent = "Weak"
          strengthBar.className = "strength-bar weak"
        } else if (strength <= 60) {
          strengthText.textContent = "Medium"
          strengthBar.className = "strength-bar medium"
        } else if (strength <= 80) {
          strengthText.textContent = "Strong"
          strengthBar.className = "strength-bar strong"
        } else {
          strengthText.textContent = "Very Strong"
          strengthBar.className = "strength-bar very-strong"
        }
  
        // Update form validation
        if (meetsAllRequirements) {
          this.setCustomValidity("")
        } else {
          this.setCustomValidity("Password does not meet all requirements")
        }
  
        // If there's a confirm password field, check if they match
        const form = this.closest("form")
        if (form) {
          const confirmField = form.querySelector('input[type="password"][data-match="password"]')
          if (confirmField && confirmField.value) {
            if (confirmField.value !== this.value) {
              confirmField.setCustomValidity("Passwords do not match")
            } else {
              confirmField.setCustomValidity("")
            }
          }
        }
      })
  
      // Add event listener to confirm password field if it exists
      const form = passwordField.closest("form")
      if (form) {
        const confirmField = form.querySelector('input[type="password"][data-match="password"]')
        if (confirmField) {
          confirmField.addEventListener("input", function () {
            if (this.value !== passwordField.value) {
              this.setCustomValidity("Passwords do not match")
            } else {
              this.setCustomValidity("")
            }
          })
        }
      }
    })
  })
  