<?php
// my-habits.php
// List habits for current user, allow deletion
require_once 'config.php';
require_login();

$user = current_user($mysqli);
$user_id = (int)$_SESSION['user_id'];

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_habit'])) {
    $hid = (int)$_POST['habit_id'];
    $stmt = $mysqli->prepare("DELETE FROM habits WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $hid, $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: my-habits.php');
    exit;
}

// Fetch habits
$stmt = $mysqli->prepare("SELECT id, habit_name, category, frequency, type, created_at FROM habits WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$habits = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Habits - Habit Tracker</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    .sidebar { min-height: calc(100vh - 56px); background: #ffffff; border-right: 1px solid #e9ecef; }
    .sidebar .nav-link { color: #495057; font-weight: 500; padding: 12px 20px; }
    .sidebar .nav-link.active { background: #eef2ff; color: #4361ee; border-radius: 6px; }
  </style>
</head>
<body class="bg-light">

  <nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="dashboard.php"><i class="fa-solid fa-square-check text-primary me-2"></i>HT Habit Tracker</a>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar Navigation -->
      <div class="col-md-3 col-lg-2 sidebar p-3">
        <ul class="nav flex-column">
          <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fa-solid fa-house me-2"></i>Home</a></li>
          <li class="nav-item"><a class="nav-link active" href="my-habits.php"><i class="fa-solid fa-list-check me-2"></i>My Habits</a></li>
          <li class="nav-item"><a class="nav-link" href="add-habit.php"><i class="fa-solid fa-circle-plus me-2"></i>Add Habit</a></li>
          <li class="nav-item"><a class="nav-link" href="progress.php"><i class="fa-solid fa-chart-pie me-2"></i>Progress</a></li>
          <li class="nav-item mt-4"><a class="nav-link text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
        </ul>
      </div>

      <!-- Main Content Area -->
      <div class="col-md-9 col-lg-10 p-4">
        <h3 class="fw-bold mb-3">Manage Habits</h3>

        <!-- Search and Filter Controls -->
        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <input type="text" id="searchInput" class="form-control" placeholder="Search Habits...">
          </div>
          <div class="col-md-4">
            <select id="filterType" class="form-select">
              <option value="All">All Types</option>
              <option value="Good">Good Habits</option>
              <option value="Bad">Bad Habits</option>
            </select>
          </div>
          <div class="col-md-2">
            <a href="add-habit.php" class="btn btn-primary w-100"><i class="fa-solid fa-plus me-1"></i> Add Habit</a>
          </div>
        </div>

        <!-- Habits Table -->
        <div class="card border-0 shadow-sm">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Habit</th>
                  <th>Type</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody id="habitTableBody">
                <?php if (empty($habits)): ?>
                  <tr><td colspan="3" class="text-center text-muted py-3">No habits found.</td></tr>
                <?php else: ?>
                  <?php foreach ($habits as $h): ?>
                    <tr data-name="<?php echo e($h['habit_name']); ?>" data-type="<?php echo e($h['type']); ?>">
                      <td class="fw-medium"><?php echo e($h['habit_name']); ?></td>
                      <td><span class="badge <?php echo ($h['type'] === 'Good') ? 'bg-success' : 'bg-danger'; ?>"><?php echo e($h['type']); ?></span></td>
                      <td>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this habit?');" style="display:inline;">
                          <input type="hidden" name="habit_id" value="<?php echo (int)$h['id']; ?>">
                          <button type="submit" name="delete_habit" class="btn btn-sm btn-light"><i class="fa-solid fa-trash text-danger"></i></button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script>
    // Client-side filtering to preserve original UX
    function loadHabitsClient() {
      const searchQuery = document.getElementById('searchInput').value.toLowerCase();
      const filterType = document.getElementById('filterType').value;
      const rows = document.querySelectorAll('#habitTableBody tr');
      rows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const type = row.getAttribute('data-type') || '';
        let show = true;
        if (searchQuery && !name.toLowerCase().includes(searchQuery)) show = false;
        if (filterType !== 'All' && type !== filterType) show = false;
        row.style.display = show ? '' : 'none';
      });
    }

    document.getElementById('searchInput').addEventListener('input', loadHabitsClient);
    document.getElementById('filterType').addEventListener('change', loadHabitsClient);
  </script>

</body>
</html>