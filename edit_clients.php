<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
  header("Location: index.php");
  exit();
}



@include './server/connect.php';

$client_id = $_GET['id'] ?? null;
$errorMsg = '';
$successMsg = '';

if ($client_id) {
    // Fetch client details
    $sql = "SELECT * FROM clients WHERE client_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $client = $result->fetch_assoc();

    if (!$client) {
        $errorMsg = "Client not found.";
    }

    // Handle form submission
    if (isset($_POST['update'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
        $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
        $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email'] ?? '');
        $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone'] ?? '');

        if (empty($name)) {
            $errorMsg = 'Please input name';
        } elseif (empty($address)) {
            $errorMsg = 'Please input address';
        } elseif (empty($contact_email)) {
            $errorMsg = 'Please input email';
        } elseif (empty($contact_phone)) {
            $errorMsg = 'Please input phone';
        }

        if (!$errorMsg) {
            // Update client details in the database
            $sql_update = "UPDATE clients SET name = ?, address = ?, contact_email = ?, contact_phone = ? WHERE client_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sssii", $name, $address, $contact_email, $contact_phone, $client_id);

            if ($stmt_update->execute()) {
                $successMsg = 'Client updated successfully';
                header('Location: show_clients.php');
                exit();
            } else {
                $errorMsg = 'Error: ' . mysqli_error($conn);
            }
        }
    }
} else {
    $errorMsg = 'Invalid client ID';
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice Management System</title>
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
              <h3 class="fw-bold mb-3">Edit Client</h3>
            </div>
          </div>

          <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
          <?php elseif (!empty($successMsg)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
          <?php endif; ?>

          <?php if (isset($client)): ?>
            <form action="" method="post" enctype="multipart/form-data">
              <div class="form-group">
                <label for="name">Client Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($client['name']) ?>" />
              </div>

              <div class="form-group">
                <label for="address">Address</label>
                <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($client['address']) ?>" />
              </div>

              <div class="form-group">
                <label for="contact_email">Email</label>
                <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?= htmlspecialchars($client['contact_email']) ?>" />
              </div>

              <div class="form-group">
                <label for="contact_phone">Phone</label>
                <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($client['contact_phone']) ?>" />
              </div>

              <button class="btn btn-primary" type="submit" name="update">
                <span class="btn-label"></span> Update Client
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php @include('components/script-links.php'); ?>
</body>
</html>
