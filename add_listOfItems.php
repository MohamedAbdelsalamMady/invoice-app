<?php

session_start();

@include './server/connect.php';

if (isset($_POST['upload'])) {
    $list_name = isset($_POST['list_name']) ? trim($_POST['list_name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $quantity = isset($_POST['quantity']) ? trim($_POST['quantity']) : '';
    $price_per_unit = isset($_POST['price_per_unit']) ? trim($_POST['price_per_unit']) : '';
    $user_id = $_SESSION['user_id'];

    // Validate inputs
    if (empty($description)) {
        $errorMsg = 'Please input description';
    } elseif (!is_numeric($quantity) || $quantity <= 0) {
        $errorMsg = 'Please input a valid quantity';
    } elseif (!is_numeric($price_per_unit) || $price_per_unit <= 0) {
        $errorMsg = 'Please input a valid price per unit';
    } else {
        // Calculate total
        $total = $quantity * $price_per_unit;

        // Insert into invoice_items table
        if (!isset($errorMsg)) {
            $stmt = $conn->prepare("INSERT INTO invoice_items (id, list_name, description, quantity, price_per_unit, total, created_at, created_by) 
VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param('issdddi', $id, $list_name, $description, $quantity, $price_per_unit, $total, $created_by);

            if ($stmt->execute()) {
                $successMsg = 'New record added successfully';
                header('Location: add_listOfItems.php');
                exit;
            } else {
                $errorMsg = 'Error: ' . $stmt->error;
            }
            $stmt->close();
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
                            <h3 class="fw-bold mb-3">Add Item</h3>
                        </div>

                    </div>




                    <form action="" method="post" enctype="multipart/form-data">
                    
                    <div class="form-group">
        <label for="list_name">List Name</label>
        <input type="text" class="form-control" name="list_name" placeholder="List Name" />
    </div>

     <div class="form-group">
        <label for="description">Description</label>
        <input type="text" class="form-control" name="description" placeholder="Description" />
    </div>

    <div class="form-group">
        <label for="quantity">Quantity</label>
        <input type="number" class="form-control" name="quantity" placeholder="Quantity" />
    </div>

    <div class="form-group">
        <label for="price_per_unit">Price per Unit</label>
        <input type="number" step="0.01" class="form-control" name="price_per_unit" placeholder="Price per unit" />
    </div>

    <div class="form-group">
        <label for="total">Total</label>
        <input type="number" step="0.01" class="form-control" name="total" placeholder="Total" />
    </div>

    <button class="btn btn-black" type="submit" name="upload">Add Item</button>
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




                </div>
            </div>
        </div>

    </div>
    <?php
    @include('components/script-links.php');
    ?>
</body>

</html>