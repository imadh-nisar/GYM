<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: index.php");
  exit();
}

include("../includes/db.php");

// Handle Add / Delete Admin form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Delete admin
  if (isset($_POST['delete_admin_id'])) {
    $deleteId = (int) $_POST['delete_admin_id'];
    $stmt = $conn->prepare("DELETE FROM admins WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php");
    exit();
  }

  // Add admin
  if (isset($_POST['add_admin'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO admins (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
      $msg = "Admin added successfully!";
    } else {
      $msg = "Error: " . $stmt->error;
    }

    $stmt->close();
  }

  // Create announcement
  if (isset($_POST['create_announcement'])) {
    $title = $_POST['announcement_title'];
    $content = $_POST['announcement_content'];
    $isActive = isset($_POST['announcement_active']) ? 1 : 0;
    $adminId = $_SESSION['admin_id'];

    $stmt = $conn->prepare("INSERT INTO announcements (title, content, is_active, created_by, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssii", $title, $content, $isActive, $adminId);

    if ($stmt->execute()) {
      $msg = "Announcement posted successfully!";
    } else {
      $msg = "Error: " . $stmt->error;
    }

    $stmt->close();
  }

  // Toggle announcement status
  if (isset($_POST['toggle_announcement'])) {
    $annId = (int) $_POST['announcement_id'];
    $stmt = $conn->prepare("UPDATE announcements SET is_active = NOT is_active WHERE id = ?");
    $stmt->bind_param("i", $annId);
    $stmt->execute();
    $stmt->close();
  }

  // Delete announcement
  if (isset($_POST['delete_announcement'])) {
    $annId = (int) $_POST['announcement_id'];
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $annId);
    $stmt->execute();
    $stmt->close();
  }
}

// Load last 50 appointments
$appointments = [];
$stmt = $conn->prepare("SELECT a.*, u.username AS user_name FROM appointments a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 50");
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Load pending users count
$pendingResult = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'pending'");
$pendingCount = $pendingResult ? $pendingResult->fetch_assoc()['count'] : 0;

// Load total approved users count
$approvedResult = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'approved'");
$approvedCount = $approvedResult ? $approvedResult->fetch_assoc()['count'] : 0;

// Load admins
$admins = [];
$stmt = $conn->prepare("SELECT id, username, email, created_at FROM admins ORDER BY created_at DESC LIMIT 50");
$stmt->execute();
$result = $stmt->get_result();
$admins = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard - GYMgeekS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../assets/css/style.css">
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
            <span class="nav-link text-light">Admin Panel</span>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../index.php">
              <i class="bi bi-box-arrow-right me-1"></i>Back to Site
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


    <!-- Header -->
    <div class="mb-5">
      <h1 class="mb-1"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</h1>
      <p class="text-muted mb-0">Manage users, workouts, meals, and admin accounts</p>
    </div>

    <!-- Feedback Message -->
    <?php if (!empty($msg)): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Management Cards -->
    <div class="row g-3 mb-5">
      <div class="col-md-6 col-lg-3">
        <a href="users.php" class="card shadow text-decoration-none text-dark h-100 hover-shadow">
          <div class="card-body text-center">
            <div class="fs-1 text-primary mb-3"><i class="bi bi-people-fill"></i></div>
            <h5 class="card-title">Users</h5>
            <p class="card-text text-muted">Manage member accounts</p>
            <?php if ($pendingCount > 0): ?>
              <span class="badge bg-warning"><?= $pendingCount ?> Pending</span>
            <?php endif; ?>
          </div>
        </a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="workouts.php" class="card shadow text-decoration-none text-dark h-100 hover-shadow">
          <div class="card-body text-center">
            <div class="fs-1 text-success mb-3"><i class="bi bi-dumbbell"></i></div>
            <h5 class="card-title">Workouts</h5>
            <p class="card-text text-muted">Create & manage workout templates</p>
          </div>
        </a>
      </div>
      <div class="col-md-6 col-lg-3">
        <a href="meals.php" class="card shadow text-decoration-none text-dark h-100 hover-shadow">
          <div class="card-body text-center">
            <div class="fs-1 text-warning mb-3"><i class="bi bi-apple"></i></div>
            <h5 class="card-title">Meals</h5>
            <p class="card-text text-muted">Manage meal plans & nutrition</p>
          </div>
        </a>
      </div>
      <div class="col-md-6 col-lg-3">
        <button type="button" class="card shadow text-decoration-none text-dark h-100 w-100 border-0 hover-shadow"
          data-bs-toggle="modal" data-bs-target="#addAdminModal" style="background: none; cursor: pointer;">
          <div class="card-body text-center">
            <div class="fs-1 text-info mb-3"><i class="bi bi-shield-plus"></i></div>
            <h5 class="card-title">Add Admin</h5>
            <p class="card-text text-muted">Create new admin account</p>
          </div>
        </button>
      </div>
      <div class="col-md-6 col-lg-3">
        <button type="button" class="card shadow text-decoration-none text-dark h-100 w-100 border-0 hover-shadow"
          data-bs-toggle="modal" data-bs-target="#createAnnouncementModal" style="background: none; cursor: pointer;">
          <div class="card-body text-center">
            <div class="fs-1 text-warning mb-3"><i class="bi bi-megaphone-fill"></i></div>
            <h5 class="card-title">Announcements</h5>
            <p class="card-text text-muted">Create & manage announcements</p>
          </div>
        </button>
      </div>
    </div>

    <!-- Admins Table -->
    <div class="card shadow mb-5">
      <div class="card-header bg-secondary text-white">
        <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Admin Accounts</h5>
      </div>
      <div class="card-body">
        <?php if (empty($admins)): ?>
          <p class="text-muted mb-0">No admins available yet.</p>
        <?php else: ?>
          <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($admins as $admin): ?>
              <div class="col">
                <div class="card h-100">
                  <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-1"><?= htmlspecialchars($admin['username']) ?></h5>
                    <p class="card-text mb-1"><strong>Email:</strong> <?= htmlspecialchars($admin['email']) ?></p>
                    <p class="card-text text-muted mb-3"><small>Added:
                        <?= htmlspecialchars($admin['created_at']) ?></small>
                    </p>
                    <button type="button" class="btn btn-sm btn-outline-danger w-100 delete-admin-btn"
                      data-admin-id="<?= $admin['id'] ?>"
                      data-admin-name="<?= htmlspecialchars($admin['username'], ENT_QUOTES) ?>">
                      Delete
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Recent Appointments -->
    <div class="card shadow mb-4">
      <div class="card-header bg-secondary text-white">📅 Recent Appointments</div>
      <div class="card-body p-0">
        <?php if (!empty($appointments)): ?>
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead class="table-dark">
                <tr>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Booked by</th>
                  <th>Goal</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($appointments as $a): ?>
                  <tr>
                    <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                    <td><?= htmlspecialchars($a['appointment_time']) ?></td>
                    <td><?= htmlspecialchars($a['name']) ?></td>
                    <td><?= htmlspecialchars($a['email']) ?></td>
                    <td><?= htmlspecialchars($a['user_name'] ?? 'Guest') ?></td>
                    <td><?= nl2br(htmlspecialchars($a['goal'])) ?></td>
                    <td><?= htmlspecialchars($a['created_at']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="p-3">
            <p class="mb-0 text-muted">No appointments have been booked yet.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <a href="../logout.php" class="btn btn-danger mt-3">Logout</a>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form method="POST">
            <div class="modal-header">
              <h5 class="modal-title">Create New Admin</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="add_admin" value="1">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success">Add Admin</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
</body>

<!-- Delete Admin Confirmation Modal -->
<div class="modal fade" id="deleteAdminModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Remove Admin</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to remove <span id="adminNamePlaceholder" class="fw-bold"></span> as an admin?</p>
          <input type="hidden" name="delete_admin_id" id="deleteAdminId">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Remove</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.querySelectorAll('.delete-admin-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const adminId = btn.getAttribute('data-admin-id');
      const adminName = btn.getAttribute('data-admin-name');
      document.getElementById('deleteAdminId').value = adminId;
      document.getElementById('adminNamePlaceholder').innerText = adminName;
      const modal = new bootstrap.Modal(document.getElementById('deleteAdminModal'));
      modal.show();
    });
  });
</script>

</html>

<!-- Create Announcement Modal -->
<div class="modal fade" id="createAnnouncementModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title"><i class="bi bi-megaphone-fill me-2"></i>Create Announcement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="create_announcement" value="1">
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-tag me-1"></i>Title</label>
            <input type="text" name="announcement_title" class="form-control" placeholder="Enter announcement title"
              required>
          </div>
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-text-paragraph me-1"></i>Content</label>
            <textarea name="announcement_content" class="form-control" rows="5"
              placeholder="Enter announcement details..." required></textarea>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="announcement_active" id="announcementActive"
                checked>
              <label class="form-check-label" for="announcementActive">
                Publish immediately
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning"><i class="bi bi-send me-1"></i>Post Announcement</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Manage Announcements Modal -->
<div class="modal fade" id="manageAnnouncementsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="bi bi-megaphone-fill me-2"></i>Manage Announcements</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php
        $allAnnouncements = [];
        $stmt = $conn->prepare("SELECT a.*, ad.username as admin_name FROM announcements a LEFT JOIN admins ad ON a.created_by = ad.id ORDER BY a.created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $allAnnouncements = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        ?>
        <?php if (empty($allAnnouncements)): ?>
          <p class="text-muted">No announcements yet.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Content</th>
                  <th>Status</th>
                  <th>Created By</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($allAnnouncements as $ann): ?>
                  <tr>
                    <td><?= htmlspecialchars($ann['title']) ?></td>
                    <td><?= htmlspecialchars(mb_substr($ann['content'], 0, 50)) ?>...</td>
                    <td>
                      <?php if ($ann['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($ann['admin_name'] ?? 'Unknown') ?></td>
                    <td><?= date('M d, Y', strtotime($ann['created_at'])) ?></td>
                    <td>
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="toggle_announcement" value="1">
                        <input type="hidden" name="announcement_id" value="<?= $ann['id'] ?>">
                        <button type="submit"
                          class="btn btn-sm btn-<?= $ann['is_active'] ? 'outline-warning' : 'outline-success' ?>">
                          <?= $ann['is_active'] ? '<i class="bi bi-pause-fill"></i> Deactivate' : '<i class="bi bi-play-fill"></i> Activate' ?>
                        </button>
                      </form>
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="delete_announcement" value="1">
                        <input type="hidden" name="announcement_id" value="<?= $ann['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"
                          onclick="return confirm('Delete this announcement?');">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>