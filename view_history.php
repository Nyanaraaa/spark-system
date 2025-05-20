<?php
require 'database.php';


$sql = "SELECT employee_id, supplies, quantity, transaction_date FROM supply_usage_history ORDER BY transaction_date DESC";
$result = $conn->query($sql); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supply Usage History</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
</head>
<body>
    <div class="container">
        <h1 class="text-center">Supply Usage History</h1>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Employee ID</th> 
                    <th>Supply Name</th>
                    <th>Quantity Requested</th>
                    <th>Request Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>
                            <td>' . htmlspecialchars($row["employee_id"]) . '</td>
                            <td>' . htmlspecialchars($row["supplies"]) . '</td>
                            <td>' . htmlspecialchars($row["quantity"]) . '</td>
                            <td>' . htmlspecialchars($row["transaction_date"]) . '</td>
                        </tr>';
                    }
                } else {
                    
                    echo '<tr><td colspan="4">No Record Found</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php

$conn->close();
?>
