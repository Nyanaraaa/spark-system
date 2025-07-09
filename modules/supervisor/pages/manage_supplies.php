<?php
/**
 * Supervisor Manage Supplies Page
 * Uses new security components and authentication
 */

// Include bootstrap for security components
require_once dirname(dirname(dirname(__DIR__))) . '/includes/bootstrap.php';

// Check authentication
SessionManager::requireAuth();
SessionManager::requireRole('supervisor');

try {
    // Get database connection
    $db = Database::getInstance();
    /** @var MySQLiCompatibility $conn */
    $conn = $db->getConnection();
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
    <link rel="manifest" href="../../../manifest.json">
    <link rel="stylesheet" href="../../../assets/css/global.css?v=<?php echo filemtime('../../../assets/css/global.css'); ?>">
    <link rel="stylesheet"
        href="../../../assets/css/supervisorupdate.css?v=<?php echo filemtime('../../../assets/css/supervisorupdate.css'); ?>">
</head>

<body>
    <?php include '../../../components/navbar/supervisor_navbar.php'; ?>

    <div class="main p-9">
        <div class="container">
            <h1 class="mb-4">
                <i class="lni lni-archive me-2" style="color: var(--gold);"></i>
                Manage Supplies
            </h1>

            <?php 
            // Check for flash messages
            $flashMessage = SessionManager::getFlashMessage();
            if ($flashMessage): ?>
                <div class="alert alert-<?= $flashMessage['type'] === 'success' ? 'success' : 'danger'; ?>" id="alert-message">
                    <?= htmlspecialchars($flashMessage['message']); ?>
                </div>
                <script>
                    setTimeout(function () {
                        const alertMessage = document.getElementById('alert-message');
                        if (alertMessage) {
                            alertMessage.style.display = 'none';
                        }
                    }, 5000);
                </script>
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
                                        <input type="text" id="search-input" class="form-control"
                                            placeholder="Search supplies" onkeyup="filterSupplies()">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <select id="category-filter" class="form-select">
                                        <option value="">All Brands</option>
                                        <?php
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
                                    <button class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#addSupplyModal">
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
                            <div class="row align-items-center">
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
                                        <span>Medium Stock (20-50): Consider restocking</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <span class="stock-badge stock-low me-2">
                                            <i class="lni lni-warning"></i>
                                        </span>
                                        <span>Low Stock (0-19): Restock immediately</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include '../../../components/modals/update_stock_modal.php'; ?>
        <?php include '../../../components/modals/add_supply_modal.php'; ?>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
            crossorigin="anonymous"></script>
        <script src="../../../assets/js/script.js"></script>

        <script>
            document.querySelectorAll('[data-bs-target="#updateModal"]').forEach(button => {
                button.addEventListener('click', function () {
                    const suppliesId = this.getAttribute('data-supplies-id');
                    const stockLimit = this.getAttribute('data-stock-limit');

                    document.getElementById('update-supplies-id').value = suppliesId;
                    document.getElementById('update-stock-limit').value = stockLimit;
                });
            });

            function deleteSupplies(suppliesId) {
                if (confirm('Are you sure you want to delete this supply?')) {
                    window.location.href = `../api/delete_supplies.php?supplies_id=${suppliesId}`;
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                const logoutLink = document.getElementById('logout-link');
                if (logoutLink) {
                    logoutLink.addEventListener('click', function (event) {
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
                printWindow.document.write('<style>');
                printWindow.document.write('body { padding: 20px; font-family: Arial, sans-serif; }');
                printWindow.document.write('.header { margin-bottom: 20px; }');
                printWindow.document.write('.logo { width: 200px; height: auto; }');
                printWindow.document.write('h1 { color: #800000; margin-top: 20px; }');
                printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; }');
                printWindow.document.write('th, td { border: 1px solid #000; padding: 8px 12px; text-align: left; }');
                printWindow.document.write('th { background-color: #800000; color: white; font-weight: bold; }');
                printWindow.document.write('tr:nth-child(even) { background-color: #f9f9f9; }');
                printWindow.document.write('tr:nth-child(odd) { background-color: #ffffff; }');
                printWindow.document.write('.brand-badge, .classification-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; }');
                printWindow.document.write('.brand-badge { background-color: #f8f9fa; color: #495057; }');
                printWindow.document.write('.classification-badge { background-color: #fff3cd; color: #856404; }');
                printWindow.document.write('.text-success { color: #28a745 !important; font-weight: bold; }');
                printWindow.document.write('.text-warning { color: #ffc107 !important; font-weight: bold; }');
                printWindow.document.write('.text-danger { color: #dc3545 !important; font-weight: bold; }');
                printWindow.document.write('</style>');
                printWindow.document.write('</head><body>');

                printWindow.document.write('<div class="header">');
                printWindow.document.write('<img src="../../../assets/images/spark_logo.png" alt="Spark Logo" class="logo"/>');
                printWindow.document.write('<h1>Supplies Inventory</h1>');
                printWindow.document.write('</div>');

                printWindow.document.write('<table>' + tableClone.innerHTML + '</table>');
                printWindow.document.write('<div style="margin-top: 20px; text-align: center; color: #666; font-size: 0.9rem;">Generated on ' + new Date().toLocaleString() + '</div>');
                printWindow.document.write('</body></html>');

                printWindow.document.close();
                printWindow.document.querySelector('img').onload = function () {
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

            document.addEventListener('DOMContentLoaded', function () {
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

<?php
} catch (Exception $e) {
    ErrorHandler::logError('Supervisor manage supplies error: ' . $e->getMessage());
    SessionManager::setFlashMessage('An error occurred loading the supplies page. Please try again.', 'error');
    header('Location: ../../../index.php');
    exit();
}
?>