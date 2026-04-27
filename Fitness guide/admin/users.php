<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: index.php");
  exit();
}

include("../includes/db.php");

// Handle approve request
if (isset($_POST['approve_id'])) {
  $user_id = intval($_POST['approve_id']);
  $stmt = $conn->prepare("UPDATE users SET status = 'approved', updated_at = NOW() WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->close();
  header("Location: users.php?success=User approved successfully");
  exit();
}

// Handle reject request
if (isset($_POST['reject_id'])) {
  $user_id = intval($_POST['reject_id']);
  $stmt = $conn->prepare("UPDATE users SET status = 'rejected', updated_at = NOW() WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->close();
  header("Location: users.php?success=User rejected");
  exit();
}

// Handle delete request (soft delete)
if (isset($_POST['delete_id'])) {
  $user_id = intval($_POST['delete_id']);
  $stmt = $conn->prepare("UPDATE users SET status = 'deleted', updated_at = NOW() WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->close();
  header("Location: users.php?success=User deleted successfully");
  exit();
}

// Fetch users (exclude deleted by default, but allow viewing all)
$showDeleted = isset($_GET['show_all']) && $_GET['show_all'] === '1';
if ($showDeleted) {
  $result = $conn->query("SELECT id, username, email, status, bmi, weight, height, created_at FROM users ORDER BY created_at DESC");
} else {
  $result = $conn->query("SELECT id, username, email, status, bmi, weight, height, created_at FROM users WHERE status != 'deleted' ORDER BY created_at DESC");
}

// Count pending users
$pendingResult = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'pending'");
$pendingCount = $pendingResult->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Users - GYMgeekS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="dashboard.php">
        <i class="bi bi-lock-fill me-2"></i>GYMgeekS Admin
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin"
        aria-controls="navbarAdmin" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarAdmin">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
              <i class="bi bi-speedometer2 me-1"></i>Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../logout.php">
              <i class="bi bi-power me-1"></i>Logout
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="py-5">
    <div class="container" data-reveal>
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h1 class="mb-1"><i class="bi bi-people-fill me-2"></i>Manage Users</h1>
          <p class="text-muted mb-0">View and manage all registered members</p>
        </div>
        <div class="d-flex gap-2">
          <?php if ($pendingCount > 0): ?>
            <span class="badge bg-warning fs-6 py-2 px-3">
              <i class="bi bi-hourglass-split me-1"></i><?= $pendingCount ?> Pending
            </span>
          <?php endif; ?>
          <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
          </a>
        </div>
      </div>

      <!-- Success Message -->
      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_GET['success']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- Users Table Card -->
      <div class="card shadow">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">
            <i class="bi bi-table me-2"></i>User Accounts
            <?php if ($result && $result->num_rows > 0): ?>
              <span class="badge bg-light text-primary float-end"><?= $result->num_rows ?></span>
            <?php endif; ?>
          </h5>
        </div>
        <div class="card-body p-0">
          <?php if ($result && $result->num_rows > 0): ?>
            <!-- Responsive Table Wrapper -->
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th><i class="bi bi-hash"></i> ID</th>
                    <th><i class="bi bi-person"></i> Username</th>
                    <th><i class="bi bi-envelope"></i> Email</th>
                    <th><i class="bi bi-speedometer"></i> Status</th>
                    <th><i class="bi bi-speedometer"></i> BMI</th>
                    <th><i class="bi bi-calendar"></i> Joined</th>
                    <th class="text-end"><i class="bi bi-gear"></i> Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td class="fw-bold"><?= htmlspecialchars($row['id']) ?></td>
                      <td>
                        <span class="badge bg-info"><?= htmlspecialchars($row['username']) ?></span>
                      </td>
                      <td>
                        <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                      </td>
                      <td>
                        <?php
                        $status = $row['status'] ?? 'pending';
                        $statusColors = [
                          'pending' => 'warning',
                          'approved' => 'success',
                          'rejected' => 'danger',
                          'deleted' => 'secondary'
                        ];
                        $statusLabels = [
                          'pending' => 'Pending',
                          'approved' => 'Approved',
                          'rejected' => 'Rejected',
                          'deleted' => 'Deleted'
                        ];
                        $color = $statusColors[$status] ?? 'secondary';
                        $label = $statusLabels[$status] ?? $status;
                        ?>
                        <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                      </td>
                      <td>
                        <?php
                        $bmi = floatval($row['bmi'] ?? 0);
                        if ($bmi === 0.0) {
                          echo '<span class="text-muted">—</span>';
                        } else {
                          $bmiColor = $bmi < 18.5 ? 'warning' : ($bmi < 25 ? 'success' : ($bmi < 30 ? 'warning' : 'danger'));
                          echo '<span class="badge bg-' . $bmiColor . '">' . number_format($bmi, 1) . '</span>';
                        }
                        ?>
                      </td>
                      <td>
                        <small><?= date('M d, Y', strtotime($row['created_at'])) ?></small>
                      </td>
                      <td class="text-end">
                        <?php if ($status === 'pending'): ?>
                          <form method="POST" class="d-inline">
                            <input type="hidden" name="approve_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-success">
                              <i class="bi bi-check-circle"></i> Approve
                            </button>
                          </form>
                          <form method="POST" class="d-inline">
                            <input type="hidden" name="reject_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-warning">
                              <i class="bi bi-x-circle"></i> Reject
                            </button>
                          </form>
                        <?php elseif ($status === 'approved'): ?>
                          <form method="POST" class="d-inline">
                            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                              <i class="bi bi-trash"></i> Delete
                            </button>
                          </form>
                        <?php elseif ($status === 'rejected'): ?>
                          <form method="POST" class="d-inline">
                            <input type="hidden" name="approve_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-success">
                              <i class="bi bi-arrow-repeat"></i> Re-approve
                            </button>
                          </form>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="alert alert-info m-0 mb-0">
              <i class="bi bi-info-circle me-2"></i>No users found.
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form method="POST">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="deleteUserLabel">
              <i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete the user <span id="deleteUserName" class="fw-bold text-danger"></span>?
            </p>
            <p class="small text-muted">⚠️ This action cannot be undone. All user data will be permanently removed.</p>
            <input type="hidden" name="delete_id" id="deleteUserId">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">
              <i class="bi bi-trash me-1"></i>Delete User
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/site.js"></script>
  <script>
    // Handle delete user button clicks
    document.querySelectorAll('.delete-user-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const userId = btn.getAttribute('data-user-id');
        const userName = btn.getAttribute('data-user-name');
        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteUserName').innerText = userName;
        const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
        modal.show();
      });
    });
  </script>
</body>

</html>