<?php
session_start();
require_once './server/connect.php'; 

$errorMessage = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_email = mysqli_real_escape_string($conn, $_POST['user_email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE user_email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $user_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['user_email'] = $user['user_email'];
            $_SESSION['user_role'] = $user['user_role'];

            header("Location: home.php");
            exit(); 
        } else {
            
            $errorMessage = 'Invalid email or password. Please try again.';
        }
    } else {
        
        $errorMessage = 'No user found with that email. Please register.';
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invocies System</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <section class="vh-100">
        <div class="container-fluid h-custom">
          <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-md-9 col-lg-6 col-xl-5">
              <img src="img/invoice-8777297_1920.png"
                class="img-fluid" alt="Sample image">
            </div>
            <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">

            <!-- Display error message -->
            <?php if (!empty($errorMessage)): ?>
              <div class="alert alert-danger" role="alert">
                  <?= htmlspecialchars($errorMessage) ?>
              </div>
              <?php endif; ?>


              <form method="post" enctype="multipart/form-data" action="index.php">
                <div class="d-flex flex-row align-items-center justify-content-center justify-content-lg-start">
                  <h2 class="mb-3">Sign In</h2>
                </div>
      
      
                <!-- Email input -->
                <div data-mdb-input-init class="form-outline mb-4">
                  <input type="email" id="form3Example3" id="user_email" name="user_email" class="form-control form-control-lg"
                    placeholder="Enter a valid email address" />
                  <label class="form-label" for="form3Example3">Email address</label>
                </div>
      
                <!-- Password input -->
                <div data-mdb-input-init class="form-outline mb-3">
                  <input type="password" id="form3Example4" id="password" name="password" class="form-control form-control-lg"
                    placeholder="Enter password" />
                  <label class="form-label" for="form3Example4">Password</label>
                </div>
      
                <div class="d-flex justify-content-between align-items-center">
                  <!-- Checkbox -->
                  <div class="form-check mb-0">
                    <input class="form-check-input me-2" type="checkbox" value="" id="form2Example3" />
                    <label class="form-check-label" for="form2Example3">
                      Remember me
                    </label>
                  </div>
                </div>
      
                <div class="text-center text-lg-start mt-4 pt-2">
                  <button type="submit" class="btn btn-primary btn-lg"
                    style="padding-left: 2.5rem; padding-right: 2.5rem;">Login</button>
                  <p class="small fw-bold mt-2 pt-1 mb-0">Don't have an account? <a href="new_account.php"
                      class="btn btn-danger">Register</a></p>
                </div>
      
              </form>


            </div>
          </div>
        </div>
      </section>

      <script src="js/bootstrap.bundle.js"></script>
      <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>