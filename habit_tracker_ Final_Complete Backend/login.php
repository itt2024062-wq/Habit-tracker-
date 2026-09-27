<?php
// login.php
// Authenticate users and start session

require_once 'config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email.';
    if ($password === '') $errors[] = 'Please enter your password.';

    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT id, password, name FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                // success
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['name'];
                $stmt->close();
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = 'Incorrect email or password.';
            }
        } else {
            $errors[] = 'Incorrect email or password.';
        }
        $stmt->close();
    }
}

$registered = isset($_GET['registered']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Habit Tracker</title>
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
              <i class="fa-solid fa-user fa-2x text-secondary"></i>
            </div>
            <h4 class="fw-bold">Sign In</h4>
          </div>

          <?php if ($registered): ?>
            <div class="alert alert-success">Registration successful. Please sign in.</div>
          <?php endif; ?>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <?php foreach ($errors as $err) echo '<div>' . e($err) . '</div>'; ?>
            </div>
          <?php endif; ?>

          <form action="login.php" method="POST">
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" placeholder="user@example.com" required value="<?php echo isset($email) ? e($email) : ''; ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-dark w-100 py-2 mb-3">Sign In</button>
            <div class="text-center">
              <a href="#" class="text-decoration-none small text-muted">Forgot password?</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

</body>
</html>