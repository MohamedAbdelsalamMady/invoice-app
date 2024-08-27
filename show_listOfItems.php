<?php
session_start();

@include './server/connect.php';

$errorMsg = '';
$successMsg = '';

// Fetch items from the database
$query = "
    SELECT 
        MIN(ii.id) as id, 
        ii.list_name, 
        ii.description, 
        SUM(ii.quantity) as total_quantity, 
        ii.price_per_unit, 
        SUM(ii.total) as total, 
        u.user_role AS created_by  
    FROM 
        invoice_items ii
    JOIN 
        users u ON ii.created_by = u.user_id  
    GROUP BY 
        ii.list_name, ii.description, ii.price_per_unit, u.user_name 
    ORDER BY 
        ii.created_at DESC
";
$result = mysqli_query($conn, $query);

// Handle item deletion
if (isset($_POST['delete'])) {
  $invoice_item_id = $_POST['delete_id'];

  $stmt = $conn->prepare("DELETE FROM invoice_items WHERE id = ?");
  $stmt->bind_param("i", $invoice_item_id);

  if ($stmt->execute()) {
    echo '';
  } else {
    echo '';
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
                  <h3 class="fw-bold mb-3">Show List Of Items</h3>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="d-flex align-items-right align-items-md-center flex-column flex-md-row pt-2 pb-4">
                <div>
                  <a href="add_listOfItems.php" class="btn btn-primary">Add Item</a>
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
                      <th scope="col">List Name</th>
                      <th scope="col">Description</th>
                      <th scope="col">Quantity</th>
                      <th scope="col">Price Per Unit</th>
                      <th scope="col">Total</th>
                      <th scope="col">Created By</th>
                      <th scope="col">Action</th>
                    </tr>
                  </thead>
                  <tbody>
  <?php if (mysqli_num_rows($result) > 0): ?>
    <?php $count = 1; ?>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
      <tr>
        <td><?= $count++; ?></td>
        <td><?= htmlspecialchars($row['list_name'] ?? ''); ?></td>
        <td><?= htmlspecialchars($row['description'] ?? ''); ?></td>
        <td><?= htmlspecialchars($row['total_quantity'] ?? 0); ?></td>
        <td><?= htmlspecialchars($row['price_per_unit'] ?? 0); ?></td>
        <td><?= htmlspecialchars($row['total'] ?? 0); ?></td>
        <td><?= htmlspecialchars($row['created_by']); ?></td>
        <td>
        <?php if ($_SESSION['user_role'] == 'admin'): ?>
          <a href="edit_listOfItems.php?id=<?= htmlspecialchars($row['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
          <a href="show_listOfItems.php?delete=<?= htmlspecialchars($row['id']); ?>" class="btn btn-danger btn-sm"
             onclick="return confirm('Are you sure you want to delete this List?');">Delete</a>
             <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
    <tr>
      <th colspan="6" class="text-center">No Items found</th>
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