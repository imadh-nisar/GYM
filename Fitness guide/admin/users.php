<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: index.php");
  exit();
}

include("../includes/db.php");

// Handle delete request inline
if (isset($_POST['delete_id'])) {
  $user_id = intval($_POST['delete_id']);
  $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->close();
}

// Fetch users
$result = $conn->query("SELECT id, username, email, bmi, created_at FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>

<head>
  <title>Manage Users</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Shared Admin Theme -->
  <link href="../assets/css/admin.css" rel="stylesheet">
</head>

<body>
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0 text-center">👥 Manage Users</h2>
      <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <div class="card p-4">
      <?php if ($result && $result->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Email</th>
              <th>BMI</th>
              <th>Created At</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['bmi']) ?></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                  <form method="POST" style="display:inline;"
                    onsubmit="return confirm('Are you sure you want to delete this user?');">
                    <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p class="text-muted">No users found.</p>
      <?php endif; ?>
    </div>
  </div>
</body>

</html>