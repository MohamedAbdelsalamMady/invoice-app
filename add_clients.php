<?php
//Protecting Code In all admin pages
session_start();


@include './server/connect.php';

if (isset($_POST['upload'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
    $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    $contact_email = mysqli_real_escape_string($conn, $_POST['contact_email'] ?? '');
    $contact_phone = mysqli_real_escape_string($conn, $_POST['contact_phone'] ?? '');
    $created_by = $_SESSION['user_id'] ?? 0;
    

    if (empty($name)) {
        $errorMsg = 'Please input name';
    } elseif (empty($address)) {
        $errorMsg = 'Please input address';
    } elseif (empty($contact_email)) {
        $errorMsg = 'Please input email';
    } elseif (empty($contact_phone)) {
        $errorMsg = 'Please input phone';
    } 

    if (!isset($errorMsg)) {
        $sql = "INSERT INTO clients (name, address, contact_email, contact_phone, created_at, created_by) 
                VALUES ('$name', '$address', '$contact_email', '$contact_phone', NOW(), '$created_by')";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $successMsg = 'New record added successfully';
            header('Location: add_clients.php');
            exit;  // Ensure no further code is executed
        } else {
            $errorMsg = 'Error ' . mysqli_error($conn);
        }
    }
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invocies Management System</title>
  <?php
  @include('components/style-links.php');
  ?>
  
</head>

<body>
  <div class="wrapper">
    <?php
    @include('components/sidebar.php');
    ?>

    <div class="main-panel">

    <?php
    @include('components/header.php')
    ?>
      

      <div class="container">

        <div class="page-inner">

          <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
              <h3 class="fw-bold mb-3">Add Clinets</h3>
            </div>

          </div>

          <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
          <?php elseif (!empty($successMsg)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
          <?php endif; ?>


          <form action="" method="post" enctype="multipart/form-data">

          <div class="form-group">
                            <label for="product_name">Client Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="" />
                        </div>

                        <div class="form-group">
                            <label for="product_name">Address</label>
                            <input type="text" class="form-control" id="address" name="address" placeholder="" />
                        </div>

                        <div class="form-group">
                            <label for="product_name">Email</label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" placeholder="" />
                        </div>

                        <div class="form-group">
                            <label for="product_name">Phone</label>
                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" placeholder="" />
                        </div>

                        <button class="btn btn-black" type="upload" name="upload">
                            <span class="btn-label">
                            </span>
                           Add Client
                        </button>

          </form>






        </div>
      </div>
    </div>

  </div>
  <?php
  @include('components/script-links.php');
  ?>
</body>

</html>