<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPARK - Supplies Usage History</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="supervisor_usage_history.css?v=<?php echo filemtime('supervisor_usage_history.css'); ?>">
</head>

<body>
    <?php include 'supervisor_navbar.php'; ?>

    <div class="main p-9">
        <div class="container">
            <h1 class="mb-4">
                <i class="lni lni-timer me-2" style="color: var(--gold);"></i>
                Supplies Usage History
            </h1>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="lni lni-search me-2"></i>
                                Search & Filter
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="lni lni-search"></i>
                                        </span>
                                        <input 
                                            type="text" 
                                            id="search-input" 
                                            class="form-control" 
                                            placeholder="Search by name, ID or supplies" 
                                            onkeyup="filterStaff()">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <select id="year-filter" class="form-select" onchange="filterByDate()">
                                                <option value="">All Years</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <select id="month-filter" class="form-select" onchange="filterByDate()">
                                                <option value="">All Months</option>
                                                <option value="01">January</option>
                                                <option value="02">February</option>
                                                <option value="03">March</option>
                                                <option value="04">April</option>
                                                <option value="05">May</option>
                                                <option value="06">June</option>
                                                <option value="07">July</option>
                                                <option value="08">August</option>
                                                <option value="09">September</option>
                                                <option value="10">October</option>
                                                <option value="11">November</option>
                                                <option value="12">December</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-md-end">
                                    <button type="button" class="btn btn-print w-100" id="print-button">
                                        <i class="lni lni-printer me-2"></i> Print
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Summary -->
            <div id="staff-summary" class="mb-4" style="display:none;">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 text-start">
                                <h5 class="mb-1">Staff Name: <span id="summary-name" class="fw-normal"></span></h5>
                                <h6 class="mb-0 text-muted">Employee ID: <span id="summary-id" class="fw-normal"></span></h6>
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
                                <i class="lni lni-list me-2"></i>
                                Usage History
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="Supplies-usage-history-table" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="25%">Full Name</th>
                                            <th width="15%">Employee ID</th>
                                            <th width="25%">Supplies</th>
                                            <th width="10%">Quantity</th>
                                            <th width="25%">Transaction Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        require 'database.php'; 

                                        $sql = "SELECT full_name, employee_id, supplies, quantity, transaction_date FROM supplies_usage_history ORDER BY transaction_date DESC";
                                        $result = $conn->query($sql);

                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                $formattedDate = date("F j, Y g:ia", strtotime($row["transaction_date"]));
                                                $quantity = intval($row["quantity"]);
                                                if ($quantity > 5) {
                                                    $quantityBadge = '<span class="badge bg-danger">' . htmlspecialchars($row["quantity"]) . '</span>';
                                                } elseif ($quantity >= 3) {
                                                    $quantityBadge = '<span class="badge bg-warning text-dark">' . htmlspecialchars($row["quantity"]) . '</span>';
                                                } else {
                                                    $quantityBadge = '<span class="badge bg-success">' . htmlspecialchars($row["quantity"]) . '</span>';
                                                }

                                                echo '<tr>
                                                        <td>
                                                            <i class="lni lni-user me-2" style="color: var(--maroon);"></i>
                                                            ' . htmlspecialchars($row["full_name"]) . '
                                                        </td>
                                                        <td>' . htmlspecialchars($row["employee_id"]) . '</td>
                                                        <td>
                                                            <i class="lni lni-package me-2" style="color: var(--gold-dark);"></i>
                                                            ' . htmlspecialchars($row["supplies"]) . '
                                                        </td>
                                                        <td>' . $quantityBadge . '</td>
                                                        <td>
                                                            <i class="lni lni-calendar me-2" style="color: var(--maroon);"></i>
                                                            ' . $formattedDate . '
                                                        </td>
                                                    </tr>';
                                            }
                                        } else {
                                            echo '<tr><td colspan="5" class="text-center py-4">No Record Found</td></tr>';
                                        }

                                        $conn->close();
                                        ?>
                                        <tr id="no-record" style="display:none;">
                                            <td colspan="5" class="text-center py-4">
                                                <i class="lni lni-search-alt" style="font-size: 2rem; color: var(--maroon-light);"></i>
                                                <p class="mt-2 mb-0">No matching records found</p>
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
                                Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="d-flex align-items-center">
                                        <i class="lni lni-search me-2" style="color: var(--maroon);"></i>
                                        <span>Use the search box to filter by name, ID, or supplies</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="lni lni-printer me-2" style="color: var(--maroon);"></i>
                                        <span>Click the Print button to generate a printable report</span>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h6 class="fw-bold">Quantity Usage Legend:</h6>
                            <ul>
                                <li><span class="badge bg-success"> Normal usage (1-2)</span></li>
                                <li><span class="badge bg-warning text-dark">Moderate usage (2-5)</span></li>
                                <li><span class="badge bg-danger">High usage(&gt;5)</span></li>
                            </ul>
                        </div>
                    </div>
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
            logoutLink.addEventListener('click', function(event) {
                event.preventDefault(); 
                const confirmLogout = confirm('Are you sure you want to log out?'); 
                if (confirmLogout) {
                    window.location.href = logoutLink.href; 
                }
            });
            
            // Highlight active sidebar link
            const currentPage = window.location.pathname.split('/').pop();
            const sidebarLinks = document.querySelectorAll('.sidebar-link');
            sidebarLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>
    
    <script>
        document.getElementById('print-button').addEventListener('click', function () {
            const table = document.getElementById('Supplies-usage-history-table');
            const tableClone = table.cloneNode(true);

            const searchInput = document.getElementById('search-input') ? document.getElementById('search-input').value.trim() : ''; // Get the search input
            const headerRow = tableClone.querySelector('thead tr');
            const bodyRows = tableClone.querySelectorAll('tbody tr');

            // Identify the index of the columns
            const headerColumns = Array.from(headerRow.children);
            const fullNameIndex = headerColumns.findIndex(th => th.textContent.trim() === 'Full Name');
            const employeeidIndex = headerColumns.findIndex(th => th.textContent.trim() === 'Employee ID');

            // Collect all the Full Names and Employee IDs for the filtered rows
            let fullNames = [];
            let employeeIDs = [];
            
            if (searchInput !== '') {
                fullNames = Array.from(bodyRows)
                    .filter(row => row.style.display !== 'none') // Only consider visible rows
                    .map(row => row.children[fullNameIndex]?.textContent.trim()) // Get Full Name from the column
                    .filter(name => name); // Remove any empty names

                employeeIDs = Array.from(bodyRows)
                    .filter(row => row.style.display !== 'none') // Only consider visible rows
                    .map(row => row.children[employeeidIndex]?.textContent.trim()) // Get Employee ID from the column
                    .filter(id => id); // Remove any empty IDs

                // Ensure that only unique names and IDs are displayed
                fullNames = [...new Set(fullNames)];
                employeeIDs = [...new Set(employeeIDs)];

                // Remove the "Full Name" column if there is a search input
                if (fullNameIndex > -1) {
                    headerRow.removeChild(headerRow.children[fullNameIndex]);
                    bodyRows.forEach(row => {
                        if (row.children[fullNameIndex]) {
                            row.removeChild(row.children[fullNameIndex]);
                        }
                    });
                }
                
                // Remove the "Employee ID" column if there is a search input
                if (employeeidIndex > -1) {
                    // Adjust index if we've already removed Full Name column
                    const adjustedEmployeeIdIndex = fullNameIndex > -1 && employeeidIndex > fullNameIndex 
                        ? employeeidIndex - 1 
                        : employeeidIndex;
                        
                    headerRow.removeChild(headerRow.children[adjustedEmployeeIdIndex]);
                    bodyRows.forEach(row => {
                        if (row.children[adjustedEmployeeIdIndex]) {
                            row.removeChild(row.children[adjustedEmployeeIdIndex]);
                        }
                    });
                }
            }

            // Open a new window for printing
            const printWindow = window.open('', '', 'height=800,width=1000');

            // Write content to the new window
            printWindow.document.write('<html><head><title>Supplies Usage History</title>');
            printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">');
            printWindow.document.write('<style>body { padding: 20px; } .header { margin-bottom: 20px; } .logo { width: 200px; height: auto; } h1 { color: #800000; margin-top: 20px; } table { width: 100%; border-collapse: collapse; } th { background-color: #800000; color: white; } </style>');
            printWindow.document.write('</head><body>');

            printWindow.document.write('<div class="header">');
            printWindow.document.write('<img src="spark_logo.png" alt="Spark Logo" class="logo"/>');
            printWindow.document.write('<h1>Supplies Usage History</h1>');
            printWindow.document.write('</div>');

            // Add a summary of Full Names and Employee IDs if search input is provided
            if (searchInput !== '' && fullNames.length > 0) {
                printWindow.document.write('<div class="staff-info mb-4 p-3 bg-light rounded">');
                printWindow.document.write(`<h4 style="color: #800000;">Staff Name: <span style="font-weight: normal;">${fullNames.join(', ')}</span></h4>`);
                printWindow.document.write(`<h5 style="color: #800000;">Employee ID: <span style="font-weight: normal;">${employeeIDs.join(', ')}</span></h5>`);
                printWindow.document.write('</div>');
            }

            // Add the table (with or without the "Full Name" and "Employee ID" columns)
            printWindow.document.write('<table class="table table-bordered">' + tableClone.innerHTML + '</table>');
            printWindow.document.write('<div class="mt-4 text-center text-muted"><small>Generated on ' + new Date().toLocaleString() + '</small></div>');
            printWindow.document.write('</body></html>');

            // Finalize and trigger print
            printWindow.document.close();
            printWindow.document.querySelector('img').onload = function () {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };
        });
    </script>

    <script>
        function filterStaff() {
            const searchInput = document.getElementById('search-input').value.toLowerCase();
            const rows = document.querySelectorAll('#Supplies-usage-history-table tbody tr');
            const headerRow = document.querySelector('#Supplies-usage-history-table thead tr');
            let recordFound = false;

            // Find column indexes
            const headerColumns = Array.from(headerRow.children);
            const fullNameIndex = headerColumns.findIndex(th => th.textContent.trim() === 'Full Name');
            const employeeidIndex = headerColumns.findIndex(th => th.textContent.trim() === 'Employee ID');
            const suppliesIndex = headerColumns.findIndex(th => th.textContent.trim() === 'Supplies');

            // Collect unique names and IDs for summary
            let fullNames = [];
            let employeeIDs = [];

            rows.forEach(row => {
                if (row.id === 'no-record') return; // Skip the no-record row
                
                const fullName = row.children[fullNameIndex]?.textContent.toLowerCase() || '';
                const employeeID = row.children[employeeidIndex]?.textContent.toLowerCase() || '';
                const supplies = row.children[suppliesIndex]?.textContent.toLowerCase() || '';
                
                const matchesSearch = fullName.includes(searchInput) || 
                                     employeeID.includes(searchInput) || 
                                     supplies.includes(searchInput);

                if (matchesSearch) {
                    row.style.display = '';
                    recordFound = true;
                    if (searchInput !== '') {
                        const nameVal = row.children[fullNameIndex]?.textContent.trim();
                        const idVal = row.children[employeeidIndex]?.textContent.trim();
                        if (nameVal && !fullNames.includes(nameVal)) fullNames.push(nameVal);
                        if (idVal && !employeeIDs.includes(idVal)) employeeIDs.push(idVal);
                    }
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide summary and columns
            const staffSummary = document.getElementById('staff-summary');
            const summaryName = document.getElementById('summary-name');
            const summaryId = document.getElementById('summary-id');

            if (searchInput !== '' && fullNames.length === 1 && employeeIDs.length === 1) {
                staffSummary.style.display = '';
                summaryName.textContent = fullNames[0];
                summaryId.textContent = employeeIDs[0];

                // Hide columns in header
                if (headerRow.children[employeeidIndex]) headerRow.children[employeeidIndex].style.display = 'none';
                if (headerRow.children[fullNameIndex]) headerRow.children[fullNameIndex].style.display = 'none';
                // Hide columns in body
                rows.forEach(row => {
                    if (row.id !== 'no-record') { // Skip the no-record row
                        if (row.children[employeeidIndex]) row.children[employeeidIndex].style.display = 'none';
                        if (row.children[fullNameIndex]) row.children[fullNameIndex].style.display = 'none';
                    }
                });
            } else {
                staffSummary.style.display = 'none';
                // Show columns in header
                if (headerRow.children[employeeidIndex]) headerRow.children[employeeidIndex].style.display = '';
                if (headerRow.children[fullNameIndex]) headerRow.children[fullNameIndex].style.display = '';
                // Show columns in body
                rows.forEach(row => {
                    if (row.id !== 'no-record') { // Skip the no-record row
                        if (row.children[employeeidIndex]) row.children[employeeidIndex].style.display = '';
                        if (row.children[fullNameIndex]) row.children[fullNameIndex].style.display = '';
                    }
                });
            }

            const noRecordMessage = document.getElementById('no-record');
            if (recordFound) {
                noRecordMessage.style.display = 'none';
            } else {
                noRecordMessage.style.display = '';
            }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            populateYearDropdown();

            function populateYearDropdown() {
                const yearFilter = document.getElementById('year-filter');
                const currentYear = new Date().getFullYear();
                
                for (let year = currentYear; year >= currentYear - 10; year--) {
                    const option = document.createElement('option');
                    option.value = year;
                    option.textContent = year;
                    yearFilter.appendChild(option);
                }
            }
        });

        function filterByDate() {
            const year = document.getElementById('year-filter').value;
            const month = document.getElementById('month-filter').value;
            const rows = document.querySelectorAll('#Supplies-usage-history-table tbody tr');
            let recordFound = false;

            rows.forEach(row => {
                if (row.id === 'no-record') return; // Skip the no-record row
                
                const transactionDateText = row.querySelector('td:nth-child(5)')?.textContent.trim();
                if (transactionDateText) {
                    const dateParts = transactionDateText.split(", ");
                    if (dateParts.length === 2) {
                        const [monthDay, yearTime] = dateParts;
                        const [monthName, day] = monthDay.split(" ");
                        const [yearParsed] = yearTime.split(" ");
                        const rowYear = parseInt(yearParsed, 10);
                        const rowMonth = new Date(`${monthName} 1, 2000`).getMonth() + 1; 
                        const rowMonthStr = rowMonth.toString().padStart(2, "0");
                        
                        const matchesYear = !year || rowYear.toString() === year;
                        const matchesMonth = !month || rowMonthStr === month;

                        if (matchesYear && matchesMonth) {
                            row.style.display = ""; 
                            recordFound = true;
                        } else {
                            row.style.display = "none"; 
                        }
                    }
                }
            });
            
            const noRecordMessage = document.getElementById("no-record");
            if (recordFound) {
                noRecordMessage.style.display = "none";
            } else {
                noRecordMessage.style.display = "";
            }
        }
    </script>
</body>
</html>