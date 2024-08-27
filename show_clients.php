<?php
session_start();

@include './server/connect.php';

$errorMsg = '';
$successMsg = '';

// Fetch clients from the database
$query = "SELECT c.client_id, c.name, c.address, c.contact_email, c.contact_phone, u.user_role AS created_by 
    FROM Clients c 
    JOIN users u ON c.created_by = u.user_id
          ";
$result = mysqli_query($conn, $query);

// Handle client deletion
if (isset($_GET['delete'])) {
  $client_id = $_GET['delete'];

  // Prepare and execute the deletion query
  $stmt = $conn->prepare("DELETE FROM clients WHERE client_id = ?");
  $stmt->bind_param("i", $client_id);

  if ($stmt->execute()) {
    $successMsg = 'Client deleted successfully';
    header('Location: show_clients.php');
    exit();
  } else {
    $errorMsg = 'Failed to delete client: ' . $stmt->error;
  }

  $stmt->close();
}

mysqli_close($conn);
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

          <div class="row">
            <div class="col">
              <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
                <div>
                  <h3 class="fw-bold mb-3">Show Clients</h3>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="d-flex align-items-right align-items-md-center flex-column flex-md-row pt-2 pb-4">
                <div>
                  <a href="add_clients.php" class="btn btn-primary">Add Client</a>
                </div>
              </div>
            </div>
          </div>


          <div class="show-tables">
            <div class="row">
              <div class="col-md-12">
                <table class="table table-head-bg-danger mt-4">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Client Name</th>
                      <th scope="col">Address</th>
                      <th scope="col">Email</th>
                      <th scope="col">Phone</th>
                      <th scope="col">Created By</th>
                      <th scope="col">Action</th>
                    </tr>
                  </thead>
                  <tbody id="invoiceTable">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                      <?php $count = 1; ?>
                      <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                          <td><?= $count++; ?></td>
                          <td><?= htmlspecialchars($row['name']); ?></td>
                          <td><?= htmlspecialchars($row['address']); ?></td>
                          <td><?= htmlspecialchars($row['contact_email']); ?></td>
                          <td><?= htmlspecialchars($row['contact_phone']); ?></td>
                          <td><?= htmlspecialchars($row['created_by']); ?></td> 
                          <td><?$userRole?></td>'
                          <?php if ($_SESSION['user_role'] == 'admin'): ?>
                            <a href="edit_clients.php?id=<?= htmlspecialchars($row['client_id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="show_clients.php?delete=<?= htmlspecialchars($row['client_id']); ?>" class="btn btn-danger btn-sm">Delete</a>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endwhile; ?>
                    <?php else: ?>
                      <tr>
                        <th colspan="6" class="text-center">No clients found</th>
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

  </div>
  <?php
  @include('components/script-links.php');
  ?>
</body>

</html>