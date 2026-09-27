<?php
// progress.php
// Show habit progress, chart data and allow marking completion
require_once 'config.php';
require_login();

$user_id = (int)$_SESSION['user_id'];

// Handle marking a habit completed for today
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_complete'])) {
    $hid = (int)$_POST['habit_id'];
    $date = date('Y-m-d');
    // Prevent duplicate
    $chk = $mysqli->prepare("SELECT id FROM habit_progress WHERE habit_id = ? AND user_id = ? AND completed_date = ? LIMIT 1");
    $chk->bind_param('iis', $hid, $user_id, $date);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows === 0) {
        $chk->close();
        $ins = $mysqli->prepare("INSERT INTO habit_progress (habit_id, user_id, completed_date, status) VALUES (?, ?, ?, 'completed')");
        $ins->bind_param('iis', $hid, $user_id, $date);
        $ins->execute();
        $ins->close();
    } else {
        $chk->close();
    }
    header('Location: progress.php');
    exit;
}

// Fetch user habits
$stmt = $mysqli->prepare("SELECT id, habit_name, type FROM habits WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
$habits = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$totalHabits = count($habits);

// Dates for last 7 days
$dates = [];
for ($i = 6; $i >= 0; $i--) {
    $dates[] = date('Y-m-d', strtotime("-$i days"));
}
$start = $dates[0];
$end = $dates[count($dates)-1];

// Fetch completion counts per day for last 7 days
$inStmt = $mysqli->prepare("SELECT completed_date, COUNT(*) as cnt FROM habit_progress WHERE user_id = ? AND completed_date BETWEEN ? AND ? GROUP BY completed_date");
$inStmt->bind_param('iss', $user_id, $start, $end);
$inStmt->execute();
$rr = $inStmt->get_result();
$counts = [];
while ($row = $rr->fetch_assoc()) {
    $counts[$row['completed_date']] = (int)$row['cnt'];
}
$inStmt->close();

// Prepare data array for chart
$chartData = [];
$totalCompletions = 0;
foreach ($dates as $d) {
    $c = $counts[$d] ?? 0;
    $chartData[] = $c;
    $totalCompletions += $c;
}

// Weekly completion rate: completions / (totalHabits * 7)
$possible = max(1, $totalHabits * 7);
$weeklyRate = round(($totalCompletions / $possible) * 100);

// Current streak: count consecutive days ending today where at least one completion exists
$streak = 0;
for ($i = 0; $i < 30; $i++) { // limit lookback to 30 days
    $d = date('Y-m-d', strtotime("-$i days"));
    $q = $mysqli->prepare("SELECT COUNT(*) as c FROM habit_progress WHERE user_id = ? AND completed_date = ?");
    $q->bind_param('is', $user_id, $d);
    $q->execute();
    $r = $q->get_result()->fetch_assoc();
    $q->close();
    if ($r['c'] > 0) {
        $streak++;
    } else {
        break;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Progress - Habit Tracker</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <!-- Chart.js Library for rendering Line Chart -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
          <li class="nav-item"><a class="nav-link" href="my-habits.php"><i class="fa-solid fa-list-check me-2"></i>My Habits</a></li>
          <li class="nav-item"><a class="nav-link" href="add-habit.php"><i class="fa-solid fa-circle-plus me-2"></i>Add Habit</a></li>
          <li class="nav-item"><a class="nav-link active" href="progress.php"><i class="fa-solid fa-chart-pie me-2"></i>Progress</a></li>
          <li class="nav-item mt-4"><a class="nav-link text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
        </ul>
      </div>

      <!-- Main Content -->
      <div class="col-md-9 col-lg-10 p-4">
        <h3 class="fw-bold mb-4">Habit Progress</h3>

        <!-- Summary Cards Row -->
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3">
              <span class="text-muted small">Total Active Habits</span>
              <h3 class="fw-bold my-1" id="totalHabits"><?php echo e($totalHabits); ?></h3>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3">
              <span class="text-muted small">Weekly Completion Rate</span>
              <h3 class="fw-bold text-success my-1"><?php echo e($weeklyRate); ?>%</h3>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3">
              <span class="text-muted small">Current Streak</span>
              <h3 class="fw-bold text-primary my-1"><?php echo e($streak); ?> Days 🔥</h3>
            </div>
          </div>
        </div>

        <!-- Line Chart Card -->
        <div class="card border-0 shadow-sm p-4 mb-4">
          <h5 class="fw-bold mb-3"><i class="fa-solid fa-chart-line text-primary me-2"></i>Weekly Performance</h5>
          <div style="max-height: 400px; position: relative;">
            <canvas id="progressChart"></canvas>
          </div>
        </div>

        <div class="card border-0 shadow-sm p-4">
          <h5 class="fw-bold mb-3">Mark Completion</h5>
          <?php if (empty($habits)): ?>
            <div class="text-muted">No habits found. <a href="add-habit.php">Add a habit</a>.</div>
          <?php else: ?>
            <ul class="list-group">
              <?php foreach ($habits as $h): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <strong><?php echo e($h['habit_name']); ?></strong>
                    <div class="small text-muted"><?php echo e($h['type']); ?></div>
                  </div>
                  <form method="POST">
                    <input type="hidden" name="habit_id" value="<?php echo (int)$h['id']; ?>">
                    <button type="submit" name="mark_complete" class="btn btn-sm btn-success">Mark Completed Today</button>
                  </form>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>

  <script>
    const labels = <?php echo json_encode(array_map(function($d){ return date('D', strtotime($d)); }, $dates)); ?>;
    const data = <?php echo json_encode($chartData); ?>;

    document.addEventListener('DOMContentLoaded', () => {
      const ctx = document.getElementById('progressChart').getContext('2d');
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Habits Completed',
            data: data,
            borderColor: '#198754',
            backgroundColor: 'rgba(25, 135, 84, 0.1)',
            borderWidth: 3,
            tension: 0.3,
            fill: true
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
      });
    });
  </script>

</body>
</html>