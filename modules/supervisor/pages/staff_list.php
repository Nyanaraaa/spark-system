<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'supervisor') {
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPARK - Staff List</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="manifest" href="../../../manifest.json">
    <link rel="stylesheet"
        href="../../../assets/css/staff_list.css?v=<?php echo filemtime('../../../assets/css/staff_list.css'); ?>">
</head>

<body>
    <?php include '../../../components/navbar/supervisor_navbar.php'; ?>

    <div class="main p-9">
        <div class="container">
            <h1 class="mb-4">
                <i class="lni lni-users me-2" style="color: var(--gold);"></i>
                Staff List
            </h1>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="lni lni-search me-2"></i>
                                Search Staff
                            </h5>
                            <button type="button" class="btn btn-gold" id="print-button">
                                <i class="lni lni-printer me-1"></i> Print List
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="search-container">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="lni lni-search"></i>
                                    </span>
                                    <input type="text" id="search-input" class="form-control"
                                        placeholder="Search by name, employee ID or position" onkeyup="filterStaff()">
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
                                <i class="lni lni-list me-2"></i>
                                Staff Directory
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="staff-list-table" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="35%">Full Name</th>
                                            <th width="20%">Employee ID</th>
                                            <th width="25%">Job Position</th>
                                            <th width="20%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Include the database connection file
                                        require '../../../config/database.php';

                                        // SQL query to fetch staff information from the database
                                        $sql = "SELECT staff_id, first_name, last_name, employee_id, position FROM staff";
                                        $result = $conn->query($sql); // Execute the query and store the result
                                        
                                        // Check if there are any staff records returned from the query
                                        if ($result->num_rows > 0) {
                                            // Loop through the results and display each staff member in a table row
                                            while ($row = $result->fetch_assoc()) {
                                                // Create a table row with data attributes for staff ID and full name
                                                echo '<tr class="staff-row" data-staff-id="' . $row["staff_id"] . '">
                                                    <td>
                                                        <i class="lni lni-user me-2" style="color: var(--maroon);"></i>
                                                        ' . htmlspecialchars($row["first_name"]) . ' ' . htmlspecialchars($row["last_name"]) . '
                                                    </td>
                                                    <td>
                                                        <span class="employee-badge">
                                                            <i class="lni lni-id-card me-1"></i>
                                                            ' . htmlspecialchars($row["employee_id"]) . '
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="position-badge">
                                                            ' . htmlspecialchars($row["position"]) . '
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button class="btn-view-profile" onclick="viewProfile(' . $row['staff_id'] . ')">
                                                            <i class="lni lni-eye me-1"></i> View Profile
                                                        </button>
                                                    </td>
                                                </tr>';
                                            }
                                        } else {
                                            // If no staff records are found, display a message in the table
                                            echo '<tr><td colspan="4" class="text-center py-4">No staff records found</td></tr>';
                                        }

                                        // Close the database connection
                                        $conn->close();
                                        ?>
                                        <tr id="no-record" style="display:none;">
                                            <td colspan="4" class="text-center py-4">
                                                <i class="lni lni-search me-2"></i>
                                                No matching staff records found
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

    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-labelledby="profileModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="profileModalLabel">
                        <i class="lni lni-user me-2"></i>
                        Staff Profile
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="profile-details">
                    <!-- Profile details will be inserted here dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="../../../assets/js/script.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const logoutLink = document.getElementById('logout-link');
            logoutLink.addEventListener('click', function (event) {
                event.preventDefault();
                const confirmLogout = confirm('Are you sure you want to log out?');
                if (confirmLogout) {
                    window.location.href = logoutLink.href;
                }
            });

            document.getElementById('print-button').addEventListener('click', function () {
                const table = document.getElementById('staff-list-table');
                const tableClone = table.cloneNode(true);

                const headerRow = tableClone.querySelector('thead tr');
                const bodyRows = tableClone.querySelectorAll('tbody tr');

                // Remove the Action column from header and body rows
                if (headerRow && headerRow.lastElementChild) {
                    headerRow.removeChild(headerRow.lastElementChild);
                }
                bodyRows.forEach(row => {
                    if (row.lastElementChild) {
                        row.removeChild(row.lastElementChild);
                    }
                });

                const printWindow = window.open('', '', 'height=800,width=1000');

                printWindow.document.write('<html><head><title>Staff Performance Assessment, Recording and Keeping</title>');
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
                printWindow.document.write('.employee-badge, .position-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; }');
                printWindow.document.write('.employee-badge { background-color: #f8f9fa; color: #495057; }');
                printWindow.document.write('.position-badge { background-color: #fff3cd; color: #856404; }');
                printWindow.document.write('</style>');
                printWindow.document.write('</head><body>');

                printWindow.document.write('<div class="header">');
                printWindow.document.write('<img src="../../../assets/images/spark_logo.png" alt="Spark Logo" class="logo"/>');
                printWindow.document.write('<h1>Staff List</h1>');
                printWindow.document.write('</div>');

                printWindow.document.write('<table>' + tableClone.innerHTML + '</table>');
                printWindow.document.write('<div style="margin-top: 20px; text-align: center; color: #666; font-size: 0.9rem;">Generated on ' + new Date().toLocaleString() + '</div>');
                printWindow.document.write('</body></html>');

                printWindow.document.close();

                // Fixed: Add timeout and error handling
                const img = printWindow.document.querySelector('img');
                let printExecuted = false;

                function executePrint() {
                    if (!printExecuted) {
                        printExecuted = true;
                        printWindow.focus();
                        printWindow.print();
                        printWindow.close();
                    }
                }

                if (img) {
                    img.onload = executePrint;
                    img.onerror = executePrint; // Handle image load error

                    // Fallback timeout in case image never loads
                    setTimeout(executePrint, 2000);
                } else {
                    // If no image found, execute immediately
                    setTimeout(executePrint, 100);
                }
            });
        });

        function filterStaff() {
            const searchInput = document.getElementById('search-input').value.toLowerCase();
            const rows = document.querySelectorAll('#staff-list-table tbody tr:not(#no-record)');
            let recordFound = false;

            rows.forEach(row => {
                if (row.id === 'no-record') return;

                const fullName = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
                const employeeID = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                const position = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';

                if (fullName.includes(searchInput) || employeeID.includes(searchInput) || position.includes(searchInput)) {
                    row.style.display = '';
                    recordFound = true;
                } else {
                    row.style.display = 'none';
                }
            });

            const noRecordMessage = document.getElementById('no-record');
            if (noRecordMessage) {
                noRecordMessage.style.display = recordFound ? 'none' : '';
            }
        }

        function viewProfile(staffId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '../api/get_staff_profile.php?staff_id=' + staffId, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const data = JSON.parse(xhr.responseText);

                    if (data) {
                        const profileHtml = `
                            <div class="row">
                                <div class="col-md-4 text-center mb-4">
                                    <img 
                                        src="../../../assets/images/uploads/${data.profile_picture || 'default.png'}" 
                                        alt="Profile Picture" 
                                        class="img-fluid rounded-circle profile-picture"   
                                        style="width: 180px; height: 180px; object-fit: cover;" 
                                    />
                                    <h5 class="mt-3 mb-0" style="color: var(--maroon);">${data.first_name} ${data.last_name}</h5>
                                    <span class="position-badge mt-2">${data.position || 'Staff'}</span>
                                </div>
                                <div class="col-md-8">
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0" style="color: var(--maroon);">
                                                <i class="lni lni-user me-2"></i>Personal Information
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-2">
                                                <div class="col-md-4 text-muted">Employee ID:</div>
                                                <div class="col-md-8">
                                                    <span class="employee-badge">${data.employee_id}</span>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-4 text-muted">Email Address:</div>
                                                <div class="col-md-8">
                                                    <i class="lni lni-envelope me-1" style="color: var(--maroon);"></i>
                                                    ${data.email_address}
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-md-4 text-muted">Contact Number:</div>
                                                <div class="col-md-8">
                                                    <i class="lni lni-phone me-1" style="color: var(--maroon);"></i>
                                                    ${data.contact_no}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        document.getElementById('profile-details').innerHTML = profileHtml;

                        // Use Bootstrap 5 modal
                        const profileModal = new bootstrap.Modal(document.getElementById('profileModal'));
                        profileModal.show();
                    } else {
                        alert('Staff profile not found.');
                    }
                }
            };
            xhr.send();
        }
    </script>
</body>

</html>