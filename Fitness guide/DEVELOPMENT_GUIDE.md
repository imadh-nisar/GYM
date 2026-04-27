# GYMgeekS Frontend - Maintenance & Development Guide

## Table of Contents

1. [Project Structure](#project-structure)
2. [Form Implementation](#form-implementation)
3. [Styling Guidelines](#styling-guidelines)
4. [Responsive Design](#responsive-design)
5. [Common Tasks](#common-tasks)
6. [Troubleshooting](#troubleshooting)
7. [Performance Tips](#performance-tips)

---

## Project Structure

```
Fitness guide/
├── index.php                 # Landing page & appointment booking
├── login.php                 # Login handler
├── logout.php                # Logout handler
├── register.php              # Registration page
├── members.php               # Member dashboard
├── assets/
│   ├── css/
│   │   └── style.css         # Main stylesheet (comprehensive)
│   └── js/
│       ├── form-handler.js   # Form validation & submission
│       ├── main.js           # Main scripts
│       └── site.js           # Site-wide utilities (theme, animations)
├── admin/
│   ├── dashboard.php         # Admin home
│   ├── users.php             # User management
│   ├── workouts.php          # Workout management
│   └── meals.php             # Meal management
├── includes/
│   ├── auth.php              # Authentication helper
│   └── db.php                # Database connection
├── images/                   # Images folder
└── sql/
    └── schema.sql            # Database schema
```

---

## Form Implementation

### Adding a New Form

#### Step 1: Create HTML Form

```html
<form id="myForm" method="POST" class="needs-validation" novalidate>
  <div class="mb-3">
    <label for="username" class="form-label"
      >Username <span class="text-danger">*</span></label
    >
    <input
      type="text"
      class="form-control"
      id="username"
      name="username"
      required
    />
    <div class="invalid-feedback">Please enter username.</div>
  </div>

  <button type="submit" class="btn btn-primary">Submit</button>
</form>
```

#### Step 2: Include Form Handler Script

```html
<script src="assets/js/form-handler.js"></script>
<script>
  // Form handler is auto-initialized
</script>
```

#### Step 3: Add Server-Side Validation

```php
<?php
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');

  // Validate
  if (empty($username)) {
    $errors[] = 'Username is required';
  }

  // If no errors, process
  if (empty($errors)) {
    // Database operation
  }
}
?>
```

### Form Handler API

#### Initialize with Custom Handlers

```javascript
// Validate specific field
FormHandler.validateField("username", {
  required: true,
  minLength: 3,
});

// Validate all fields
FormHandler.validateAllFields("#myForm");

// Show error
FormHandler.showError("#myForm", "Error message");

// Show success
FormHandler.showSuccess("#myForm", "Success message");

// Reset form
FormHandler.resetForm("#myForm");

// Clear validation states
FormHandler.clearValidation("#myForm");
```

### Required Field Indicators

Always use:

```html
<label class="form-label">Field Name <span class="text-danger">*</span></label>
```

### Form Validation Classes

- `.needs-validation` - Form class for validation
- `.is-invalid` - Applied to invalid fields
- `.is-valid` - Applied to valid fields
- `.was-validated` - Applied to form after submit attempt
- `.invalid-feedback` - Error message container

---

## Styling Guidelines

### Color Variables

All colors are defined as CSS variables in `:root`:

```css
--primary: #007bff /* Main brand color */ --success: #28a745
  /* Success/positive actions */ --danger: #dc3545
  /* Danger/destructive actions */ --warning: #ffc107 /* Warnings */
  --info: #17a2b8 /* Information */;
```

### Using Variables in CSS

```css
.my-element {
  background: var(--primary);
  color: var(--text);
  border-radius: var(--border-radius);
  transition: all var(--transition);
}
```

### Button Styling

#### Primary Button

```html
<button class="btn btn-primary">Action</button>
```

#### Danger Button (Destructive)

```html
<button class="btn btn-danger">Delete</button>
```

#### Secondary Button

```html
<button class="btn btn-secondary">Cancel</button>
```

#### Button Sizes

```html
<button class="btn btn-primary btn-sm">Small</button>
<button class="btn btn-primary">Normal</button>
<button class="btn btn-primary btn-lg">Large</button>
```

### Card Styling

```html
<div class="card shadow">
  <div class="card-header bg-primary text-white">
    <h5 class="mb-0">Card Title</h5>
  </div>
  <div class="card-body">Content here</div>
</div>
```

### Typography

#### Headings

- `<h1>` - Page title (auto-scaled with clamp())
- `<h2>` - Section headers
- `<h3>` - Subsections
- Use `.lead` class for emphasis

#### Text Utilities

- `.text-muted` - Gray text for secondary info
- `.text-danger` - Red text for errors
- `.text-success` - Green text for success
- `.fw-bold` - Bold text
- `.small` - Smaller text

---

## Responsive Design

### Breakpoints Used

- `576px` - Small (mobile)
- `768px` - Medium (tablet)
- `992px` - Large (desktop)
- `1200px` - Extra large

### Mobile-First Example

```css
/* Mobile (base) */
.card {
  margin-bottom: 1rem;
}

/* Tablet and up */
@media (min-width: 768px) {
  .card {
    margin-bottom: 2rem;
  }
}
```

### Responsive Classes from Bootstrap

```html
<!-- Column responsive sizing -->
<div class="col-12 col-md-6 col-lg-4">
  Takes full width on mobile, half on tablet, third on desktop
</div>

<!-- Display utilities -->
<div class="d-none d-md-block">Only visible on tablet and up</div>
```

### Testing Responsiveness

1. Use Chrome DevTools (F12)
2. Toggle device toolbar (Ctrl+Shift+M)
3. Test at: 320px, 480px, 768px, 1024px, 1920px
4. Test on actual devices if possible

---

## Common Tasks

### Task 1: Add a New Admin Page

1. Create file: `admin/new-page.php`
2. Add auth check at top:

```php
<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: ../index.php");
  exit();
}
include("../includes/db.php");
?>
```

3. Use consistent navbar:

```html
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <!-- Copy from admin/users.php -->
</nav>
```

4. Import necessary scripts:

```html
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/form-handler.js"></script>
<script src="../assets/js/site.js"></script>
```

### Task 2: Add a New Input Field

1. Use proper structure:

```html
<div class="mb-3">
  <label for="fieldId" class="form-label">
    Field Label <span class="text-danger">*</span>
  </label>
  <input
    type="text"
    class="form-control"
    id="fieldId"
    name="fieldName"
    required
  />
  <div class="invalid-feedback">Error message here</div>
</div>
```

2. Add server-side validation:

```php
if (empty($fieldValue)) {
  $errors[] = 'Field is required';
}
```

### Task 3: Create a Modal for Confirmation

```html
<div class="modal fade" id="myModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Modal Title</h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"
          ></button>
        </div>
        <div class="modal-body">Content here</div>
        <div class="modal-footer bg-light">
          <button
            type="button"
            class="btn btn-secondary"
            data-bs-dismiss="modal"
          >
            Cancel
          </button>
          <button type="submit" class="btn btn-primary">Action</button>
        </div>
      </form>
    </div>
  </div>
</div>
```

### Task 4: Add Table Responsiveness

```html
<div class="table-responsive">
  <table class="table table-hover">
    <!-- Table content -->
  </table>
</div>
```

---

## Troubleshooting

### Issue 1: Forms Not Validating

**Problem:** Form validation not showing error states

**Solution:**

1. Ensure `class="needs-validation"` is on form
2. Check that `form-handler.js` is loaded
3. Verify input fields have `name` attributes
4. Check browser console for errors

### Issue 2: Styling Not Applied

**Problem:** CSS changes not showing

**Solution:**

1. Clear browser cache (Ctrl+Shift+Delete)
2. Do hard refresh (Ctrl+Shift+R or Cmd+Shift+R)
3. Check that `style.css` is properly linked
4. Verify CSS syntax is correct

### Issue 3: Modal Not Opening

**Problem:** Modal button clicks don't open modal

**Solution:**

1. Check modal has unique ID
2. Verify button has `data-bs-toggle="modal"` and `data-bs-target="#modalId"`
3. Ensure Bootstrap JS is loaded
4. Check for JavaScript errors in console

### Issue 4: Responsive Issues on Mobile

**Problem:** Layout broken on small screens

**Solution:**

1. Use Chrome DevTools to test different sizes
2. Check for horizontal scrolling
3. Verify responsive classes are used (col-12, col-md-6, etc.)
4. Test at actual mobile sizes (320px, 480px)

---

## Performance Tips

### 1. Optimize Images

- Compress images before uploading
- Use appropriate formats (WebP for modern browsers)
- Lazy load images when possible

### 2. Minimize CSS/JS

- Use minified Bootstrap from CDN
- Remove unused CSS rules
- Combine multiple small JS files

### 3. Efficient DOM Manipulation

```javascript
// Bad: Multiple DOM queries
const btn = document.querySelector(".btn");
btn.addEventListener("click", function () {
  document.querySelector(".modal").style.display = "block";
  document.querySelector(".message").textContent = "Done";
});

// Good: Query once, cache reference
const btn = document.querySelector(".btn");
const modal = document.querySelector(".modal");
const message = document.querySelector(".message");

btn.addEventListener("click", function () {
  modal.style.display = "block";
  message.textContent = "Done";
});
```

### 4. Event Delegation

```javascript
// Bad: Add listener to each button
document.querySelectorAll(".delete-btn").forEach((btn) => {
  btn.addEventListener("click", handleDelete);
});

// Good: Delegate from parent (if using dynamic elements)
document.addEventListener("click", (e) => {
  if (e.target.classList.contains("delete-btn")) {
    handleDelete(e);
  }
});
```

### 5. Debounce Scroll Events

Already implemented in `site.js` with scroll reveal animations.

---

## Code Standards

### Naming Conventions

- **CSS Classes:** kebab-case (`.delete-user-btn`)
- **JavaScript Variables:** camelCase (`deleteUserBtn`)
- **HTML IDs:** camelCase (`appointmentForm`)
- **Database Columns:** snake_case (`created_at`)

### Comments

```javascript
// Single line comment for clarity
// Use comments to explain WHY, not WHAT

/**
 * Multi-line comment for complex logic
 * @param {string} id - The user ID
 * @returns {boolean} - Success status
 */
```

### Indentation

- Use 2 spaces for HTML/PHP
- Use 2 spaces for CSS
- Use 2 spaces for JavaScript

---

## Security Checklist

Before deploying:

- [ ] Validate all user inputs (server-side)
- [ ] Sanitize all output with `htmlspecialchars()`
- [ ] Use prepared statements for SQL
- [ ] Hash passwords with `password_hash()`
- [ ] Implement CSRF tokens (future enhancement)
- [ ] Use HTTPS in production
- [ ] Test SQL injection prevention
- [ ] Check for XSS vulnerabilities
- [ ] Verify authentication checks on protected pages

---

## Useful Resources

### Bootstrap Documentation

- https://getbootstrap.com/docs/5.3/

### Bootstrap Icons

- https://icons.getbootstrap.com/

### CSS Variables

- https://developer.mozilla.org/en-US/docs/Web/CSS/--*

### Form Validation

- https://developer.mozilla.org/en-US/docs/Learn/Forms/Form_validation

### Responsive Design

- https://developer.mozilla.org/en-US/docs/Learn/CSS/CSS_layout/Responsive_Design

---

## Contact & Support

For questions or issues:

1. Check browser console for errors (F12)
2. Review this guide
3. Check CHANGES_SUMMARY.md
4. Test in multiple browsers
5. Clear cache and reload

---

**Last Updated:** April 2026
**Version:** 2.0
