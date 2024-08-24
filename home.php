<?php
session_start();



if (!isset($_SESSION['user_role'])) {
    header("Location: index.php");
    exit();
}

$user_role = $_SESSION['user_role'];



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
              <h3 class="fw-bold mb-3">Dashboard</h3>
            </div>

          </div>

          <!-- The Content  -->

          <?php
          @include('components/tables-home.php');
          ?>




        </div>
      </div>
    </div>

  </div>
  <?php
  @include('components/script-links.php');
  ?>
</body>

</html>