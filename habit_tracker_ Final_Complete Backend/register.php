<?php
// register.php
// Connects register form to MySQL and creates new users

require_once 'config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $terms = isset($_POST['terms']);

    if ($name === '') $errors[] = 'Name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if ($password === '' || strlen($password) < 6) $errors[] = 'Password is required and must be at least 6 characters.';
    if (!$terms) $errors[] = 'You must agree to the terms.';

    if (empty($errors)) {
        // Check if email already exists
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email already registered. Please login or use another email.';
            $stmt->close();
        } else {
            $stmt->close();
            // Insert new user
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $ins = $mysqli->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $ins->bind_param('sss', $name, $email, $hashed);
            if ($ins->execute()) {
                $ins->close();
                // Registration successful — redirect to login page
                header('Location: login.php?registered=1');
                exit;
            } else {
                $errors[] = 'Database error while creating account. Please try again later.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Habit Tracker</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.php"><i class="fa-solid fa-square-check text-primary me-2"></i>HT Habit Tracker</a>
    </div>
  </nav>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4">
          <div class="text-center mb-4">
            <div class="bg-light rounded-circle d-inline-block p-3 mb-2">
              <i class="fa-solid fa-user-plus fa-2x text-secondary"></i>
            </div>
            <h4 class="fw-bold">Register</h4>
          </div>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
            </div>
          <?php endif; ?>

          <form action="register.php" method="POST">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" name="name" class="form-control" placeholder="Your name" required value="<?php echo isset($name) ? e($name) : ''; ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" placeholder="user@example.com" required value="<?php echo isset($email) ? e($email) : ''; ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" id="terms" name="terms" required <?php echo isset($terms) && $terms ? 'checked' : ''; ?>>
              <label class="form-check-label small text-muted" for="terms">I agree to terms</label>
            </div>
            <button type="submit" class="btn btn-dark w-100 py-2">Register</button>
          </form>
          <div class="mt-3 text-center small text-muted">Already have an account? <a href="login.php">Sign in</a></div>
        </div>
      </div>
    </div>
  </div>

</body>
</html>