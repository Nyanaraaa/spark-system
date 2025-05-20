<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPARK - Supply Request</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="housekeeping_request.css?v=<?php echo filemtime('housekeeping_request.css'); ?>">
</head>

<body>
    <?php include 'housekeeping_navbar.php'; ?>
    
    <div class="main p-9">
        <div class="container">
            <h1 class="mb-4">
                <i class="lni lni-reply me-2" style="color: var(--gold);"></i>
                Supply Request
            </h1>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="lni lni-search me-2"></i>
                                Search Supplies
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="input-group mb-3">
                                <span class="input-group-text">
                                    <i class="lni lni-search"></i>
                                </span>
                                <input 
                                    type="text" 
                                    id="search-input" 
                                    class="form-control" 
                                    placeholder="Search by name, brand or classification" 
                                    onkeyup="filterSupplies()">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <select id="category-filter" class="form-select">
                                        <option value="">All Brands</option>
                                        <?php
                                        require 'database.php';
                                        $categoryQuery = "SELECT DISTINCT brand FROM supplies";
                                        $categoryResult = $conn->query($categoryQuery);
                                        while ($categoryRow = $categoryResult->fetch_assoc()) {
                                            echo '<option value="' . htmlspecialchars($categoryRow['brand']) . '">' . htmlspecialchars($categoryRow['brand']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <select id="classification-filter" class="form-select">
                                        <option value="">All Classifications</option>
                                        <?php
                                        $classificationQuery = "SELECT DISTINCT classification FROM supplies";
                                        $classificationResult = $conn->query($classificationQuery);
                                        while ($classificationRow = $classificationResult->fetch_assoc()) {
                                            echo '<option value="' . htmlspecialchars($classificationRow['classification']) . '">' . htmlspecialchars($classificationRow['classification']) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['alert_type']; ?>" id="alert-message">
                    <i class="lni lni-<?= $_SESSION['alert_type'] === 'success' ? 'checkmark-circle' : 'warning'; ?> me-2"></i>
                    <?= htmlspecialchars($_SESSION['message']); ?>
                </div>
                <script>
                    setTimeout(function() {
                        const alertMessage = document.getElementById('alert-message');
                        if (alertMessage) {
                            alertMessage.style.display = 'none';
                        }
                    }, 5000);
                </script>
                <?php unset($_SESSION['message']);
                unset($_SESSION['alert_type']); ?>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="lni lni-package me-2"></i>
                                Available Supplies
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="request-table" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="25%">Supplies</th>
                                            <th width="15%">Brand</th>
                                            <th width="15%">Classification</th>
                                            <th width="15%">Stocks</th>
                                            <th width="20%">Last Updated</th>
                                            <th width="10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="supplies-table-body">
                                    <?php
                                    require 'database.php';

                                    // Assuming the employee ID is stored in the session when the user is logged in
                                    $employeeId = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : ''; // Leave as string

                                    // If employee_id is not set or invalid, handle the error
                                    if (empty($employeeId)) {
                                        die('Invalid employee ID.');
                                    }

                                    $sql = "SELECT supplies_id, supplies, classification, brand, stocks, last_updated FROM supplies";
                                    $result = $conn->query($sql);

                                    if ($result->num_rows > 0) {
                                        
                                        while ($row = $result->fetch_assoc()) {
                                            $formattedDate = date("F j, Y g:ia", strtotime($row["last_updated"]));

                                            $suppliesId = $row["supplies_id"];
                                            // Make sure employee_id is properly enclosed in quotes to be treated as a string
                                            $limitQuery = "SELECT SUM(quantity) AS total_requested 
                                                        FROM supplies_usage_history 
                                                        WHERE supplies_id = $suppliesId 
                                                        AND employee_id = '$employeeId'  -- Keep employee_id as string
                                                        AND DATE(transaction_date) = CURDATE()"; 
                                            $limitResult = $conn->query($limitQuery);
                                            $totalRequested = ($limitResult && $limitResult->num_rows > 0) ? $limitResult->fetch_assoc()['total_requested'] : 0;
                                            $limitReached = ($totalRequested >= 2); 

                                            // Determine stock level class
                                            $stockClass = '';
                                            $stockBadgeClass = '';
                                            $stockIcon = '';
                                            $quantity = $row["stocks"];

                                            if ($quantity > 0 && $quantity <= 19) {
                                                $stockClass = 'text-danger';
                                                $stockBadgeClass = 'stock-low';
                                                $stockIcon = '<i class="lni lni-warning me-1"></i>';
                                            } elseif ($quantity <= 50 && $quantity >= 20) {
                                                $stockClass = 'text-warning';
                                                $stockBadgeClass = 'stock-medium';
                                                $stockIcon = '<i class="lni lni-reload me-1"></i>';
                                            } elseif ($quantity <= 100 && $quantity > 50) {
                                                $stockClass = 'text-success';
                                                $stockBadgeClass = 'stock-high';
                                                $stockIcon = '<i class="lni lni-checkmark-circle me-1"></i>';
                                            } elseif ($quantity == 0) {
                                                $stockClass = 'text-danger';
                                                $stockBadgeClass = 'stock-low';
                                                $stockIcon = '<i class="lni lni-close-circle me-1"></i>';
                                            }

                                            echo '<tr>
                                                    <td>
                                                        <i class="lni lni-package me-2" style="color: var(--gold-dark);"></i>
                                                        ' . htmlspecialchars($row["supplies"]) . '
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">
                                                            ' . htmlspecialchars($row["brand"]) . '
                                                        </span>
                                                    </td>
                                                    <td>' . htmlspecialchars($row["classification"]) . '</td>
                                                    <td>
                                                        <span class="stock-badge ' . $stockBadgeClass . '">
                                                            ' . $stockIcon . htmlspecialchars($quantity) . '
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="date-badge">
                                                            <i class="lni lni-calendar me-1" style="color: var(--maroon);"></i>
                                                            ' . htmlspecialchars($formattedDate) . '
                                                        </span>
                                                    </td>
                                                    <td>';
                                        
                                            if ($limitReached) {
                                                echo '<button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#requestModal"
                                                        data-supplies-id="' . htmlspecialchars($row["supplies_id"]) . '" 
                                                        data-supplies="' . htmlspecialchars($row["supplies"]) . '" 
                                                        data-stocks="' . htmlspecialchars($row["stocks"]) . '">
                                                        <i class="lni lni-warning me-1"></i>Request
                                                    </button>';
                                            } else {
                                                echo '<button class="btn btn-sm custom-btn-bg" data-bs-toggle="modal" data-bs-target="#requestModal" 
                                                        data-supplies-id="' . htmlspecialchars($row["supplies_id"]) . '" 
                                                        data-supplies="' . htmlspecialchars($row["supplies"]) . '" 
                                                        data-stocks="' . htmlspecialchars($row["stocks"]) . '">
                                                        <i class="lni lni-cart me-1"></i>Order
                                                    </button>';
                                            }

                                            echo '</td>
                                                </tr>';
                                        }
                                    } else {
                                        echo '<tr id="no-record"><td colspan="6" class="text-center py-4">No Record Found</td></tr>';
                                    }

                                    $conn->close();
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="lni lni-information me-2"></i>
                                Stock Level Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center">
                                        <span class="stock-badge stock-high me-2">
                                            <i class="lni lni-checkmark-circle"></i>
                                        </span>
                                        <span>High Stock (51-100): Good supply level</span>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center">
                                        <span class="stock-badge stock-medium me-2">
                                            <i class="lni lni-reload"></i>
                                        </span>
                                        <span>Medium Stock (20-50): Consider reordering</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <span class="stock-badge stock-low me-2">
                                            <i class="lni lni-warning"></i>
                                        </span>
                                        <span>Low Stock (0-19): Reorder immediately</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="requestModalLabel">
                        <i class="lni lni-cart me-2"></i>
                        Supply Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="submit_request.php" method="POST">
                        <input type="hidden" id="request-supplies-id" name="supplies_id">
                        <div class="mb-3">
                            <label for="request-supplies-name" class="form-label">Supply Name</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="lni lni-package"></i>
                                </span>
                                <input type="text" id="request-supplies-name" name="supplies_name" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="request-stocks" class="form-label">Available Stocks</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="lni lni-database"></i>
                                </span>
                                <input type="text" id="request-stocks" name="stocks" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="request-quantity" class="form-label">Quantity</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="lni lni-calculator"></i>
                                </span>
                                <input type="number" id="request-quantity" name="quantity" class="form-control" required min="1">
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="lni lni-checkmark-circle me-2"></i>
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="script.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logoutLink = document.getElementById('logout-link');
            if (logoutLink) {
                logoutLink.addEventListener('click', function(event) {
                    event.preventDefault();
                    const confirmLogout = confirm('Are you sure you want to log out?');
                    if (confirmLogout) {
                        window.location.href = logoutLink.href;
                    }
                });
            }
        });
    </script>

    <script>
        document.querySelectorAll('[data-bs-target="#requestModal"]').forEach(button => {
            button.addEventListener('click', function() {
                const suppliesId = this.getAttribute('data-supplies-id');
                const suppliesName = this.getAttribute('data-supplies');
                const stocks = this.getAttribute('data-stocks');

                document.getElementById('request-supplies-id').value = suppliesId;
                document.getElementById('request-supplies-name').value = suppliesName;
                document.getElementById('request-stocks').value = stocks;
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categoryFilter = document.getElementById('category-filter');
            const classificationFilter = document.getElementById('classification-filter');
            const table = document.getElementById('request-table');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            function filterTable() {
                const categoryValue = categoryFilter.value.toLowerCase();
                const classificationValue = classificationFilter.value.toLowerCase();
                let recordFound = false;

                for (let i = 0; i < rows.length; i++) {
                    if (rows[i].id === 'no-record') continue;
                    
                    const categoryCell = rows[i].cells[1].textContent.toLowerCase();
                    const classificationCell = rows[i].cells[2].textContent.toLowerCase();

                    if (
                        (categoryValue === '' || categoryCell.includes(categoryValue)) &&
                        (classificationValue === '' || classificationCell.includes(classificationValue))
                    ) {
                        rows[i].style.display = '';
                        recordFound = true;
                    } else {
                        rows[i].style.display = 'none';
                    }
                }

                const noRecordMessage = document.getElementById('no-record');
                if (noRecordMessage) {
                    noRecordMessage.style.display = recordFound ? 'none' : '';
                }
            }

            if (categoryFilter) categoryFilter.addEventListener('change', filterTable);
            if (classificationFilter) classificationFilter.addEventListener('change', filterTable);
        });
    </script>

    <script>
        function filterSupplies() {
            const input = document.getElementById('search-input');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('request-table');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            let recordFound = false;

            for (let i = 0; i < rows.length; i++) {
                if (rows[i].id === 'no-record') continue;
                
                const cells = rows[i].getElementsByTagName('td');
                let match = false;

                for (let j = 0; j < cells.length; j++) {
                    if (cells[j]) {
                        const cellText = cells[j].textContent || cells[j].innerText;
                        if (cellText.toLowerCase().indexOf(filter) > -1) {
                            match = true;
                            break;
                        }
                    }
                }

                rows[i].style.display = match ? '' : 'none';
                recordFound = recordFound || match;
            }

            const noRecordMessage = document.getElementById('no-record');
            if (noRecordMessage) {
                noRecordMessage.style.display = recordFound ? 'none' : '';
            }
        }
    </script>
</body>
</html>