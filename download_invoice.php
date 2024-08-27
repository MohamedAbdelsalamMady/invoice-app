<?php
require('pdf_file/fpdf.php');
@include './server/connect.php';

if (isset($_GET['invoice_id'])) {
    $invoice_id = $_GET['invoice_id'];

    // Fetch invoice details
    $query = "
        SELECT invoices.invoice_number, clients.name AS client_name, clients.address, invoices.invoice_date, invoices.due_date, invoices.total_amount
        FROM invoices
        JOIN clients ON invoices.client_id = clients.client_id
        WHERE invoices.invoice_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $invoice_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoice = $result->fetch_assoc();

    // Fetch invoice items
    $items_query = "
        SELECT description, quantity, price_per_unit, total
        FROM invoice_items
        WHERE id = ?";
    $items_stmt = $conn->prepare($items_query);
    $items_stmt->bind_param("i", $invoice_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();

    if ($invoice) {
        // Create PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Set Font
        $pdf->SetFont('Arial', 'B', 16);
        
        // Add Invoice Title
        $pdf->Cell(190, 10, 'Invoice', 0, 1, 'C');
        $pdf->Ln(10);
        
        // Invoice Information
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(100, 10, 'Invoice Number: ' . $invoice['invoice_number'], 0, 1);
        $pdf->Cell(100, 10, 'Client Name: ' . $invoice['client_name'], 0, 1);
        $pdf->Cell(100, 10, 'Client Address: ' . $invoice['address'], 0, 1);
        $pdf->Cell(100, 10, 'Invoice Date: ' . $invoice['invoice_date'], 0, 1);
        $pdf->Cell(100, 10, 'Due Date: ' . $invoice['due_date'], 0, 1);
        $pdf->Ln(10);

        // Invoice Items Table Header
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(80, 10, 'Description', 1);
        $pdf->Cell(30, 10, 'Quantity', 1);
        $pdf->Cell(40, 10, 'Price per Unit', 1);
        $pdf->Cell(40, 10, 'Total', 1);
        $pdf->Ln();

        // Invoice Items Data
        $pdf->SetFont('Arial', '', 12);
        while ($item = $items_result->fetch_assoc()) {
            $pdf->Cell(80, 10, $item['description'], 1);
            $pdf->Cell(30, 10, $item['quantity'], 1);
            $pdf->Cell(40, 10, '$' . number_format($item['price_per_unit'], 2), 1);
            $pdf->Cell(40, 10, '$' . number_format($item['total'], 2), 1);
            $pdf->Ln();
        }

        $pdf->Ln(10);
        $pdf->Cell(100, 10, 'Total Amount: $' . $invoice['total_amount'], 0, 1, 'R');
        
        $pdf->Output('D', 'invoice_' . $invoice['invoice_number'] . '.pdf');
    } else {
        echo "Invoice not found!";
    }

    $stmt->close();
    $items_stmt->close();
} else {
    echo "Invalid request!";
}

mysqli_close($conn);
?>