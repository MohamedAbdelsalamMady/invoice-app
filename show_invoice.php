<?php
session_start();

@include './server/connect.php';
require './pdf_file/fpdf.php';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
  $records_per_page = 4;
  $offset = ($page - 1) * $records_per_page;


$errorMsg = '';
$successMsg = '';

// Query to fetch invoices with search and pagination
$sql = "
    SELECT 
        invoices.invoice_id, 
        invoices.invoice_number, 
        clients.name AS client_name, 
        clients.address, 
        invoices.invoice_date, 
        invoices.due_date, 
        invoices.total_amount, 
        u.user_role AS created_by 
    FROM 
        invoices
    JOIN 
        clients ON invoices.client_id = clients.client_id
    JOIN 
        users u ON invoices.created_by = u.user_id  -- Join the users table
    WHERE 
        invoices.invoice_number LIKE '%$search%'
        OR clients.name LIKE '%$search%'
    ORDER BY 
        invoices.created_at DESC
    LIMIT 
        $offset, $records_per_page
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die('Error: ' . mysqli_error($conn));
}

// Query to count total records for pagination
$count_sql = "
    SELECT COUNT(*) as total
    FROM invoices
    JOIN clients ON invoices.client_id = clients.client_id
    WHERE invoices.invoice_number LIKE '%$search%'
    OR clients.name LIKE '%$search%'
";

$count_result = mysqli_query($conn, $count_sql);

if (!$count_result) {
    die('Error: ' . mysqli_error($conn));
}

$count_row = mysqli_fetch_assoc($count_result);
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $records_per_page);


// Handle invoice deletion
if (isset($_GET['delete'])) {
  $invoice_id = $_GET['delete'];

  // Prepare and execute the deletion query
  $stmt = $conn->prepare("DELETE FROM invoices WHERE invoice_id = ?");
  $stmt->bind_param("i", $invoice_id);

  if ($stmt->execute()) {
    $successMsg = 'Invoice deleted successfully';
    header('Location: show_invoice.php');
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

          <form method="GET" action="show_invoice.php">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Search Invoices..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <div class="input-group-append">
                            <button class="btn btn-outline-primary" type="submit">Search</button>
                        </div>
                    </div>
                </form>



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
                      <th scope="col">Created By</th>
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
                          <td><?= htmlspecialchars($row['created_by']); ?></td>
                          <td>
                          <?php if ($_SESSION['user_role'] == 'admin'): ?>
    <!-- Admin-specific UI components -->
    <a href="edit_invoice.php?id=<?= htmlspecialchars($row['invoice_id']); ?>" class="btn btn-warning btn-sm">Edit</a>
    <a href="show_invoice.php?delete=<?= htmlspecialchars($row['invoice_id']); ?>"
                              class="btn btn-danger btn-sm"
                              onclick="return confirm('Are you sure you want to delete this List?');">Delete</a>
<?php endif; ?>

                            <a href="read_invoice.php?delete=<?= htmlspecialchars($row['invoice_id']); ?>" class="btn btn-success btn-sm">Show</a>
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



          <!-- Pagination -->
          <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="show_invoice.php?page=<?= $page - 1; ?>&search=<?= htmlspecialchars($search); ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="show_invoice.php?page=<?= $i; ?>&search=<?= htmlspecialchars($search); ?>"><?= $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="show_invoice.php?page=<?= $page + 1; ?>&search=<?= htmlspecialchars($search); ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>


          <script>
        function searchInvoices() {
            const searchInput = document.getElementById('search').value;
            window.location.href = 'show_invoice.php?search=' + encodeURIComponent(searchInput);
        }
    </script>


        </div>
      </div>
    </div>

  </div>
  
  <?php
  @include('components/script-links.php');
  ?>
</body>

</html>