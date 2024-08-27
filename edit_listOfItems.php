<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}

@include './server/connect.php';

$id = $_GET['id'] ?? null;
$errorMsg = '';
$successMsg = '';

if ($id) {
    // Fetch item details
    $sql = "SELECT * FROM invoice_items WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    if (!$item) {
        $errorMsg = "Item not found.";
    }

    // Handle form submission
    if (isset($_POST['update'])) {
        $list_name = mysqli_real_escape_string($conn, $_POST['list_name'] ?? '');
        $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity'] ?? '');
        $price_per_unit = mysqli_real_escape_string($conn, $_POST['price_per_unit'] ?? '');
        $total = mysqli_real_escape_string($conn, $_POST['total'] ?? '');

        // Validate inputs
        if (empty($list_name)) {
            $errorMsg = 'Please input list name';
        } elseif (empty($description)) {
            $errorMsg = 'Please input description';
        } elseif (!is_numeric($quantity) || $quantity <= 0) {
            $errorMsg = 'Please input a valid quantity';
        } elseif (!is_numeric($price_per_unit) || $price_per_unit <= 0) {
            $errorMsg = 'Please input a valid price per unit';
        }

        if (!$errorMsg) {
            // Calculate total if not manually entered
            if (empty($total)) {
                $total = $quantity * $price_per_unit;
            }

            // Update item details in the database
            $sql_update = "UPDATE invoice_items SET list_name = ?, description = ?, quantity = ?, price_per_unit = ?, total = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssiddi", $list_name, $description, $quantity, $price_per_unit, $total, $id);

            if ($stmt_update->execute()) {
                $successMsg = 'Item updated successfully';
                header('Location: show_listOfItems.php');
                exit();
            } else {
                $errorMsg = 'Error: ' . $stmt_update->error;
            }
        }
    }
} else {
    $errorMsg = 'Invalid Item ID';
}

$conn->close();
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
                            <h3 class="fw-bold mb-3">Edit Item</h3>
                        </div>
                    </div>

                    <?php if (!empty($errorMsg)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
                    <?php elseif (!empty($successMsg)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
                    <?php endif; ?>

                    <?php if (isset($item)): ?>
                        <form action="" method="post">
                            <div class="form-group">
                                <label for="list_name">List Name</label>
                                <input type="text" class="form-control" name="list_name" value="<?= htmlspecialchars($item['list_name']) ?>" />
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <input type="text" class="form-control" name="description" value="<?= htmlspecialchars($item['description']) ?>" />
                            </div>

                            <div class="form-group">
                                <label for="quantity">Quantity</label>
                                <input type="number" class="form-control" name="quantity" value="<?= htmlspecialchars($item['quantity']) ?>" />
                            </div>

                            <div class="form-group">
                                <label for="price_per_unit">Price per Unit</label>
                                <input type="number" step="0.01" class="form-control" name="price_per_unit" value="<?= htmlspecialchars($item['price_per_unit']) ?>" />
                            </div>

                            <div class="form-group">
                                <label for="total">Total</label>
                                <input type="number" step="0.01" class="form-control" name="total" value="<?= htmlspecialchars($item['total']) ?>" readonly />
                            </div>

                            <button class="btn btn-primary" type="submit" name="update">Update Item</button>
                        </form>

                        <script>
    document.addEventListener('input', function(event) {
        if (event.target.matches('[name="quantity"], [name="price_per_unit"]')) {
            calculateTotal();
        }
    });

    function calculateTotal() {
        const quantity = parseFloat(document.querySelector('[name="quantity"]').value) || 0;
        const pricePerUnit = parseFloat(document.querySelector('[name="price_per_unit"]').value) || 0;
        const total = quantity * pricePerUnit;
        document.querySelector('[name="total"]').value = total.toFixed(2);
    }
</script>


                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php @include('components/script-links.php'); ?>
</body>
</html>