<?php
require './server/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
	$user_email = mysqli_real_escape_string($conn, $_POST['user_email']);
	$password = $_POST['password'];
	$confirmpassword = $_POST['confirm_password'];
    $user_role = mysqli_real_escape_string($conn, $_POST['user_role']); // Get the user role

    if (strpos($user_email, '@gmail.com') === false) {
      $message = 'Please use a Gmail address for registration.';
  } else {
      $uppercase = preg_match('@[A-Z]@', $password);
      $lowercase = preg_match('@[a-z]@', $password);
      $number = preg_match('@[0-9]@', $password);
      $specialChars = preg_match('@[^\w]@', $password);

    if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
        $message = 'Password must be at least 8 characters long and include at least one upper case letter, one number, and one special character.';
    } else {
        // Check if passwords match
        if ($password == $confirmpassword) {
            // Hash the password using bcrypt
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            // Insert user into the database with user role
            $stmt = $conn->prepare("INSERT INTO users (user_name, user_email, password, user_role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $user_name, $user_email, $passwordHash, $user_role);

            if ($stmt->execute()) {
                header("Location: home.php");
                exit();
            } else {
                $message = "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $message = "Passwords do not match.";
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
    <title>Invoices System - Register</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <section class="vh-100">
        <div class="container-fluid h-custom">
          <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-md-9 col-lg-6 col-xl-5">
              <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-login-form/draw2.webp"
                class="img-fluid" alt="Sample image">
            </div>
            <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">

              <form method="post" action="new_account.php" enctype="multipart/form-data">
                <div class="d-flex flex-row align-items-center justify-content-center justify-content-lg-start">
                  <h2 class="mb-3">Register - Create New Account</h2>
                </div>

                <?php if (!empty($message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($message) ?>
                </div>
                <?php endif; ?>

                <div data-mdb-input-init class="form-outline mb-4">
                  <input type="text" id="user_name" name="user_name" class="form-control form-control-lg"
                    placeholder="Enter your name" required />
                  <label class="form-label" for="user_name">Full Name</label>
                </div>

                <div data-mdb-input-init class="form-outline mb-4">
                  <input type="email" id="user_email" name="user_email" class="form-control form-control-lg"
                    placeholder="Enter a valid email address" required />
                  <label class="form-label" for="user_email">Email address</label>
                </div>
      
                <div data-mdb-input-init class="form-outline mb-3">
                  <input type="password" id="password" name="password" class="form-control form-control-lg"
                    placeholder="Enter password" required />
                  <label class="form-label" for="password">Password</label>
                </div>

                <div data-mdb-input-init class="form-outline mb-3">
                  <input type="password" id="confirm_password" name="confirm_password" class="form-control form-control-lg"
                    placeholder="Confirm password" required />
                  <label class="form-label" for="confirm_password">Confirm Password</label>
                </div>

                <div class="form-group">
                    <input type="hidden" class="form-control" name="user_role" id="user_role" value="user" required>
                </div>
      
                <div class="d-flex justify-content-between align-items-center">

                  <div class="form-check mb-0">
                    <input class="form-check-input me-2" type="checkbox" value="" id="form2Example5" required />
                    <label class="form-check-label" for="form2Example5">
                      I agree to the <a href="#!" class="text-body">terms and conditions</a>
                    </label>
                  </div>
                </div>
      
                <div class="text-center text-lg-start mt-4 mb-4 pt-2">
                  <button  type="submit" class="btn btn-primary btn-lg"
                    style="padding-left: 2.5rem; padding-right: 2.5rem;">Register</button>
                  <p class="small fw-bold mt-2 pt-1 mb-0">Already have an account? <a href="index.php"
                      class="btn btn-danger">Sign In</a></p>
                </div>
      
              </form>
            </div>
          </div>
        </div>
      </section>

      <script>
    document.querySelector('form').addEventListener('submit', function (e) {
        const emailField = document.getElementById('user_email');
        if (!emailField.value.endsWith('@gmail.com')) {
            alert('Please use a Gmail address for registration.');
            e.preventDefault(); // Prevent form submission
        }
    });
</script>
      <script src="js/bootstrap.bundle.js"></script>
      <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
