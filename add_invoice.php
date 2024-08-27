<?php
session_start();

@include './server/connect.php';

// Fetch existing items from invoice_items table
$item_result = mysqli_query($conn, "SELECT * FROM invoice_items");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'];
    $invoice_date = $_POST['invoice_date'];
    $due_date = $_POST['due_date'];
    $created_by = $_SESSION['user_id'];

    // Generate the invoice number with date (e.g., 20240827-INV001)
    $datePart = date('Ymd');
    $uniqueNumber = uniqid();
    $invoice_number = $datePart . '-INV' . substr($uniqueNumber, -3);

    // Insert into invoices table with initial total_amount as 0
    $invoice_sql = "INSERT INTO invoices (client_id, invoice_number, invoice_date, due_date, total_amount, created_at, created_by) 
                    VALUES (?, ?, ?, ?, 0, NOW(), ?)";

    if ($stmt = $conn->prepare($invoice_sql)) {
        $stmt->bind_param('isssi', $client_id, $invoice_number, $invoice_date, $due_date, $created_by);

        if ($stmt->execute()) {
            $invoice_id = $stmt->insert_id; // Get the inserted invoice ID
            $totalAmount = 0;

            // Loop through the selected items and add them to the new invoice
            foreach ($_POST['selected_items'] as $item_id) {
                // Fetch details of the selected item
                $item_sql = "SELECT * FROM invoice_items WHERE id = ?";
                $item_stmt = $conn->prepare($item_sql);
                $item_stmt->bind_param('i', $item_id);
                $item_stmt->execute();
                $item_result = $item_stmt->get_result();
                $item = $item_result->fetch_assoc();
            
                // Add the item to the new invoice
                $item_insert_sql = "INSERT INTO invoice_items (list_name, description, quantity, price_per_unit, total, created_at)
                                    VALUES (?, ?, ?, ?, ?, NOW())";
                $item_insert_stmt = $conn->prepare($item_insert_sql);
                $item_insert_stmt->bind_param(
                    'ssidd',
                    $item['list_name'],
                    $item['description'],
                    $item['quantity'],
                    $item['price_per_unit'],
                    $item['total']
                );
                $item_insert_stmt->execute();
            
                // Add the item total to the overall total
                $totalAmount += $item['total'];
            }

            // Update total_amount in invoices table
            $update_sql = "UPDATE invoices SET total_amount = ? WHERE invoice_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param('di', $totalAmount, $invoice_id);
            $update_stmt->execute();

            // Redirect to the invoice page
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

// Fetch unique items based on description
$item_result = mysqli_query($conn, "
    SELECT * FROM invoice_items
    GROUP BY description
");


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
    <label for="client_search">Search Client Name:</label>
    <input type="text" class="form-control" id="client_search" name="client_search" placeholder="Type to search..." autocomplete="off">
    <div id="client_list" class="list-group"></div>
</div>
<input type="hidden" name="client_id" id="client_id"> <!-- To store the selected client ID -->

                        <div class="form-group">
                            <label for="invoice_date">Invoice Date:</label>
                            <input type="date" name="invoice_date" id="invoice_date" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="due_date">Due Date:</label>
                            <input type="date" name="due_date" id="due_date" class="form-control" required>
                        </div>

                        <!-- Select items from existing -->
                        <div class="form-group">
    <label for="items">Select Items:</label>
    <select name="selected_items[]" id="items" class="form-control" multiple>
        <?php while ($item = mysqli_fetch_assoc($item_result)) : ?>
            <option value="<?php echo htmlspecialchars($item['id']); ?>"
                <?php if (isset($_POST['selected_items']) && in_array($item['id'], $_POST['selected_items'])) echo 'selected'; ?>>
                <?php echo htmlspecialchars($item['list_name']) . " - " . htmlspecialchars($item['description']); ?>
            </option>
        <?php endwhile; ?>
    </select>
    <small class="form-text text-muted">Hold down the Ctrl (Windows) or Command (Mac) key to select multiple items.</small>
</div>
                        

<div class="form-group">
    <label for="total_amount">Total Price:</label>
    <input type="text" name="total_amount" id="total_amount" class="form-control" readonly>
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
            <button type="button" class="btn btn-danger btn-sm ml-2" onclick="removeItem(this)">X</button>
        `;
        document.getElementById('item-list').appendChild(itemDiv);
    }

    function removeItem(button) {
        button.parentElement.remove();
    }

    function calculateTotal() {
        const selectedItems = document.getElementById('items').selectedOptions;
        let total = 0;

        // Loop through selected items and sum up their prices
        for (let i = 0; i < selectedItems.length; i++) {
            const price = parseFloat(selectedItems[i].getAttribute('data-price'));
            total += price;
        }

        // Update the total amount field
        document.getElementById('total_amount').value = total.toFixed(2);
    }
</script>
<script>
document.getElementById('client_search').addEventListener('input', function() {
    const searchTerm = this.value;

    // Only search when the user types 2 or more characters
    if (searchTerm.length >= 2) {
        fetch('search_clients.php?query=' + searchTerm)
            .then(response => response.json())
            .then(data => {
                const clientList = document.getElementById('client_list');
                clientList.innerHTML = ''; // Clear the previous search results

                if (data.length > 0) {
                    data.forEach(client => {
                        const listItem = document.createElement('a');
                        listItem.className = 'list-group-item list-group-item-action';
                        listItem.href = '#';
                        listItem.textContent = client.name;
                        listItem.dataset.clientId = client.client_id;

                        listItem.addEventListener('click', function(e) {
                            e.preventDefault();
                            document.getElementById('client_search').value = client.name; // Show selected client name
                            document.getElementById('client_id').value = client.client_id; // Store the client ID
                            clientList.innerHTML = ''; // Hide the search results
                        });

                        clientList.appendChild(listItem);
                    });
                } else {
                    clientList.innerHTML = '<p class="list-group-item">No clients found</p>';
                }
            });
    } else {
        document.getElementById('client_list').innerHTML = ''; // Clear the list if the search term is less than 2 characters
    }
});
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