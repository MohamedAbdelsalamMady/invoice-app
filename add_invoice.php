<?php
session_start();

@include './server/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Capture form data
  $client_id = $_POST['client_id'];
  $invoice_date = $_POST['invoice_date'];
  $due_date = $_POST['due_date'];
  $total_amount = $_POST['total_amount'];

  
  $invoice_number = uniqid('INV-');

  $invoice_sql = "INSERT INTO invoices (client_id, invoice_number, invoice_date, due_date, total_amount, created_at) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
  
  if ($stmt = $conn->prepare($invoice_sql)) {
      $stmt->bind_param('isssd', $client_id, $invoice_number, $invoice_date, $due_date, $total_amount);
      
      if ($stmt->execute()) {
          $invoice_id = $stmt->insert_id; 

          
          foreach ($_POST['description'] as $index => $description) {
              $quantity = $_POST['quantity'][$index];
              $price_per_unit = $_POST['price_per_unit'][$index];
              $total = $_POST['total'][$index];

              $item_sql = "INSERT INTO invoice_items (invoice_id, description, quantity, price_per_unit, total, created_at) 
                           VALUES (?, ?, ?, ?, ?, NOW())";
              
              if ($item_stmt = $conn->prepare($item_sql)) {
                  $item_stmt->bind_param('isidd', $invoice_id, $description, $quantity, $price_per_unit, $total);
                  $item_stmt->execute();
              } else {
                  echo "Error preparing item statement: " . $conn->error;
              }
          }

          
          header('Location: add_invoice.php'); 
          exit();
      } else {
          echo "Error inserting invoice: " . $stmt->error;
      }
  } else {
      echo "Error preparing invoice statement: " . $conn->error;
  }
}

$client_result = mysqli_query($conn, "SELECT * FROM clients");
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
              <h3 class="fw-bold mb-3">Add Invoice</h3>
            </div>

          </div>

          <form method="post" action="" enctype="multipart/form-data">
          <div class="form-group">
        <label for="client_id">Client Name</label>
        <select class="form-control" id="client_id" name="client_id">
            <?php while ($client = mysqli_fetch_assoc($client_result)): ?>
                <option value="<?php echo $client['client_id']; ?>"><?php echo $client['name']; ?></option>
            <?php endwhile; ?>
        </select>
    </div>


    <div class="form-group">
        <label for="invoice_date">Invoice Date:</label>
        <input type="date" name="invoice_date" id="invoice_date" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="due_date">Due Date:</label>
        <input type="date" name="due_date" id="due_date" class="form-control" required>
    </div>

    <div class="form-group">
    <label for="items">Items:</label>
    <div id="item-list">
        <div class="item d-flex align-items-center">
            <input type="text" name="description[]" placeholder="Description" class="form-control" required>
            <input type="number" name="quantity[]" placeholder="Quantity" class="form-control" required>
            <input type="number" step="0.01" name="price_per_unit[]" placeholder="Price per unit" class="form-control" required>
            <input type="number" step="0.01" name="total[]" placeholder="Total" class="form-control" readonly>
            <button type="button" class="btn btn-danger btn-sm ml-2" onclick="removeItem(this)">X</button>
        </div>
    </div>
    <button type="button" class="btn btn-primary" onclick="addItem()">Add Another Item</button>
</div>

    <div class="form-group">
        <label for="total_amount">Total Amount:</label>
        <input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control" readonly>
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