<?php
session_start();

if (!isset($_SESSION['user_role'])) {
    header("Location: index.php");
    exit();
}

require_once 'server/connect.php'; // Include the database connection file

$errorMessage = '';
$successMessage = '';

$user_email = $_SESSION['user_email'];
$user_name = $_SESSION['user_name'];
$hashed_password = '';

// Fetch user details from the database
$query = "SELECT * FROM users WHERE user_email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $hashed_password = $user['password'];
} else {
    $errorMessage = 'User not found.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if the new password and confirmation match
    if ($new_password !== $confirm_password) {
        $errorMessage = 'New password and confirmation do not match.';
    } else {
        // Verify the old password
        if (password_verify($old_password, $user['password'])) {
            // Hash the new password
            $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $update_query = "UPDATE users SET password = ? WHERE user_email = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param('ss', $new_password_hashed, $user_email);

            if ($update_stmt->execute()) {
                $successMessage = 'Password updated successfully.';
                $hashed_password = $new_password_hashed; // Update the displayed hashed password
            } else {
                $errorMessage = 'Failed to update password.';
            }
        } else {
            $errorMessage = 'Old password is incorrect.';
        }
    }
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
             <!-- Display user email and hashed password -->
        <div class="alert alert-info">
            <strong>Email:</strong> <?= htmlspecialchars($user_email) ?><br>
            <strong>User Name:</strong> <?= htmlspecialchars($user_name) ?>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="profile.php">
            <div class="form-group">
                <label for="old_password">Old Password</label>
                <input type="password" class="form-control" id="old_password" name="old_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit"  class="btn btn-primary mt-4">Update Password</button>
        </form>


</div>
          </div>
        </div>
      </section>

      <script src="js/bootstrap.bundle.js"></script>
      <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>