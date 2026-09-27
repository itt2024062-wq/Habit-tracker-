<?php
// add-habit.php
// Add a new habit for the logged-in user
require_once 'config.php';
require_login();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $habitName = trim($_POST['habitName'] ?? '');
    $habitType = $_POST['habitType'] ?? 'Good';
    $habitCategory = $_POST['habitCategory'] ?? '';
    $habitFrequency = $_POST['habitFrequency'] ?? 'Daily';

    if ($habitName === '') $errors[] = 'Habit name is required.';
    if ($habitCategory === '') $errors[] = 'Please select a category.';

    if (empty($errors)) {
        $user_id = (int)$_SESSION['user_id'];
        $stmt = $mysqli->prepare("INSERT INTO habits (user_id, habit_name, category, frequency, type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('issss', $user_id, $habitName, $habitCategory, $habitFrequency, $habitType);
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: my-habits.php');
            exit;
        } else {
            $errors[] = 'Database error while saving habit.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Habit - Habit Tracker</title>
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
      <div class="col-md-3 col-lg-2 sidebar p-3">
        <ul class="nav flex-column">
          <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fa-solid fa-house me-2"></i>Home</a></li>
          <li class="nav-item"><a class="nav-link" href="my-habits.php"><i class="fa-solid fa-list-check me-2"></i>My Habits</a></li>
          <li class="nav-item"><a class="nav-link active" href="add-habit.php"><i class="fa-solid fa-circle-plus me-2"></i>Add Habit</a></li>
          <li class="nav-item"><a class="nav-link" href="progress.php"><i class="fa-solid fa-chart-pie me-2"></i>Progress</a></li>
          <li class="nav-item mt-4"><a class="nav-link text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
        </ul>
      </div>

      <div class="col-md-9 col-lg-10 p-4">
        <div class="row justify-content-center">
          <div class="col-md-8">
            <div class="card border-0 shadow-sm p-4">
              <h3 class="fw-bold mb-4">Add New Habit</h3>

              <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                  <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
                </div>
              <?php endif; ?>

              <form id="addHabitForm" method="POST" action="add-habit.php">
  <div class="mb-3">
    <label class="form-label">Habit Name:</label>
    <input type="text" name="habitName" id="habitName" class="form-control" placeholder="Enter Habit Name" required value="<?php echo isset($habitName) ? e($habitName) : ''; ?>">
  </div>
  <div class="mb-3">
    <label class="form-label d-block">Habit Type:</label>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="habitType" id="typeGood" value="Good" <?php echo (!isset($habitType) || $habitType === 'Good') ? 'checked' : ''; ?>>
      <label class="form-check-label text-success fw-bold" for="typeGood">Good Habit</label>
    </div>
    <div class="form-check form-check-inline">
      <input class="form-check-input" type="radio" name="habitType" id="typeBad" value="Bad" <?php echo (isset($habitType) && $habitType === 'Bad') ? 'checked' : ''; ?>>
      <label class="form-check-label text-danger fw-bold" for="typeBad">Bad Habit</label>
    </div>
  </div>
  <div class="mb-3">
    <label class="form-label">Category:</label>
    <select id="habitCategory" name="habitCategory" class="form-select" required>
      <option value="" disabled <?php echo !isset($habitCategory) ? 'selected' : ''; ?>>Select Category</option>
      <option value="Health" <?php echo (isset($habitCategory) && $habitCategory === 'Health') ? 'selected' : ''; ?>>Health</option>
      <option value="Productivity" <?php echo (isset($habitCategory) && $habitCategory === 'Productivity') ? 'selected' : ''; ?>>Productivity</option>
      <option value="Fitness" <?php echo (isset($habitCategory) && $habitCategory === 'Fitness') ? 'selected' : ''; ?>>Fitness</option>
    </select>
  </div>
  <div class="mb-4">
    <label class="form-label">Frequency:</label>
    <select id="habitFrequency" name="habitFrequency" class="form-select">
      <option value="Daily" <?php echo (!isset($habitFrequency) || $habitFrequency === 'Daily') ? 'selected' : ''; ?>>Daily</option>
      <option value="Weekly" <?php echo (isset($habitFrequency) && $habitFrequency === 'Weekly') ? 'selected' : ''; ?>>Weekly</option>
    </select>
  </div>
  <button type="submit" class="btn btn-primary w-100 py-2">Save Habit</button>
</form>

            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

</body>
</html>