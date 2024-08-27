<?php
@include './server/connect.php';

if (isset($_GET['query'])) {
    $search_term = $_GET['query'];
    
    // Prepare the SQL query to search clients by name (use LIKE for partial matching)
    $sql = "SELECT client_id, name FROM clients WHERE name LIKE ?";
    $stmt = $conn->prepare($sql);
    $search_term = "%{$search_term}%";
    $stmt->bind_param('s', $search_term);
    $stmt->execute();
    $result = $stmt->get_result();

    $clients = [];

    while ($row = $result->fetch_assoc()) {
        $clients[] = $row; // Collect matching clients
    }

    // Return the results as JSON
    echo json_encode($clients);
}
?>