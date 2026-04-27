/**
 * Form Validation & Handling Module
 * Provides reusable form validation, submission handling, and feedback
 */

const FormHandler = (() => {
  'use strict';

  // Initialize Bootstrap form validation on all forms with .needs-validation class
  function initBootstrapValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }

  // Add real-time validation feedback
  function addRealtimeValidation() {
    const inputs = document.querySelectorAll('.form-control[required], .form-select[required]');
    
    inputs.forEach(input => {
      input.addEventListener('blur', () => {
        if (input.value.trim() === '') {
          input.classList.add('is-invalid');
          input.classList.remove('is-valid');
        } else if (input.type === 'email') {
          const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
          input.classList.toggle('is-invalid', !isValid);
          input.classList.toggle('is-valid', isValid);
        } else {
          input.classList.add('is-valid');
          input.classList.remove('is-invalid');
        }
      });

      input.addEventListener('input', () => {
        if (input.classList.contains('was-validated')) {
          if (input.value.trim() === '') {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
          } else if (input.type === 'email') {
            const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
            input.classList.toggle('is-invalid', !isValid);
            input.classList.toggle('is-valid', isValid);
          } else {
            input.classList.add('is-valid');
            input.classList.remove('is-invalid');
          }
        }
      });
    });
  }

  // Handle form submission with loading state
  function handleFormSubmit(formSelector, callback) {
    const form = document.querySelector(formSelector);
    if (!form) return;

    form.addEventListener('submit', function (e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        form.classList.add('was-validated');
        return;
      }

      // Show loading state
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
        
        // Reset after 3 seconds if something goes wrong
        setTimeout(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        }, 3000);
      }

      if (callback) {
        callback(e);
      }
    });
  }

  // Validate specific fields
  function validateField(fieldName, rules) {
    const field = document.querySelector(`[name="${fieldName}"]`);
    if (!field) return true;

    const value = field.value.trim();
    
    if (rules.required && value === '') {
      field.classList.add('is-invalid');
      return false;
    }

    if (rules.email && value !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
      field.classList.add('is-invalid');
      return false;
    }

    if (rules.minLength && value.length < rules.minLength) {
      field.classList.add('is-invalid');
      return false;
    }

    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
    return true;
  }

  // Clear form validation states
  function clearValidation(formSelector) {
    const form = document.querySelector(formSelector);
    if (!form) return;

    form.classList.remove('was-validated');
    const inputs = form.querySelectorAll('.form-control, .form-select');
    inputs.forEach(input => {
      input.classList.remove('is-invalid', 'is-valid');
    });
  }

  // Reset form to initial state
  function resetForm(formSelector) {
    const form = document.querySelector(formSelector);
    if (!form) return;

    form.reset();
    clearValidation(formSelector);
  }

  // Show form error message
  function showError(formSelector, message) {
    const form = document.querySelector(formSelector);
    if (!form) return;

    // Remove existing error if any
    const existingError = form.querySelector('.form-error-message');
    if (existingError) existingError.remove();

    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger form-error-message mt-3';
    errorDiv.setAttribute('role', 'alert');
    errorDiv.textContent = message;
    form.appendChild(errorDiv);

    // Auto-remove after 5 seconds
    setTimeout(() => {
      errorDiv.remove();
    }, 5000);
  }

  // Show form success message
  function showSuccess(formSelector, message) {
    const form = document.querySelector(formSelector);
    if (!form) return;

    // Remove existing message if any
    const existingMessage = form.querySelector('.form-success-message');
    if (existingMessage) existingMessage.remove();

    const successDiv = document.createElement('div');
    successDiv.className = 'alert alert-success form-success-message mt-3';
    successDiv.setAttribute('role', 'alert');
    successDiv.textContent = message;
    form.appendChild(successDiv);

    // Auto-remove after 3 seconds
    setTimeout(() => {
      successDiv.remove();
    }, 3000);
  }

  // Public API
  return {
    init: function () {
      document.addEventListener('DOMContentLoaded', () => {
        initBootstrapValidation();
        addRealtimeValidation();
      });
    },
    validateField,
    validateAllFields: function (formSelector) {
      const form = document.querySelector(formSelector);
      if (!form) return false;

      const inputs = form.querySelectorAll('[required]');
      let isValid = true;

      inputs.forEach(input => {
        if (input.value.trim() === '') {
          input.classList.add('is-invalid');
          isValid = false;
        }
      });

      return isValid;
    },
    clearValidation,
    resetForm,
    showError,
    showSuccess,
    handleFormSubmit
  };
})();

// Auto-initialize on page load
FormHandler.init();
