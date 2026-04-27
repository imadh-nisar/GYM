<?php
session_start();
include("includes/db.php");

// If user not logged in → show login/register
if (!isset($_SESSION['user_id'])) {
  ?>
  <!DOCTYPE html>
  <html>

  <head>
    <title>GYMgeekS Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
  </head>

  <body>
    <div class="container py-5">
      <h1 class="text-center text-primary mb-4">🏋️ GYMgeekS Portal</h1>
      <div class="card p-4 shadow mx-auto" style="max-width:400px;">
        <form action="login.php" method="POST">
          <input type="text" name="username" class="form-control mb-3" placeholder="Username or Email" required>
          <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
          <button class="btn btn-success w-100">Login</button>
        </form>
        <hr>
        <p class="text-center">Don’t have a user account?</p>
        <a href="register.php" class="btn btn-primary w-100">Register</a>
      </div>
    </div>
  </body>

  </html>
  <?php
  exit();
}

// Logged in → fetch user data
$user_id = $_SESSION['user_id'];

// Check if new user (from registration)
$isNewUser = isset($_SESSION['new_user']) || isset($_GET['new_user']);

// Clear the new user flag after checking
if (isset($_SESSION['new_user'])) {
  unset($_SESSION['new_user']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $weight = $_POST['weight'];
  $height = $_POST['height'];
  $chest = $_POST['chest'];
  $waist = $_POST['waist'];
  $arms = $_POST['arms'];
  $legs = $_POST['legs'];

  // BMI calculation (height now stored/entered in cm, convert to meters for calculation)
  $bmi = 0;
  $heightInMeters = $height / 100; // Convert cm to meters
  if ($heightInMeters > 0) {
    $bmi = $weight / ($heightInMeters * $heightInMeters);
  }

  $stmt = $conn->prepare("UPDATE users 
        SET weight=?, height=?, chest=?, waist=?, arms=?, legs=?, bmi=?, created_at=NOW()
        WHERE id=?");
  $stmt->bind_param("dddddddi", $weight, $height, $chest, $waist, $arms, $legs, $bmi, $user_id);
  $stmt->execute();
  $stmt->close();

  // Save weight to history if weight changed
  // First get current weight to compare
  $stmt = $conn->prepare("SELECT weight FROM users WHERE id=?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $oldUserData = $result->fetch_assoc();
  $stmt->close();

  $oldWeight = $oldUserData['weight'] ?? 0;
  if ($weight != $oldWeight) {
    $stmt = $conn->prepare("INSERT INTO weight_history (user_id, weight) VALUES (?, ?)");
    $stmt->bind_param("id", $user_id, $weight);
    $stmt->execute();
    $stmt->close();
  }
}

// Fetch updated user data
$stmt = $conn->prepare("SELECT weight, height, chest, waist, arms, legs, bmi FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

// Fetch weight history for chart
$stmt = $conn->prepare("SELECT weight, recorded_at FROM weight_history WHERE user_id=? ORDER BY recorded_at ASC LIMIT 30");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$weightHistory = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch workouts dynamically filtered by BMI
$stmt = $conn->prepare("SELECT id, title, description, sets, reps, rest, category 
                        FROM workout_templates 
                        WHERE (bmi_min IS NULL OR bmi_min <= ?) 
                          AND (bmi_max IS NULL OR bmi_max >= ?)");
$stmt->bind_param("dd", $userData['bmi'], $userData['bmi']);
$stmt->execute();
$result = $stmt->get_result();
$workouts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch meals dynamically filtered by BMI
$stmt = $conn->prepare("SELECT id, title, description, category 
                        FROM meal_templates 
                        WHERE (bmi_min IS NULL OR bmi_min <= ?) 
                          AND (bmi_max IS NULL OR bmi_max >= ?)");
$stmt->bind_param("dd", $userData['bmi'], $userData['bmi']);
$stmt->execute();
$result = $stmt->get_result();
$meals = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle add/remove user workouts (AJAX)
if (isset($_POST['action'])) {
  if ($_POST['action'] === 'add_workout' && isset($_POST['workout_id'])) {
    $workout_id = (int) $_POST['workout_id'];
    $stmt = $conn->prepare("INSERT IGNORE INTO user_workouts (user_id, workout_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $workout_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
    exit();
  }
  if ($_POST['action'] === 'remove_workout' && isset($_POST['workout_id'])) {
    $workout_id = (int) $_POST['workout_id'];
    $stmt = $conn->prepare("DELETE FROM user_workouts WHERE user_id = ? AND workout_id = ?");
    $stmt->bind_param("ii", $user_id, $workout_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
    exit();
  }
  if ($_POST['action'] === 'add_meal' && isset($_POST['meal_id'])) {
    $meal_id = (int) $_POST['meal_id'];
    $stmt = $conn->prepare("INSERT IGNORE INTO user_meals (user_id, meal_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $meal_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
    exit();
  }
  if ($_POST['action'] === 'remove_meal' && isset($_POST['meal_id'])) {
    $meal_id = (int) $_POST['meal_id'];
    $stmt = $conn->prepare("DELETE FROM user_meals WHERE user_id = ? AND meal_id = ?");
    $stmt->bind_param("ii", $user_id, $meal_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
    exit();
  }
}

// Fetch user's selected workouts
$stmt = $conn->prepare("SELECT wt.id, wt.title, wt.description, wt.sets, wt.reps, wt.rest, wt.category 
                        FROM user_workouts uw 
                        JOIN workout_templates wt ON uw.workout_id = wt.id 
                        WHERE uw.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$userWorkouts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch user's selected meals
$stmt = $conn->prepare("SELECT mt.id, mt.title, mt.description, mt.category 
                        FROM user_meals um 
                        JOIN meal_templates mt ON um.meal_id = mt.id 
                        WHERE um.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$userMeals = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch active announcements
$stmt = $conn->prepare("SELECT a.*, ad.username as admin_name 
                        FROM announcements a 
                        LEFT JOIN admins ad ON a.created_by = ad.id 
                        WHERE a.is_active = 1 
                        ORDER BY a.created_at DESC 
                        LIMIT 10");
$stmt->execute();
$result = $stmt->get_result();
$announcements = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Prepare BMI status and UI helpers
$bmi = floatval($userData['bmi'] ?? 0);
$bmiLabel = 'Unknown';
$bmiColor = 'secondary';
$bmiNote = '';
if ($bmi > 0) {
  if ($bmi < 18.5) {
    $bmiLabel = 'Underweight';
    $bmiColor = 'warning';
    $bmiNote = 'Try adding balanced calories and strength training.';
  } elseif ($bmi < 25) {
    $bmiLabel = 'Normal';
    $bmiColor = 'success';
    $bmiNote = 'Great job! Keep building consistent habits.';
  } elseif ($bmi < 30) {
    $bmiLabel = 'Overweight';
    $bmiColor = 'warning';
    $bmiNote = 'Focus on balanced nutrition and regular cardio.';
  } else {
    $bmiLabel = 'Obese';
    $bmiColor = 'danger';
    $bmiNote = 'Consider speaking with a coach or a doctor for a plan.';
  }
}

// Group workouts and meals by category for UI sections
$workoutsByCategory = [];
foreach ($workouts as $w) {
  $category = trim($w['category'] ?? '') ?: 'General';
  $workoutsByCategory[$category][] = $w;
}

$mealsByCategory = [];
foreach ($meals as $m) {
  $category = trim($m['category'] ?? '') ?: 'General';
  $mealsByCategory[$category][] = $m;
}

// Create a simple weekly workout schedule (workout / rest alternating days)
$weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$weeklySchedule = [];
$workoutIndex = 0;
foreach ($weekDays as $i => $day) {
  $isWorkoutDay = ($i % 2 === 0); // Mon, Wed, Fri, Sun
  if ($isWorkoutDay && !empty($workouts)) {
    $workout = $workouts[$workoutIndex % count($workouts)];
    $weeklySchedule[$day] = [
      'type' => 'workout',
      'workout' => $workout,
    ];
    $workoutIndex++;
  } else {
    $weeklySchedule[$day] = [
      'type' => 'rest',
    ];
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Member Dashboard - GYMgeekS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>

<body data-theme="dark">
  <!-- Navigation Bar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top border-bottom border-secondary shadow-sm">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="index.php">
        <i class="bi bi-dumbbell"></i> GYMgeekS
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <span class="nav-link">Welcome, <strong><?= htmlspecialchars($_SESSION['user_name']); ?></strong></span>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="logout.php">
              <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Announcements Section -->
  <?php if (!empty($announcements)): ?>
    <div class="bg-gradient py-3" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);">
      <div class="container">
        <div class="d-flex align-items-center mb-2">
          <i class="bi bi-megaphone-fill text-warning me-2"></i>
          <h6 class="mb-0 text-white">📢 Announcements</h6>
        </div>
        <div class="row g-2">
          <?php foreach ($announcements as $ann): ?>
            <div class="col-md-6 col-lg-4">
              <div class="card border-0 shadow-sm h-100" style="background: rgba(255,255,255,0.05);">
                <div class="card-body py-2">
                  <h6 class="text-warning mb-1">
                    <i class="bi bi-pin-angle me-1"></i><?= htmlspecialchars($ann['title']) ?>
                  </h6>
                  <p class="text-light small mb-0"><?= htmlspecialchars($ann['content']) ?></p>
                  <small class="text-muted d-block mt-1">
                    <i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($ann['admin_name'] ?? 'Admin') ?>
                    • <?= date('M d, Y', strtotime($ann['created_at'])) ?>
                  </small>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Main Content -->

  <div class="container">

    <?php if ($isNewUser): ?>
      <!-- New User Measurements Modal - Auto shown -->
      <div class="modal fade" id="newUserMeasurementsModal" tabindex="-1" aria-labelledby="newUserMeasurementsLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <form id="newUserMeasurementsForm" method="POST" class="needs-validation" novalidate>
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="newUserMeasurementsLabel">
                  <i class="bi bi-person-lines-fill me-2"></i>Welcome! Let's Get Your Measurements
                </h5>
              </div>
              <div class="modal-body">
                <p class="text-muted mb-3">Enter your body measurements to get personalized workout and meal plans based
                  on your BMI.</p>

                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label fw-600"><i class="bi bi-speedometer me-1"></i>Weight <span
                        class="text-danger">*</span></label>
                    <div class="input-group">
                      <input type="number" step="0.1" name="weight" class="form-control" placeholder="Enter weight"
                        required>
                      <span class="input-group-text">kg</span>
                    </div>
                    <div class="invalid-feedback">Please enter your weight</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label fw-600"><i class="bi bi-arrows-vertical me-1"></i>Height <span
                        class="text-danger">*</span></label>
                    <div class="input-group">
                      <input type="number" step="1" name="height" class="form-control" placeholder="Enter height"
                        required>
                      <span class="input-group-text">cm</span>
                    </div>
                    <div class="form-text text-muted small">e.g., 175 for 175cm</div>
                  </div>
                </div>

                <hr class="my-4">
                <p class="text-muted small mb-3">Optional body measurements (can be added later):</p>

                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-circle me-1"></i>Chest</label>
                    <div class="input-group">
                      <input type="number" name="chest" class="form-control" placeholder="Optional">
                      <span class="input-group-text">cm</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-circle me-1"></i>Waist</label>
                    <div class="input-group">
                      <input type="number" name="waist" class="form-control" placeholder="Optional">
                      <span class="input-group-text">cm</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-circle me-1"></i>Arms</label>
                    <div class="input-group">
                      <input type="number" name="arms" class="form-control" placeholder="Optional">
                      <span class="input-group-text">cm</span>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-circle me-1"></i>Legs</label>
                    <div class="input-group">
                      <input type="number" name="legs" class="form-control" placeholder="Optional">
                      <span class="input-group-text">cm</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer bg-light">
                <button type="submit" class="btn btn-primary btn-lg">
                  <i class="bi bi-check-circle me-2"></i>Save & Continue
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <script>
        // Auto-show the modal for new users
        document.addEventListener('DOMContentLoaded', function () {
          var modal = new bootstrap.Modal(document.getElementById('newUserMeasurementsModal'));
          modal.show();
        });
      </script>
    <?php endif; ?>

    <!-- Update sizes button + modal -->
    <div class="d-flex justify-content-between align-items-start mb-4">
      <div>
        <h3 class="mb-1"><i class="bi bi-rulers me-2"></i>Your Measurements</h3>
        <p class="text-muted mb-0">💡 Tip: Update your measurements monthly for accurate personalized plans</p>
      </div>
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#updateSizesModal">
        <i class="bi bi-pencil me-1"></i>Update
      </button>
    </div>

    <!-- Update Sizes Modal -->
    <div class="modal fade" id="updateSizesModal" tabindex="-1" aria-labelledby="updateSizesLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form id="updateSizesForm" method="POST" class="needs-validation" novalidate>
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title" id="updateSizesLabel">
                <i class="bi bi-arrow-repeat me-2"></i>Update Your Measurements
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p class="text-muted small mb-3">All measurements help personalize your fitness plan</p>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-600"><i class="bi bi-speedometer me-1"></i>Weight <span
                      class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="number" step="0.1" name="weight" class="form-control" placeholder="Enter weight"
                      value="<?= htmlspecialchars($userData['weight'] ?? '') ?>" required>
                    <span class="input-group-text">kg</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-600"><i class="bi bi-arrows-vertical me-1"></i>Height <span
                      class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="number" step="0.01" name="height" class="form-control" placeholder="Enter height"
                      value="<?= htmlspecialchars($userData['height'] ?? '') ?>" required>
                    <span class="input-group-text">m</span>
                  </div>
                </div>
              </div>

              <hr class="my-3">
              <p class="text-muted small mb-2">Optional body measurements:</p>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label"><i class="bi bi-circle me-1"></i>Chest</label>
                  <div class="input-group">
                    <input type="number" name="chest" class="form-control" placeholder="Optional"
                      value="<?= htmlspecialchars($userData['chest'] ?? '') ?>">
                    <span class="input-group-text">cm</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label"><i class="bi bi-circle me-1"></i>Waist</label>
                  <div class="input-group">
                    <input type="number" name="waist" class="form-control" placeholder="Optional"
                      value="<?= htmlspecialchars($userData['waist'] ?? '') ?>">
                    <span class="input-group-text">cm</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label"><i class="bi bi-circle me-1"></i>Arms</label>
                  <div class="input-group">
                    <input type="number" name="arms" class="form-control" placeholder="Optional"
                      value="<?= htmlspecialchars($userData['arms'] ?? '') ?>">
                    <span class="input-group-text">cm</span>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label"><i class="bi bi-circle me-1"></i>Legs</label>
                  <div class="input-group">
                    <input type="number" name="legs" class="form-control" placeholder="Optional"
                      value="<?= htmlspecialchars($userData['legs'] ?? '') ?>">
                    <span class="input-group-text">cm</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer bg-light">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-1"></i>Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <?php if (!empty($userData['weight']) && !empty($userData['height'])): ?>
      <!-- Sizes Display -->
      <div class="row text-center mb-4">
        <?php foreach (['weight' => 'kg', 'height' => 'm', 'chest' => 'cm', 'waist' => 'cm', 'arms' => 'cm', 'legs' => 'cm'] as $field => $unit): ?>
          <div class="col-md-4 mb-3">
            <div class="card shadow">
              <div class="card-body">
                <h6 class="card-title"><?= ucfirst($field) ?></h6>
                <p class="fw-bold text-primary"><span><?= htmlspecialchars($userData[$field]) ?></span> <span
                    class="text-muted"><?= $unit ?></span></p>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- BMI Status -->
      <div class="row text-center mb-4">
        <div class="col-md-4 mb-3">
          <div class="card shadow border-<?= $bmiColor ?>">
            <div class="card-header bg-<?= $bmiColor ?> text-white">BMI Status</div>
            <div class="card-body">
              <h3 class="fw-bold mb-1">
                <?= $bmi > 0 ? number_format($bmi, 1) : '—' ?>
              </h3>
              <p class="mb-1">You are <strong>
                  <?= htmlspecialchars($bmiLabel) ?>
                </strong>.</p>
              <p class="small text-muted mb-2">Healthy range: 18.5–24.9</p>
              <div class="position-relative" style="height:180px;">
                <canvas id="bmiChart"></canvas>
              </div>
              <?php if ($bmiNote): ?>
                <p class="small text-muted mt-2">
                  <?= htmlspecialchars($bmiNote) ?>
                </p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        Update your weight and height above to see personalized BMI insights and your weekly schedule.
      </div>
    <?php endif; ?>

    <!-- Weekly Workout Schedule -->
    <div class="card shadow mb-3">
      <div class="card-header bg-info text-white">📅 Weekly Workout Schedule</div>
      <div class="card-body">
        <div class="row row-cols-1 row-cols-md-3 row-cols-xl-7 g-3">
          <?php foreach ($weeklySchedule as $day => $plan): ?>
            <div class="col">
              <div class="card h-100 border-<?= $plan['type'] === 'rest' ? 'secondary' : 'primary' ?>">
                <div
                  class="card-header bg-<?= $plan['type'] === 'rest' ? 'light' : 'primary' ?> text-<?= $plan['type'] === 'rest' ? 'dark' : 'white' ?>">
                  <?= htmlspecialchars($day) ?>
                </div>
                <div class="card-body p-2 d-flex flex-column">
                  <?php if ($plan['type'] === 'rest'): ?>
                    <p class="mb-0 fw-bold">Rest Day</p>
                    <p class="small text-muted mb-2">Recovery & mobility</p>
                    <button type="button" class="btn btn-sm btn-outline-light mt-auto"
                      onclick="startWorkoutForDay('<?= addslashes($day) ?>')">Start Light Stretch</button>
                  <?php else: ?>
                    <p class="mb-1 fw-bold">
                      <?= htmlspecialchars($plan['workout']['title']) ?>
                    </p>
                    <p class="small text-muted mb-1">
                      <?= htmlspecialchars($plan['workout']['description']) ?>
                    </p>
                    <div class="small mb-2">
                      Sets:
                      <?= htmlspecialchars($plan['workout']['sets']) ?> •
                      Reps:
                      <?= htmlspecialchars($plan['workout']['reps']) ?> •
                      Rest:
                      <?= htmlspecialchars($plan['workout']['rest']) ?>s
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-light mt-auto"
                      onclick="startWorkoutForDay('<?= addslashes($day) ?>')">Start</button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Weight History Chart -->
    <div class="card shadow mb-3">
      <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <h5 class="mb-0 text-white"><i class="bi bi-graph-up-arrow me-2"></i>Weight Progress Journey</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-8">
            <canvas id="weightHistoryChart" height="100"></canvas>
          </div>
          <div class="col-md-4">
            <div class="weight-stats h-100 d-flex flex-column justify-content-center">
              <?php if (count($weightHistory) > 1): ?>
                <?php
                $firstWeight = $weightHistory[0]['weight'];
                $lastWeight = end($weightHistory)['weight'];
                $change = $lastWeight - $firstWeight;
                ?>
                <div class="text-center mb-3">
                  <h6 class="text-muted">Total Change</h6>
                  <h3 class="<?= $change <= 0 ? 'text-success' : 'text-warning' ?>">
                    <i class="bi <?= $change <= 0 ? 'bi-arrow-down-circle' : 'bi-arrow-up-circle' ?>"></i>
                    <?= number_format(abs($change), 1) ?> kg
                  </h3>
                </div>
                <div class="text-center">
                  <h6 class="text-muted">Current</h6>
                  <h2 class="fw-bold text-primary"><?= number_format($lastWeight, 1) ?> <small>kg</small></h2>
                </div>
              <?php else: ?>
                <div class="text-center text-muted">
                  <i class="bi bi-info-circle"></i>
                  <p>Update your weight a few times to see your progress chart!</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Weekly Progress Chart -->
    <div class="card shadow mb-3">
      <div class="card-header bg-secondary text-white">📈 Weekly Progress</div>
      <div class="card-body">
        <canvas id="progressChart" height="120"></canvas>
      </div>
    </div>

    <!-- My Selected Workouts -->
    <?php if (!empty($userWorkouts)): ?>
      <div class="card shadow mb-3 border-warning">
        <div class="card-header bg-warning text-dark">
          <h5 class="mb-0"><i class="bi bi-check2-circle me-2"></i>My Workouts</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <?php foreach ($userWorkouts as $uw): ?>
              <div class="col-md-6 col-lg-4">
                <div class="card border-warning h-100">
                  <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <h6 class="mb-0 text-warning"><?= htmlspecialchars($uw['title']) ?></h6>
                      <button type="button" class="btn btn-sm btn-outline-danger btn-remove-workout"
                        data-workout-id="<?= $uw['id'] ?>">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                    <div class="small text-muted mb-2"><?= htmlspecialchars($uw['description']) ?></div>
                    <div class="small">
                      <span class="badge bg-secondary"><?= htmlspecialchars($uw['category']) ?></span>
                      <span class="text-muted">Sets: <?= htmlspecialchars($uw['sets']) ?> | Reps:
                        <?= htmlspecialchars($uw['reps']) ?> | Rest: <?= htmlspecialchars($uw['rest']) ?>s</span>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Workout Plan -->
    <div class="card shadow mb-3">
      <div class="card-header bg-primary text-white">🏋️ Available Workouts (By Category)</div>
      <div class="card-body">
        <?php if (!empty($workoutsByCategory)): ?>
          <div class="accordion" id="workoutAccordion">
            <?php $idx = 0;
            foreach ($workoutsByCategory as $cat => $list):
              $idx++; ?>
              <div class="accordion-item">
                <h2 class="accordion-header" id="workoutHeading<?= $idx ?>">
                  <button class="accordion-button <?= $idx !== 1 ? 'collapsed' : '' ?>" type="button"
                    data-bs-toggle="collapse" data-bs-target="#workoutCollapse<?= $idx ?>"
                    aria-expanded="<?= $idx === 1 ? 'true' : 'false' ?>" aria-controls="workoutCollapse<?= $idx ?>">
                    <?= htmlspecialchars($cat) ?> (<?= count($list) ?>)
                  </button>
                </h2>
                <div id="workoutCollapse<?= $idx ?>" class="accordion-collapse collapse <?= $idx === 1 ? 'show' : '' ?>"
                  aria-labelledby="workoutHeading<?= $idx ?>" data-bs-parent="#workoutAccordion">
                  <div class="accordion-body p-0">
                    <div class="list-group list-group-flush">
                      <?php foreach ($list as $w): ?>
                        <div class="list-group-item">
                          <div class="d-flex justify-content-between align-items-start">
                            <div>
                              <div class="fw-bold"><?= htmlspecialchars($w['title']) ?></div>
                              <div class="text-muted small"><?= htmlspecialchars($w['description']) ?></div>
                            </div>
                            <div class="text-end small">
                              <div>Sets: <?= htmlspecialchars($w['sets']) ?></div>
                              <div>Reps: <?= htmlspecialchars($w['reps']) ?></div>
                              <div>Rest: <?= htmlspecialchars($w['rest']) ?>s</div>
                            </div>
                            <div class="ms-2">
                              <button type="button" class="btn btn-sm btn-success btn-add-workout"
                                data-workout-id="<?= $w['id'] ?>" title="Add to My Workouts">
                                <i class="bi bi-plus-lg"></i> Add
                              </button>
                            </div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-muted">No workouts available for your BMI.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- My Selected Meals -->
    <?php if (!empty($userMeals)): ?>
      <div class="card shadow mb-3 border-info">
        <div class="card-header bg-info text-dark">
          <h5 class="mb-0"><i class="bi bi-check2-circle me-2"></i>My Meals</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <?php foreach ($userMeals as $um): ?>
              <div class="col-md-6 col-lg-4">
                <div class="card border-info h-100">
                  <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <h6 class="mb-0 text-info"><?= htmlspecialchars($um['title']) ?></h6>
                      <button type="button" class="btn btn-sm btn-outline-danger btn-remove-meal"
                        data-meal-id="<?= $um['id'] ?>">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                    <div class="small text-muted mb-2"><?= htmlspecialchars($um['description']) ?></div>
                    <div class="small">
                      <span class="badge bg-secondary"><?= htmlspecialchars($um['category']) ?></span>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Meal Plan -->
    <div class="card shadow mb-3">
      <div class="card-header bg-success text-white">🍽 Available Meals (By Category)</div>
      <div class="card-body">
        <?php if (!empty($mealsByCategory)): ?>
          <div class="row g-3">
            <?php foreach ($mealsByCategory as $cat => $list): ?>
              <div class="col-md-6">
                <div class="card border-secondary h-100">
                  <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><?= htmlspecialchars($cat) ?> (<?= count($list) ?>)</span>
                  </div>
                  <ul class="list-group list-group-flush">
                    <?php foreach ($list as $m): ?>
                      <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                          <div class="flex-grow-1">
                            <div class="fw-bold"><?= htmlspecialchars($m['title']) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($m['description']) ?></div>
                          </div>
                          <div class="ms-2">
                            <button type="button" class="btn btn-sm btn-success btn-add-meal" data-meal-id="<?= $m['id'] ?>"
                              title="Add to My Meals">
                              <i class="bi bi-plus-lg"></i>
                            </button>
                          </div>
                        </div>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="text-muted">No meals available for your BMI.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Customize Plan Section -->
    <div class="card shadow mb-3 border-warning">
      <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="bi bi-sliders me-2"></i>Customize Your Plan</h5>
      </div>
      <div class="card-body">
        <p class="text-muted">Want to see different meals or workouts? Select a BMI category below to preview plans:</p>

        <form method="GET" class="row g-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label fw-600">Preview Meals For:</label>
            <select name="preview_bmi" class="form-select" onchange="this.form.submit()">
              <option value="">-- My Current BMI --</option>
              <option value="underweight">Underweight (< 18.5)</option>
              <option value="normal">Normal (18.5 - 24.9)</option>
              <option value="overweight">Overweight (25 - 29.9)</option>
              <option value="obese">Obese (30+)</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-600">Preview Workouts For:</label>
            <select name="preview_workout" class="form-select" onchange="this.form.submit()">
              <option value="">-- My Current BMI --</option>
              <option value="underweight">Underweight (< 18.5)</option>
              <option value="normal">Normal (18.5 - 24.9)</option>
              <option value="overweight">Overweight (25 - 29.9)</option>
              <option value="obese">Obese (30+)</option>
            </select>
          </div>
          <div class="col-md-4">
            <button type="button" class="btn btn-outline-primary w-100" onclick="resetPreview()">
              <i class="bi bi-arrow-counterclockwise me-1"></i>Reset to My BMI
            </button>
          </div>
        </form>

        <?php
        // Handle preview mode
        $previewBMI = null;
        $previewWorkout = null;

        if (isset($_GET['preview_bmi']) && !empty($_GET['preview_bmi'])) {
          $previewBMI = $_GET['preview_bmi'];
        }
        if (isset($_GET['preview_workout']) && !empty($_GET['preview_workout'])) {
          $previewWorkout = $_GET['preview_workout'];
        }

        // Determine BMI ranges for preview
        $bmiRanges = [
          'underweight' => ['min' => 0, 'max' => 18.5],
          'normal' => ['min' => 18.5, 'max' => 25],
          'overweight' => ['min' => 25, 'max' => 30],
          'obese' => ['min' => 30, 'max' => 100]
        ];

        // Fetch preview meals if selected
        $previewMeals = [];
        if ($previewBMI && isset($bmiRanges[$previewBMI])) {
          $range = $bmiRanges[$previewBMI];
          $stmt = $conn->prepare("SELECT id, title, description, category 
            FROM meal_templates 
            WHERE (bmi_min IS NULL OR bmi_min <= ?) 
              AND (bmi_max IS NULL OR bmi_max > ?)");
          $stmt->bind_param("dd", $range['max'], $range['min']);
          $stmt->execute();
          $result = $stmt->get_result();
          $previewMeals = $result->fetch_all(MYSQLI_ASSOC);
          $stmt->close();
        }

        // Fetch preview workouts if selected
        $previewWorkouts = [];
        if ($previewWorkout && isset($bmiRanges[$previewWorkout])) {
          $range = $bmiRanges[$previewWorkout];
          $stmt = $conn->prepare("SELECT id, title, description, sets, reps, rest, category 
            FROM workout_templates 
            WHERE (bmi_min IS NULL OR bmi_min <= ?) 
              AND (bmi_max IS NULL OR bmi_max > ?)");
          $stmt->bind_param("dd", $range['max'], $range['min']);
          $stmt->execute();
          $result = $stmt->get_result();
          $previewWorkouts = $result->fetch_all(MYSQLI_ASSOC);
          $stmt->close();
        }
        ?>

        <?php if ($previewBMI || $previewWorkout): ?>
          <hr class="my-4">
          <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>Preview mode: Showing plans for
            <?= $previewBMI ? htmlspecialchars(ucfirst($previewBMI)) : 'your BMI' ?>
            <?php if ($previewBMI && $previewWorkout): ?> and
              <?= htmlspecialchars(ucfirst($previewWorkout)) ?>
            <?php endif; ?>
          </div>

          <?php if ($previewMeals): ?>
            <div class="row g-3 mb-4">
              <div class="col-12">
                <h6 class="text-warning"><i class="bi bi-cup-hot me-2"></i>Preview Meals
                  (<?= htmlspecialchars(ucfirst($previewBMI)) ?>):</h6>
              </div>
              <?php foreach ($previewMeals as $pm): ?>
                <div class="col-md-4">
                  <div class="card border-warning bg-dark">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="fw-bold text-warning"><?= htmlspecialchars($pm['title']) ?></div>
                        <button type="button" class="btn btn-sm btn-success btn-add-meal" data-meal-id="<?= $pm['id'] ?>">
                          <i class="bi bi-plus-lg"></i> Add
                        </button>
                      </div>
                      <div class="small text-muted"><?= htmlspecialchars($pm['description']) ?></div>
                      <span class="badge bg-secondary"><?= htmlspecialchars($pm['category']) ?></span>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($previewWorkouts): ?>
            <div class="row g-3">
              <div class="col-12">
                <h6 class="text-warning"><i class="bi bi-dumbbell me-2"></i>Preview Workouts
                  (<?= htmlspecialchars(ucfirst($previewWorkout)) ?>):</h6>
              </div>
              <?php foreach ($previewWorkouts as $pw): ?>
                <div class="col-md-4">
                  <div class="card border-warning bg-dark">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="fw-bold text-warning"><?= htmlspecialchars($pw['title']) ?></div>
                        <button type="button" class="btn btn-sm btn-success btn-add-workout"
                          data-workout-id="<?= $pw['id'] ?>">
                          <i class="bi bi-plus-lg"></i> Add
                        </button>
                      </div>
                      <div class="small text-muted"><?= htmlspecialchars($pw['description']) ?></div>
                      <div class="small">Sets: <?= htmlspecialchars($pw['sets']) ?> | Reps:
                        <?= htmlspecialchars($pw['reps']) ?>
                      </div>
                      <span class="badge bg-secondary"><?= htmlspecialchars($pw['category']) ?></span>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <script>
      function resetPreview() {
        window.location.href = 'members.php';
      }

      // Add workout to user's list
      document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-add-workout')) {
          const btn = e.target.closest('.btn-add-workout');
          const workoutId = btn.dataset.workoutId;

          fetch('members.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=add_workout&workout_id=' + workoutId
          })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                location.reload();
              }
            });
        }

        if (e.target.closest('.btn-remove-workout')) {
          const btn = e.target.closest('.btn-remove-workout');
          const workoutId = btn.dataset.workoutId;

          if (confirm('Remove this workout from your list?')) {
            fetch('members.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: 'action=remove_workout&workout_id=' + workoutId
            })
              .then(res => res.json())
              .then(data => {
                if (data.success) {
                  location.reload();
                }
              });
          }
        }

        if (e.target.closest('.btn-add-meal')) {
          const btn = e.target.closest('.btn-add-meal');
          const mealId = btn.dataset.mealId;

          fetch('members.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=add_meal&meal_id=' + mealId
          })
            .then(res => res.json())
            .then(data => {
              if (data.success) {
                location.reload();
              }
            });
        }

        if (e.target.closest('.btn-remove-meal')) {
          const btn = e.target.closest('.btn-remove-meal');
          const mealId = btn.dataset.mealId;

          if (confirm('Remove this meal from your list?')) {
            fetch('members.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: 'action=remove_meal&meal_id=' + mealId
            })
              .then(res => res.json())
              .then(data => {
                if (data.success) {
                  location.reload();
                }
              });
          }
        }
      });
    </script>



    <a href="logout.php" class="btn btn-danger mt-4">Logout</a>
  </div>


  <!-- Workout Modal -->
  <div class="modal fade" id="workoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Workout Assistant</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p id="exerciseText"></p>
          <div class="progress mb-2">
            <div id="restBar" class="progress-bar bg-danger" role="progressbar" style="width:0%"></div>
          </div>
          <p id="restText" class="text-danger fw-bold"></p>
        </div>
        <div class="modal-footer">
          <button id="quitBtn" type="button" class="btn btn-secondary">Quit</button>
          <button id="nextBtn" class="btn btn-primary">Next</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const workouts = <?= json_encode($workouts) ?>;
    const weeklySchedule = <?= json_encode($weeklySchedule) ?>;

    let activeWorkouts = workouts;
    let currentExerciseIndex = 0;
    let currentSet = 1;
    let timerInterval = null;
    let timerRemaining = 0;
    let workoutModal = null;
    let progressChart = null;

    function formatTime(seconds) {
      const mins = Math.floor(seconds / 60);
      const secs = seconds % 60;
      return mins > 0 ? `${mins}:${secs.toString().padStart(2, '0')}` : `${secs}s`;
    }

    const EXERCISE_SECONDS = 1 * 60; // 15 minutes per exercise
    const REST_SECONDS = 60; // 1 minute rest
    let currentPhase = 'exercise'; // 'exercise' | 'rest'

    function renderExercise() {
      const exerciseTextEl = document.getElementById('exerciseText');
      const restTextEl = document.getElementById('restText');
      const restBarEl = document.getElementById('restBar');
      const nextBtn = document.getElementById('nextBtn');

      if (currentExerciseIndex >= activeWorkouts.length) {
        exerciseTextEl.innerText = '🎉 Workout complete! Great job!';
        restTextEl.innerText = '';
        restBarEl.style.width = '0%';
        nextBtn.style.display = 'none';

        // Track progress and refresh chart
        recordWorkoutCompletion();
        renderProgressChart();
        if (window.gymgeeks) {
          gymgeeks.confetti();
          gymgeeks.showToast('Great job! Workout complete.', 'success');
        }
        return;
      }

      const workout = activeWorkouts[currentExerciseIndex];
      const totalSets = workout.sets || 1;
      exerciseTextEl.innerText = `${workout.title} — ${workout.description}\nSet ${currentSet} of ${totalSets} • ${workout.reps} reps`;

      // Reset UI
      restTextEl.innerText = `Get ready...`;
      restBarEl.style.width = '0%';
      nextBtn.style.display = 'inline-block';
      nextBtn.disabled = true;

      // Start with exercise timer, then automatically start rest timer
      currentPhase = 'exercise';
      startPhaseTimer(EXERCISE_SECONDS);
    }

    function startPhaseTimer(seconds) {
      const restTextEl = document.getElementById('restText');
      const restBarEl = document.getElementById('restBar');
      const nextBtn = document.getElementById('nextBtn');

      clearInterval(timerInterval);
      timerRemaining = seconds;

      updatePhaseUI();

      timerInterval = setInterval(() => {
        timerRemaining -= 1;
        if (timerRemaining < 0) {
          clearInterval(timerInterval);

          if (currentPhase === 'exercise') {
            currentPhase = 'rest';
            startPhaseTimer(REST_SECONDS);
            return;
          }

          // Rest finished
          nextBtn.disabled = false;
          restTextEl.innerText = '✅ Ready! Tap Next to continue.';
          restBarEl.style.width = '100%';
          return;
        }

        updatePhaseUI();
      }, 1000);

      function updatePhaseUI() {
        const total = currentPhase === 'exercise' ? EXERCISE_SECONDS : REST_SECONDS;
        const phaseLabel = currentPhase === 'exercise' ? 'Exercise' : 'Rest';
        restTextEl.innerText = `⏳ ${phaseLabel}: ${formatTime(timerRemaining)}`;
        const percent = ((total - timerRemaining) / total) * 100;
        restBarEl.style.width = `${percent}%`;
      }
    }

    function teardownWorkout() {
      clearInterval(timerInterval);
      timerInterval = null;
      currentExerciseIndex = 0;
      currentSet = 1;
    }

    function nextStep() {
      const workout = activeWorkouts[currentExerciseIndex];
      if (!workout) return;

      if (currentSet < (workout.sets || 1)) {
        currentSet += 1;
      } else {
        currentExerciseIndex += 1;
        currentSet = 1;
      }

      renderExercise();
    }

    function quitWorkout() {
      teardownWorkout();
      if (workoutModal) {
        workoutModal.hide();
      }
    }

    function startWorkoutSession(workoutList) {
      if (!Array.isArray(workoutList) || workoutList.length === 0) {
        alert('No workouts available for your BMI.');
        return;
      }

      activeWorkouts = workoutList;
      workoutModal = new bootstrap.Modal(document.getElementById('workoutModal'));
      workoutModal.show();
      currentExerciseIndex = 0;
      currentSet = 1;
      renderExercise();
    }

    function startServerWorkout() {
      startWorkoutSession(workouts);
    }

    function startWorkoutForDay(day) {
      const plan = weeklySchedule[day];
      if (!plan) {
        alert('No schedule found for that day.');
        return;
      }

      if (plan.type === 'rest') {
        startWorkoutSession([{
          title: 'Light Stretch & Mobility',
          description: 'Use this time to stretch, breathe, and reset.',
          sets: 1,
          reps: 1,
          rest: 120
        }]);
        return;
      }

      startWorkoutSession([plan.workout]);
    }

    // Progress tracking (stored locally per user/browser)
    function getProgressData() {
      try {
        const raw = localStorage.getItem('gymgeeks_progress');
        return raw ? JSON.parse(raw) : {};
      } catch {
        return {};
      }
    }

    function saveProgressData(data) {
      try {
        localStorage.setItem('gymgeeks_progress', JSON.stringify(data));
      } catch {
        // ignore storage errors
      }
    }

    function recordWorkoutCompletion() {
      const today = new Date().toISOString().slice(0, 10);
      const data = getProgressData();
      data[today] = (data[today] || 0) + 1;
      saveProgressData(data);
    }

    function getLast7Days() {
      const labels = [];
      const values = [];
      const data = getProgressData();
      for (let i = 6; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        const key = d.toISOString().slice(0, 10);
        labels.push(d.toLocaleDateString(undefined, { weekday: 'short' }));
        values.push(data[key] || 0);
      }
      return { labels, values };
    }

    function renderProgressChart() {
      const ctx = document.getElementById('progressChart');
      if (!ctx) return;

      const { labels, values } = getLast7Days();

      const chartData = {
        labels,
        datasets: [{
          label: 'Workouts completed',
          data: values,
          backgroundColor: '#0d6efd',
          borderRadius: 8,
          maxBarThickness: 40
        }]
      };

      if (progressChart) {
        progressChart.data = chartData;
        progressChart.update();
        return;
      }

      progressChart = new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: {
          scales: {
            y: {
              beginAtZero: true,
              ticks: { stepSize: 1 }
            }
          },
          plugins: {
            legend: { display: false }
          }
        }
      });
    }

    document.addEventListener('DOMContentLoaded', () => {
      const nextBtn = document.getElementById('nextBtn');
      const quitBtn = document.getElementById('quitBtn');
      const modalEl = document.getElementById('workoutModal');

      if (nextBtn) nextBtn.addEventListener('click', nextStep);
      if (quitBtn) quitBtn.addEventListener('click', quitWorkout);

      if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', () => {
          teardownWorkout();
        });
      }

      // BMI Chart (renders a quick gauge-style donut)
      const userBMI = <?= json_encode($bmi) ?>;
      const bmiLabel = <?= json_encode($bmiLabel) ?>;
      const bmiColor = <?= json_encode($bmiColor) ?>;

      if (userBMI > 0) {
        const ctx = document.getElementById('bmiChart');
        if (ctx) {
          const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
              labels: ['BMI', 'Remaining'],
              datasets: [{
                data: [userBMI, Math.max(0, 40 - userBMI)],
                backgroundColor: [
                  bmiColor === 'success' ? '#198754' : bmiColor === 'warning' ? '#ffc107' : '#dc3545',
                  '#e9ecef'
                ],
                borderWidth: 0
              }]
            },
            options: {
              cutout: '75%',
              plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
              }
            }
          });

          const chartWrapper = ctx.parentElement;
          if (chartWrapper) {
            chartWrapper.style.position = 'relative';
            const labelEl = document.createElement('div');
            labelEl.className = 'position-absolute top-50 start-50 translate-middle text-center';
            labelEl.style.pointerEvents = 'none';
            labelEl.innerHTML = `<div class="fw-bold">${userBMI.toFixed(1)}</div><div class="small text-${bmiColor}">${bmiLabel}</div>`;
            chartWrapper.appendChild(labelEl);
          }
        }
      }

      // Render progress chart (workouts completed this week)
      renderProgressChart();

      // Weight History Chart
      const weightHistory = <?= json_encode($weightHistory) ?>;
      if (weightHistory && weightHistory.length > 0) {
        const weightCtx = document.getElementById('weightHistoryChart');
        if (weightCtx) {
          const labels = weightHistory.map((w, i) => {
            const date = new Date(w.recorded_at);
            return i === 0 || i === weightHistory.length - 1
              ? date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' })
              : date.toLocaleDateString(undefined, { month: 'short' });
          });
          const weights = weightHistory.map(w => w.weight);

          // Create gradient
          const gradient = weightCtx.getContext('2d').createLinearGradient(0, 0, 0, 400);
          gradient.addColorStop(0, 'rgba(102, 126, 234, 0.8)');
          gradient.addColorStop(1, 'rgba(118, 75, 162, 0.2)');

          new Chart(weightCtx, {
            type: 'line',
            data: {
              labels: labels,
              datasets: [{
                label: 'Weight (kg)',
                data: weights,
                borderColor: '#667eea',
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#764ba2',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointHoverBackgroundColor: '#764ba2',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 3
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: { display: false },
                tooltip: {
                  backgroundColor: 'rgba(102, 126, 234, 0.9)',
                  titleColor: '#fff',
                  bodyColor: '#fff',
                  padding: 12,
                  cornerRadius: 8,
                  displayColors: false,
                  callbacks: {
                    label: function (context) {
                      return context.parsed.y.toFixed(1) + ' kg';
                    }
                  }
                }
              },
              scales: {
                x: {
                  grid: { display: false },
                  ticks: { color: '#6c757d' }
                },
                y: {
                  grid: { color: 'rgba(0,0,0,0.05)' },
                  ticks: { color: '#6c757d' }
                }
              },
              interaction: {
                intersect: false,
                mode: 'index'
              }
            }
          });
        }
      }
    });
  </script>
</body>

</html>