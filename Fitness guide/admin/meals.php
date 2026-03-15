<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: index.php");
  exit();
}
include("../includes/db.php");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Deletion request
  if (isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM meal_templates WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    header("Location: meals.php");
    exit();
  }

  // Add new meal
  $title = $_POST['title'];
  $description = $_POST['description'];
  $category = $_POST['category'];

  $stmt = $conn->prepare("INSERT INTO meal_templates (title, description, category, created_at) VALUES (?, ?, ?, NOW())");
  $stmt->bind_param("sss", $title, $description, $category);
  $stmt->execute();
  $stmt->close();
  header("Location: meals.php");
  exit();
}

// Fetch meals
$result = $conn->query("SELECT id, title, description, category FROM meal_templates ORDER BY created_at DESC");
$meals = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
  <title>Manage Meals</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
      min-height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }

    h2 {
      color: #fff;
      font-weight: bold;
      text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
    }

    .card {
      border-radius: 12px;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .table {
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
    }

    .table thead {
      background: #e58e26;
      color: #fff;
    }

    .btn-primary {
      background: #fa983a;
      border: none;
    }

    .btn-primary:hover {
      background: #e58e26;
    }

    .btn-success {
      background: #78e08f;
      border: none;
    }

    .btn-success:hover {
      background: #38ada9;
    }
  </style>
</head>

<body>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0 text-center">🍽 Manage Meals</h2>
      <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <!-- Add Button -->
    <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addMealModal">
      ➕ Add Meal
    </button>

    <!-- Meals List -->
    <div class="card p-4 shadow">
      <h4>Existing Meals</h4>
      <?php if (empty($meals)): ?>
        <p class="text-muted">No meals added yet.</p>
      <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 g-4">
          <?php foreach ($meals as $meal): ?>
            <div class="col">
              <div class="card h-100">
                <div class="card-body d-flex flex-column">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <h5 class="card-title mb-1"><?= htmlspecialchars($meal['title']) ?></h5>
                      <span class="badge bg-info text-dark"><?= htmlspecialchars($meal['category']) ?></span>
                    </div>
                    <form method="POST" onsubmit="return confirm('Delete this meal?');">
                      <input type="hidden" name="delete_id" value="<?= $meal['id'] ?>">
                      <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                  </div>
                  <p class="card-text mt-3 mb-0"><?= htmlspecialchars($meal['description']) ?></p>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Add Meal Modal -->
  <div class="modal fade" id="addMealModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Add New Meal</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="text" name="title" class="form-control mb-2" placeholder="Meal Title" required>
            <textarea name="description" class="form-control mb-2" placeholder="Meal Description" required></textarea>
            <select name="category" class="form-control mb-2" required>
              <option value="">Select Category</option>
              <option value="Weight Loss">Weight Loss</option>
              <option value="Muscle Gain">Muscle Gain</option>
              <option value="Balanced">Balanced</option>
            </select>
          </div>
          <div class="modal-footer">
            <button class="btn btn-success">Add Meal</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>