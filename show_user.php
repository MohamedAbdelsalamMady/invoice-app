<?php


session_start();

// Uncomment this for security
/* if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
} */

require_once './server/connect.php';

$sql = "SELECT * FROM users";
$result = mysqli_query($conn, $sql);

if (!$result) {
    $errorMsg = 'Error: ' . mysqli_error($conn);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user_role and user_id are set before proceeding
    if (isset($_POST['user_role']) && isset($_POST['user_id'])) {
        $userId = $_POST['user_id'];
        $userRole = $_POST['user_role'];

        $sql = "UPDATE users SET user_role = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $userRole, $userId);

        if ($stmt->execute()) {
            echo 'User role updated successfully';
        } else {
            echo 'Error: ' . $stmt->error;
        }

        $stmt->close();
    } else {
        echo 'Error: User role or ID is missing.';
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoices Management System</title>
  <?php @include('components/style-links.php'); ?>
</head>

<body>
  <div class="wrapper">
    <?php @include('components/sidebar.php'); ?>

    <div class="main-panel">
      <?php @include('components/header.php'); ?>

      <div class="container">
        <div class="page-inner">
          <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
              <h3 class="fw-bold mb-3">Show Users</h3>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <table class="table table-head-bg-primary mt-4">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">User Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">User Role</th>
                    <th scope="col">Password</th>
                    <th scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (mysqli_num_rows($result) > 0) : ?>
                    <?php $counter = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                      <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                        <td>
                          <form method="POST">
                            <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                            <select name="user_role" onchange="this.form.submit()">
                              <option value="user" <?php if ($row['user_role'] == 'user') echo 'selected'; ?>>User</option>
                              <option value="admin" <?php if ($row['user_role'] == 'admin') echo 'selected'; ?>>Admin</option>
                            </select>
                          </form>
                        </td>
                        <td style="font-size: 10px;"><?php echo htmlspecialchars($row['password']); ?></td> <!-- Consider replacing this with '******' for security -->
                        <td>
                          <!-- Remove Update button if not needed -->
                        </td>
                      </tr>
                    <?php endwhile; ?>
                  <?php else : ?>
                    <tr>
                      <th colspan="6" style="text-align: center;">No Users found</th>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php @include('components/script-links.php'); ?>
</body>

</html>