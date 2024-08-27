<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
  header("Location: index.php");
  exit();
}

@include './server/connect.php';

$invoice_id = $_GET['id'] ?? null;

if ($invoice_id) {
    // Fetch invoice data
    $invoice_sql = "SELECT * FROM invoices WHERE invoice_id = ?";
    $stmt = $conn->prepare($invoice_sql);
    $stmt->bind_param('i', $invoice_id);
    $stmt->execute();
    $invoice_result = $stmt->get_result();
    $invoice = $invoice_result->fetch_assoc();

    // Fetch invoice items
    $item_sql = "SELECT * FROM invoice_items WHERE id = ?";
    $item_stmt = $conn->prepare($item_sql);
    $item_stmt->bind_param('i', $invoice_id);
    $item_stmt->execute();
    $item_result = $item_stmt->get_result();
    $items = $item_result->fetch_all(MYSQLI_ASSOC);

    if (!$invoice) {
        echo "Invoice not found.";
        exit();
    }
} else {
    echo "Invalid invoice ID.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture form data
    $client_id = $_POST['client_id'];
    $invoice_date = $_POST['invoice_date'];
    $due_date = $_POST['due_date'];
    $total_amount = $_POST['total_amount'];

    // Update invoice data
    $update_invoice_sql = "UPDATE invoices SET client_id = ?, invoice_date = ?, due_date = ?, total_amount = ?, updated_at = NOW() WHERE invoice_id = ?";
    $stmt = $conn->prepare($update_invoice_sql);
    $stmt->bind_param('issdi', $client_id, $invoice_date, $due_date, $total_amount, $invoice_id);

    if ($stmt->execute()) {
        // Delete existing items
        $delete_items_sql = "DELETE FROM invoice_items WHERE invoice_id = ?";
        $delete_stmt = $conn->prepare($delete_items_sql);
        $delete_stmt->bind_param('i', $invoice_id);
        $delete_stmt->execute();

        // Insert new items
        foreach ($_POST['description'] as $index => $description) {
            $quantity = $_POST['quantity'][$index];
            $price_per_unit = $_POST['price_per_unit'][$index];
            $total = $_POST['total'][$index];

            $item_sql = "INSERT INTO invoice_items (invoice_id, description, quantity, price_per_unit, total, created_at) 
                         VALUES (?, ?, ?, ?, ?, NOW())";

            $item_stmt = $conn->prepare($item_sql);
            $item_stmt->bind_param('isidd', $invoice_id, $description, $quantity, $price_per_unit, $total);
            $item_stmt->execute();
        }

        header('Location: show_invoices.php');
        exit();
    } else {
        echo "Error updating invoice: " . $stmt->error;
    }
}

$client_result = mysqli_query($conn, "SELECT * FROM clients");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Invoice</title>
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
              <h3 class="fw-bold mb-3">Edit Invoice</h3>
            </div>
          </div>

          <form method="post" action="" enctype="multipart/form-data">
            <div class="form-group">
              <label for="client_id">Client Name</label>
              <select class="form-control" id="client_id" name="client_id">
                <?php while ($client = mysqli_fetch_assoc($client_result)): ?>
                  <option value="<?php echo $client['client_id']; ?>" <?php if ($client['client_id'] == $invoice['client_id']) echo 'selected'; ?>>
                    <?php echo $client['name']; ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="form-group">
              <label for="invoice_date">Invoice Date:</label>
              <input type="date" name="invoice_date" id="invoice_date" class="form-control" value="<?= htmlspecialchars($invoice['invoice_date']) ?>" required>
            </div>

            <div class="form-group">
              <label for="due_date">Due Date:</label>
              <input type="date" name="due_date" id="due_date" class="form-control" value="<?= htmlspecialchars($invoice['due_date']) ?>" required>
            </div>

            <div class="form-group">
              <label for="items">Items:</label>
              <div id="item-list">
                <?php foreach ($items as $index => $item): ?>
                  <div class="item d-flex align-items-center">
                    <input type="text" name="description[]" value="<?= htmlspecialchars($item['description']) ?>" placeholder="Description" class="form-control" required>
                    <input type="number" name="quantity[]" value="<?= htmlspecialchars($item['quantity']) ?>" placeholder="Quantity" class="form-control" required>
                    <input type="number" step="0.01" name="price_per_unit[]" value="<?= htmlspecialchars($item['price_per_unit']) ?>" placeholder="Price per unit" class="form-control" required>
                    <input type="number" step="0.01" name="total[]" value="<?= htmlspecialchars($item['total']) ?>" placeholder="Total" class="form-control" readonly>
                    <button type="button" class="btn btn-danger btn-sm ml-2" onclick="removeItem(this)">X</button>
                  </div>
                <?php endforeach; ?>
              </div>
              <button type="button" class="btn btn-primary" onclick="addItem()">Add Another Item</button>
            </div>

            <div class="form-group">
              <label for="total_amount">Total Amount:</label>
              <input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control" value="<?= htmlspecialchars($invoice['total_amount']) ?>" readonly>
            </div>

            <button type="submit" name="submit" class="btn btn-primary">Submit</button>
          </form>

          <script>
            function addItem() {
              const itemDiv = document.createElement('div');
              itemDiv.className = 'item d-flex align-items-center';
              itemDiv.innerHTML = `
                <input type="text" name="description[]" placeholder="Description" class="form-control" required>
                <input type="number" name="quantity[]" placeholder="Quantity" class="form-control" required>
                <input type="number" step="0.01" name="price_per_unit[]" placeholder="Price per unit" class="form-control" required>
                <input type="number" step="0.01" name="total[]" placeholder="Total" class="form-control" readonly>
                <button type="button" class="btn btn-danger btn-sm ml-2" onclick="removeItem(this)">X</button>
              `;
              document.getElementById('item-list').appendChild(itemDiv);
            }

            function removeItem(button) {
              button.parentElement.remove();
              calculateTotals();
            }

            document.addEventListener('input', function(event) {
              if (event.target.matches('[name="quantity[]"], [name="price_per_unit[]"]')) {
                calculateTotals();
              }
            });

            function calculateTotals() {
              let totalAmount = 0;
              document.querySelectorAll('.item').forEach(function(item) {
                const quantity = parseFloat(item.querySelector('[name="quantity[]"]').value) || 0;
                const pricePerUnit = parseFloat(item.querySelector('[name="price_per_unit[]"]').value) || 0;
                const total = quantity * pricePerUnit;
                item.querySelector('[name="total[]"]').value = total.toFixed(2);
                totalAmount += total;
              });
              document.getElementById('total_amount').value = totalAmount.toFixed(2);
            }

            calculateTotals();
          </script>

        </div>
      </div>
    </div>
  </div>

  <?php @include('components/script-links.php'); ?>
</body>

</html>
