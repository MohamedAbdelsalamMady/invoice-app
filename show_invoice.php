<?php
session_start();

@include './server/connect.php';
require './pdf_file/fpdf.php';

$errorMsg = '';
$successMsg = '';

// Fetch invoices with client information
$query = "
    SELECT invoices.invoice_id, invoices.invoice_number, clients.name AS client_name, clients.address, invoices.invoice_date, invoices.due_date, invoices.total_amount
    FROM invoices
    JOIN clients ON invoices.client_id = clients.client_id
    ORDER BY invoices.created_at DESC
";
$result = mysqli_query($conn, $query);

// Handle invoice deletion
if (isset($_GET['delete'])) {
    $invoice_id = $_GET['delete'];

    // Prepare and execute the deletion query
    $stmt = $conn->prepare("DELETE FROM invoices WHERE invoice_id = ?");
    $stmt->bind_param("i", $invoice_id);

    if ($stmt->execute()) {
        $successMsg = 'Invoice deleted successfully';
        header('Location: show_invoices.php');
        exit();
    } else {
        $errorMsg = 'Failed to delete invoice: ' . $stmt->error;
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
              <h3 class="fw-bold mb-3">Show Invoices</h3>
            </div>
          </div>
          </div>
          <div class="col">
            <div class="d-flex align-items-right align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
              <a href="add_invoice.php" class="btn btn-primary">Add Invoice</a>
            </div>
          </div>
          </div>
        </div>

        <div class="show-tables">
          <div class="row">
              <div class="col-md-12">
              <table class="table table-head-bg-info mt-4">
                                    <thead>
                                        <tr>
                                            <th scope="col">Invoice Number</th>
                                            <th scope="col">Client Name</th>
                                            <th scope="col">Client Address</th>
                                            <th scope="col">Invoice Date</th>
                                            <th scope="col">Due Date</th>
                                            <th scope="col">Total Amount</th>
                                            <th scope="col">Action</th>
                                            <th scope="col">Download</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($result) > 0): ?>
                                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($row['invoice_number']); ?></td>
                                                    <td><?= htmlspecialchars($row['client_name']); ?></td>
                                                    <td><?= htmlspecialchars($row['address']); ?></td>
                                                    <td><?= htmlspecialchars($row['invoice_date']); ?></td>
                                                    <td><?= htmlspecialchars($row['due_date']); ?></td>
                                                    <td><?= htmlspecialchars($row['total_amount']); ?></td>
                                                    <td>
                                                        <a href="edit_invoice.php?id=<?= htmlspecialchars($row['invoice_id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                                        <a href="show_invoice.php?delete=<?= htmlspecialchars($row['invoice_id']); ?>" class="btn btn-danger btn-sm">Delete</a>
                                                    </td>
                                                    <td>
                    <a href="download_invoice.php?invoice_id=<?= htmlspecialchars($row['invoice_id']); ?>" class="btn btn-secondary btn-sm">Download PDF</a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <th colspan="7" class="text-center">No invoices found</th>
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