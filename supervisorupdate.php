<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPARK - Manage Supplies</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="supervisorupdate.css?v=<?php echo filemtime('supervisorupdate.css'); ?>">
</head>

<body>
    <?php include 'supervisor_navbar.php'; ?>

    <div class="main p-9">
        <div class="container">
            <h1 class="mb-4">
                <i class="lni lni-archive me-2" style="color: var(--gold);"></i>
                Manage Supplies
            </h1>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success" id="alert-message">
                    <?= htmlspecialchars($_SESSION['success']); ?>
                </div>
                <script>
                    setTimeout(function () {
                        const alertMessage = document.getElementById('alert-message');
                        if (alertMessage) {
                            alertMessage.style.display = 'none';
                        }
                    }, 5000);
                </script>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger" id="alert-message">
                    <?= htmlspecialchars($_SESSION['error']); ?>
                </div>
                <script>
                    setTimeout(function () {
                        const alertMessage = document.getElementById('alert-message');
                        if (alertMessage) {
                            alertMessage.style.display = 'none';
                        }
                    }, 5000);
                </script>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Filter Controls -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="lni lni-search me-2"></i>
                                Filter Supplies
                            </h5>
                            <button type="button" class="btn btn-gold" id="print-button">
                                <i class="lni lni-printer me-1"></i> Print Inventory
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="lni lni-search"></i>
                                        </span>
                                        <input type="text" id="search-input" class="form-control" placeholder="Search supplies" onkeyup="filterSupplies()">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
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
                                <div class="col-md-4 mb-3 mb-md-0">
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
                            <div class="row mt-3">
                                <div class="col-12 text-center">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplyModal">
                                        <i class="lni lni-plus me-1"></i> Add New Supply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="lni lni-package me-2"></i>
                                Inventory List
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="Update-stock-table" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Supplies</th>
                                            <th>Brand</th>
                                            <th>Classification</th>
                                            <th>Current Stock</th>
                                            <th>Restock Quantity</th>
                                            <th>Last Updated</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        require 'database.php';
                                        $sql = "SELECT supplies_id, supplies, classification, brand, stocks, last_updated, stock_limit FROM supplies";
                                        $result = $conn->query($sql);

                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                // Format the date
                                                $formatted_date = date("F j, Y g:ia", strtotime($row["last_updated"]));

                                                // Determine the stock color class
                                                $stockClass = '';
                                                if ($row["stocks"] <= 100 && $row["stocks"] > 50) {
                                                    $stockClass = 'text-success'; // Green
                                                } elseif ($row["stocks"] <= 50 && $row["stocks"] >= 20) {
                                                    $stockClass = 'text-warning'; // Yellow
                                                } elseif ($row["stocks"] <= 19) {
                                                    $stockClass = 'text-danger'; // Red
                                                }

                                                // Get stock limit from database
                                                $stock_limit = $row["stock_limit"];

                                                // Calculate restock quantity dynamically
                                                $restock_quantity = max(0, $stock_limit - $row["stocks"]);

                                                // Generate the table row
                                                echo '<tr>
                                                    <td>
                                                        <i class="lni lni-package me-2" style="color: var(--maroon);"></i>
                                                        ' . htmlspecialchars($row["supplies"]) . '
                                                    </td>
                                                    <td>
                                                        <span class="brand-badge">
                                                            <i class="lni lni-tag me-1"></i>
                                                            ' . htmlspecialchars($row["brand"]) . '
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="classification-badge">
                                                            <i class="lni lni-folder me-1"></i>
                                                            ' . htmlspecialchars($row["classification"]) . '
                                                        </span>
                                                    </td>
                                                    <td class="' . $stockClass . ' fw-bold">' . $row["stocks"] . '</td>
                                                    <td>' . $restock_quantity . '</td>
                                                    <td>' . $formatted_date . '</td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn-update update-btn btn btn-sm" data-bs-toggle="modal" data-bs-target="#updateModal" data-supplies-id="' . $row["supplies_id"] . '" data-stock-limit="' . $row["stock_limit"] . '">
                                                                <i class="lni lni-reload me-1"></i> Update
                                                            </button>
                                                            <button class="btn-delete delete-btn btn btn-sm" onclick="deleteSupplies(' . $row["supplies_id"] . ')">
                                                                <i class="lni lni-trash-can me-1"></i> Delete
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>';
                                            }
                                        } else {
                                            echo '<tr><td colspan="7" class="text-center">No Record Found</td></tr>';
                                        }
                                        $conn->close();
                                        ?>

                                        <tr id="no-record" style="display:none;">
                                            <td colspan="7" class="text-center py-4">
                                                <i class="lni lni-search me-2"></i>
                                                No matching records found
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Stock Modal -->
    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateModalLabel">
                        <i class="lni lni-reload me-2"></i> Restock Supplies
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="update_stock.php" method="post">
                        <input type="hidden" id="update-supplies-id" name="supplies_id">
                        
                        <div class="mb-4">
                            <label for="update-stocks" class="form-label fw-bold" style="color: var(--maroon);">Add Stock Quantity</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="lni lni-plus"></i>
                                </span>
                                <input type="number" id="update-stocks" name="stocks" class="form-control" min="0" required>
                            </div>
                            <div class="form-text">Enter the quantity you want to add to current stock.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="update-stock-limit" class="form-label fw-bold" style="color: var(--maroon);">Stock Limit</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="lni lni-warning"></i>
                                </span>
                                <input type="number" id="update-stock-limit" name="stock_limit" class="form-control" min="1" required>
                            </div>
                            <div class="form-text">Set the minimum stock level before restock is needed.</div>
                        </div>
                    
                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="lni lni-checkmark-circle me-1"></i> Update Stock
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Supplies Modal -->
    <div class="modal fade" id="addSupplyModal" tabindex="-1" aria-labelledby="addSupplyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSupplyModalLabel">
                        <i class="lni lni-plus-circle me-2"></i> Add New Supply
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="add_supply.php" method="post">
                        <div class="mb-3">
                            <label for="supply-name" class="form-label fw-bold" style="color: var(--maroon);">Supply Name</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="lni lni-package"></i>
                                </span>
                                <input type="text" id="supply-name" name="supply_name" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category" class="form-label fw-bold" style="color: var(--maroon);">Brand Name</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="lni lni-tag"></i>
                                </span>
                                <input type="text" id="category" name="category" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="classification" class="form-label fw-bold" style="color: var(--maroon);">Classification</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="lni lni-folder"></i>
                                </span>
                                <select id="classification" name="classification" class="form-select" onchange="toggleOtherInput()" required>
                                    <option value="" disabled selected>Select Classification</option>
                                    <option value="Floor Cleaner">Floor Cleaner</option>
                                    <option value="All-purpose Cleaner">All-purpose Cleaner</option>
                                    <option value="Bathroom Cleaner">Bathroom Cleaner</option>
                                    <option value="Disinfectant">Disinfectant</option>
                                    <option value="Heavy-duty Cleaner">Heavy-duty Cleaner</option>
                                    <option value="Rust Remover">Rust Remover</option>
                                    <option value="Stain Remover">Stain Remover</option>
                                    <option value="Upholstery Cleaner">Upholstery Cleaner</option>
                                    <option value="Pool/Surface Cleaner">Pool/Surface Cleaner</option>
                                    <option value="Surface Scrubber">Surface Scrubber</option>
                                    <option value="Wiping Cloth">Wiping Cloth</option>
                                    <option value="Painting Tool">Painting Tool</option>
                                    <option value="Hand Protection">Hand Protection</option>
                                    <option value="Waste Disposal">Waste Disposal</option>
                                    <option value="Plumbing Accessory">Plumbing Accessory</option>
                                    <option value="Dish Soap">Dish Soap</option>
                                    <option value="Laundry Detergent">Laundry Detergent</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3" id="otherClassificationDiv" style="display: none;">
                            <label for="otherClassification" class="form-label fw-bold" style="color: var(--maroon);">Custom Classification</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="lni lni-pencil"></i>
                                </span>
                                <input type="text" id="otherClassification" class="form-control" placeholder="Enter new classification" onblur="updateDropdown()">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="initial-stock" class="form-label fw-bold" style="color: var(--maroon);">Initial Stock</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="lni lni-database"></i>
                                </span>
                                <input type="number" id="initial-stock" name="initial_stock" class="form-control" min="0" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="stock-limit" class="form-label fw-bold" style="color: var(--maroon);">Stock Limit</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="lni lni-warning"></i>
                                </span>
                                <input type="number" id="stock-limit" name="stock_limit" class="form-control" min="1" required>
                            </div>
                            <div class="form-text">Set the minimum stock level before restock is needed.</div>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="lni lni-checkmark-circle me-1"></i> Add Supply
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
        document.querySelectorAll('[data-bs-target="#updateModal"]').forEach(button => {
            button.addEventListener('click', function() {
                const suppliesId = this.getAttribute('data-supplies-id');
                const stockLimit = this.getAttribute('data-stock-limit');

                document.getElementById('update-supplies-id').value = suppliesId;
                document.getElementById('update-stock-limit').value = stockLimit;
            });
        });

        function deleteSupplies(suppliesId) {
            if (confirm('Are you sure you want to delete this supply?')) {
                window.location.href = `delete_supplies.php?supplies_id=${suppliesId}`;
            }
        }
        
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
            
            var updateModal = document.getElementById("updateModal");
            updateModal.addEventListener("show.bs.modal", function (event) {
                var button = event.relatedTarget;
                var suppliesId = button.getAttribute("data-supplies-id");
                var stockLimit = button.getAttribute("data-stock-limit");

                document.getElementById("update-supplies-id").value = suppliesId;
                document.getElementById("update-stock-limit").value = stockLimit;
            });
        });

        document.getElementById('print-button').addEventListener('click', function () {
            const table = document.getElementById('Update-stock-table');
            const tableClone = table.cloneNode(true);

            const headerRow = tableClone.querySelector('thead tr');
            const bodyRows = tableClone.querySelectorAll('tbody tr');

            // Remove the Action column
            if (headerRow.lastElementChild) {
                headerRow.removeChild(headerRow.lastElementChild);
            }
            bodyRows.forEach(row => {
                if (row.lastElementChild) {
                    row.removeChild(row.lastElementChild);
                }
            });

            const printWindow = window.open('', '', 'height=800,width=1000');

            printWindow.document.write('<html><head><title>Supplies Inventory</title>');
            printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">');
            printWindow.document.write('<style>body { padding: 20px; } .header { margin-bottom: 20px; text-align: center; } .logo { width: 200px; height: auto; margin-bottom: 15px; } h1 { color: #800000; margin-top: 20px; } table { width: 100%; border-collapse: collapse; } th { background-color: #800000; color: white; } </style>');
            printWindow.document.write('</head><body>');

            printWindow.document.write('<div class="header">');
            printWindow.document.write('<img src="spark_logo.png" alt="Spark Logo" class="logo"/>');
            printWindow.document.write('<h1>Supplies Inventory</h1>');
            printWindow.document.write('</div>');

            printWindow.document.write('<table class="table table-bordered">' + tableClone.innerHTML + '</table>');
            printWindow.document.write('<div class="mt-4 text-center text-muted"><small>Generated on ' + new Date().toLocaleString() + '</small></div>');
            printWindow.document.write('</body></html>');

            printWindow.document.close();
            printWindow.document.querySelector('img').onload = function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };
        });

        function filterSupplies() {
            const input = document.getElementById('search-input');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('Update-stock-table');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            let recordFound = false;

            for (let i = 0; i < rows.length; i++) {
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
            noRecordMessage.style.display = recordFound ? 'none' : ''; 
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const categoryFilter = document.getElementById('category-filter');
            const classificationFilter = document.getElementById('classification-filter');
            const table = document.getElementById('Update-stock-table');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            function filterTable() {
                const categoryValue = categoryFilter.value.toLowerCase();
                const classificationValue = classificationFilter.value.toLowerCase();
                let recordFound = false;

                for (let i = 0; i < rows.length; i++) {
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
                noRecordMessage.style.display = recordFound ? 'none' : '';
            }

            categoryFilter.addEventListener('change', filterTable);
            classificationFilter.addEventListener('change', filterTable);
        });

        function toggleOtherInput() {
            const classificationSelect = document.getElementById('classification');
            const otherClassificationDiv = document.getElementById('otherClassificationDiv');

            // Show or hide the custom input field based on selection
            if (classificationSelect.value === 'Other') {
                otherClassificationDiv.style.display = 'block';
            } else {
                otherClassificationDiv.style.display = 'none';
            }
        }

        function updateDropdown() {
            const otherClassificationInput = document.getElementById('otherClassification');
            const classificationSelect = document.getElementById('classification');

            // Check if the input field has a non-empty value
            if (otherClassificationInput.value.trim() !== '') {
                // Create a new option and set its value and text
                const customOption = new Option(otherClassificationInput.value, otherClassificationInput.value);

                // Remove the 'Other' option if it exists
                const otherOption = classificationSelect.querySelector('option[value="Other"]');
                if (otherOption) {
                    otherOption.remove();
                }

                // Add the new custom option and select it
                classificationSelect.add(customOption);
                classificationSelect.value = otherClassificationInput.value;

                // Hide the custom input and clear its value
                otherClassificationInput.value = '';
                document.getElementById('otherClassificationDiv').style.display = 'none';
            }
        }
    </script>
</body>
</html>
