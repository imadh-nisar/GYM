<?php
session_start();
include("includes/db.php");

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    // If no errors, insert user with pending status
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users 
            (username, email, password, status, weight, height, chest, waist, arms, legs, bmi, created_at) 
            VALUES (?, ?, ?, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NOW())");

        if ($stmt) {
            $stmt->bind_param("sss", $username, $email, $hashedPassword);

            if ($stmt->execute()) {
                // Don't auto-login - show pending message instead
                $success = true;
            } else {
                if (strpos($stmt->error, 'Duplicate') !== false) {
                    $errors[] = 'Username or email already exists';
                } else {
                    $errors[] = 'Error creating account: ' . $stmt->error;
                }
            }
            $stmt->close();
        } else {
            $errors[] = 'Database error: ' . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - GYMgeekS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="min-vh-100 d-flex align-items-center bg-light py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-lg-6" data-reveal>
                    <div class="card shadow-lg">
                        <div class="card-header bg-primary text-white py-4">
                            <h2 class="mb-0 text-center">
                                <i class="bi bi-person-plus-fill me-2"></i>Create GYMgeekS Account
                            </h2>
                        </div>

                        <div class="card-body p-4">
                            <!-- Error Messages -->
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong><i class="bi bi-exclamation-circle me-2"></i>Registration Error</strong>
                                    <ul class="mb-0 mt-2">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?= htmlspecialchars($error); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <!-- Success Message -->
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <h4 class="alert-heading"><i class="bi bi-check-circle-fill me-2"></i>Registration
                                        Successful!</h4>
                                    <p class="mb-0">Your account has been created and is <strong>pending approval</strong>.
                                    </p>
                                    <hr>
                                    <p class="mb-0"><i class="bi bi-info-circle me-1"></i>An administrator will review your
                                        request. You will be able to login once your account is approved.</p>
                                    <hr>
                                    <p class="mb-0">Redirecting to <a href="index.php">home page</a> in <span
                                            id="countdown">5</span> seconds...</p>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <script>
                                    let seconds = 5;
                                    const countdown = document.getElementById('countdown');
                                    const interval = setInterval(() => {
                                        seconds--;
                                        countdown.textContent = seconds;
                                        if (seconds <= 0) {
                                            clearInterval(interval);
                                            window.location.href = 'index.php';
                                        }
                                    }, 1000);
                                </script>
                            <?php else: ?>
                                <!-- Registration Form -->
                                <form id="registerForm" method="POST" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="username" class="form-label">
                                            <i class="bi bi-person-fill me-1"></i>Username
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" id="username" name="username"
                                            placeholder="Choose your username (min 3 chars)" minlength="3" maxlength="50"
                                            required>
                                        <div class="invalid-feedback">Please choose a valid username (3+ characters).</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">
                                            <i class="bi bi-envelope-fill me-1"></i>Email Address
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="your@email.com" required>
                                        <div class="invalid-feedback">Please enter a valid email address.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">
                                            <i class="bi bi-lock-fill me-1"></i>Password
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Min 6 characters" minlength="6" maxlength="100" required>
                                        <div class="invalid-feedback">Password must be at least 6 characters.</div>
                                        <small class="form-text text-muted">Use a mix of letters, numbers, and symbols for
                                            security.</small>
                                    </div>

                                    <div class="mb-4">
                                        <label for="confirm_password" class="form-label">
                                            <i class="bi bi-lock-fill me-1"></i>Confirm Password
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input type="password" class="form-control" id="confirm_password"
                                            name="confirm_password" placeholder="Re-enter password" minlength="6"
                                            maxlength="100" required>
                                        <div class="invalid-feedback">Passwords must match.</div>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                        <i class="bi bi-check-circle me-2"></i>Create Account
                                    </button>
                                </form>

                                <hr class="my-4">
                                <p class="text-center text-muted mb-0">
                                    Already have an account?
                                    <a href="index.php" class="fw-bold">Login here</a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Features Info -->
                    <div class="mt-4 text-center text-muted small">
                        <p>By registering, you'll have access to:</p>
                        <div class="d-flex justify-content-around flex-wrap gap-2">
                            <span><i class="bi bi-check-circle-fill text-success me-1"></i>Personalized Workouts</span>
                            <span><i class="bi bi-check-circle-fill text-success me-1"></i>Nutrition Plans</span>
                            <span><i class="bi bi-check-circle-fill text-success me-1"></i>Progress Tracking</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/form-handler.js"></script>
    <script src="assets/js/site.js"></script>
    <script>
        // Additional password confirmation validation
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm_password');

        if (confirmInput && passwordInput) {
            confirmInput.addEventListener('input', () => {
                if (confirmInput.value !== passwordInput.value) {
                    confirmInput.classList.add('is-invalid');
                    confirmInput.classList.remove('is-valid');
                } else if (confirmInput.value !== '') {
                    confirmInput.classList.add('is-valid');
                    confirmInput.classList.remove('is-invalid');
                }
            });
        }
    </script>
</body>

</html>