<?php
require_once '../../../includes/bootstrap.php';

// Require supervisor authentication
AuthMiddleware::init('supervisor');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPARK - Staff Records</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="manifest" href="../../../manifest.json">
    <link rel="stylesheet"
        href="../../../assets/css/staff.css?v=<?php echo filemtime('../../../assets/css/staff.css'); ?>">

</head>

<body>
    <?php include '../../../components/navbar/supervisor_navbar.php'; ?>

    <div class="main p-9">
        <div class="container">
            <!-- Alert Message (Hidden by default) -->
            <div id="alert-message" class="alert alert-success alert-dismissible fade show" role="alert"
                style="display: none;">
                <span id="alert-text"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <h1 class="mb-4">
                <i class="lni lni-files me-2" style="color: var(--gold);"></i>
                Staff Records
            </h1>

            <?php
            if (isset($_GET['message']) && isset($_GET['status'])) {
                $message = htmlspecialchars($_GET['message']);
                $status = htmlspecialchars($_GET['status']);
                echo "
                <div class='alert alert-" . ($status === 'success' ? 'success' : 'danger') . " alert-dismissible fade show' role='alert' id='autoDismissAlert'>
                    <i class='lni lni-" . ($status === 'success' ? 'checkmark-circle' : 'warning') . " me-2'></i>
                    $message
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>
                <script>
                    // Automatically dismiss the alert after 5 seconds
                    setTimeout(() => {
                        const alert = document.getElementById('autoDismissAlert');
                        if (alert) {
                            alert.classList.remove('show');
                            alert.classList.add('fade');
                            setTimeout(() => alert.remove(), 500); // Remove the element after fade-out
                        }
                    }, 5000);

                    // Remove the query parameters from the URL without reloading
                    const url = new URL(window.location.href);
                    url.search = '';
                    window.history.replaceState({}, document.title, url);
                </script>
                ";
            }
            ?>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="lni lni-cog me-2"></i>
                                Staff Management
                            </h5>
                            <button type="button" class="btn btn-print" id="print-button">
                                <i class="lni lni-printer me-1"></i> Print Records
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <button type="button" class="btn btn-create" data-bs-toggle="modal"
                                        data-bs-target="#uploadModal">
                                        <i class="lni lni-plus me-1"></i> CREATE NEW STAFF RECORD
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="lni lni-search"></i>
                                        </span>
                                        <input type="text" id="search-input" class="form-control"
                                            placeholder="Search by name, contact, email, ID or position"
                                            onkeyup="filterStaff()">
                                    </div>
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
                                <i class="lni lni-users me-2"></i>
                                Staff Directory
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="staff-records-table" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Contact No</th>
                                            <th>Email Address</th>
                                            <th>Employee ID</th>
                                            <th>Job Position</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT staff_id, first_name, last_name, contact_no, email_address, employee_id, position FROM staff";
                                        $result = $db->query($sql);

                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                echo '<tr>
                                                    <td>
                                                        <i class="lni lni-user me-2" style="color: var(--maroon);"></i>
                                                        ' . htmlspecialchars($row["first_name"]) . '
                                                    </td>
                                                    <td>' . htmlspecialchars($row["last_name"]) . '</td>
                                                    <td>
                                                        <i class="lni lni-phone me-2" style="color: var(--maroon);"></i>
                                                        ' . htmlspecialchars($row["contact_no"]) . '
                                                    </td>
                                                    <td>
                                                        <i class="lni lni-envelope me-2" style="color: var(--maroon);"></i>
                                                        ' . htmlspecialchars($row["email_address"]) . '
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
                                                        <button class="btn btn-warning btn-sm mb-1 me-1" onclick="editStaff(' . $row["staff_id"] . ', \'' . htmlspecialchars($row["first_name"]) . '\', \'' . htmlspecialchars($row["last_name"]) . '\', \'' . htmlspecialchars($row["contact_no"]) . '\', \'' . htmlspecialchars($row["email_address"]) . '\', \'' . htmlspecialchars($row["employee_id"]) . '\', \'' . htmlspecialchars($row["position"]) . '\')" data-bs-toggle="modal" data-bs-target="#editModal">
                                                            <i class="lni lni-pencil me-1"></i> Edit
                                                        </button>
                                                        <a href="../api/delete_staff_record.php?staff_id=' . $row["staff_id"] . '" class="btn btn-danger btn-sm mb-1" 
                                                        onclick="return confirm(\'Are you sure you want to delete this record?\')">
                                                            <i class="lni lni-trash-can me-1"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>';
                                            }
                                        } else {
                                            echo '<tr><td colspan="7" class="text-center py-4">No staff records found</td></tr>';
                                        }

                                        $db->close();
                                        ?>
                                        <tr id="no-record" style="display:none;">
                                            <td colspan="7" class="text-center py-4">
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

    <!-- Create Staff Record Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">
                        <i class="lni lni-user-add me-2"></i>
                        Create Staff Record
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="../api/create_staff_record.php" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="lni lni-user"></i></span>
                                <input type="text" id="first_name" name="first_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="lni lni-user"></i></span>
                                <input type="text" id="last_name" name="last_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="contact_no" class="form-label">Contact Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="lni lni-phone"></i></span>
                                <input type="text" id="contact_no" name="contact_no" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email_address" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="lni lni-envelope"></i></span>
                                <input type="email" id="email_address" name="email_address" class="form-control"
                                    required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="position" class="form-label">Position</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="lni lni-briefcase"></i></span>
                                <select id="position" name="position" class="form-select" required
                                    onchange="toggleOtherInput()">
                                    <option value="" disabled selected>Select a position</option>
                                    <option value="Janitor">Janitor</option>
                                    <option value="Janitress">Janitress</option>
                                    <option value="Gardener">Gardener</option>
                                    <option value="Custodian">Custodian</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div id="otherPositionDiv" class="mb-3" style="display: none;">
                            <label for="otherPosition" class="form-label">Please specify</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="lni lni-pencil"></i></span>
                                <input type="text" id="otherPosition" name="otherPosition" class="form-control"
                                    oninput="updateDropdown()">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="employee_id" class="form-label">Employee ID</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="lni lni-id-card"></i></span>
                                <input type="text" id="employee_id" name="employee_id" class="form-control" required>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="lni lni-save me-1"></i> Create Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Staff Record Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="lni lni-pencil me-2"></i>
                        Edit Staff Record
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="../api/edit_staff_record.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" id="edit_staff_id" name="staff_id">
                        <div class="mb-3">
                            <label for="edit_first_name" class="form-label">First Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="lni lni-user"></i></span>
                                <input type="text" id="edit_first_name" name="first_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_last_name" class="form-label">Last Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="lni lni-user"></i></span>
                                <input type="text" id="edit_last_name" name="last_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_contact_no" class="form-label">Contact Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="lni lni-phone"></i></span>
                                <input type="text" id="edit_contact_no" name="contact_no" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email_address" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="lni lni-envelope"></i></span>
                                <input type="email" id="edit_email_address" name="email_address" class="form-control"
                                    required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_position" class="form-label">Position</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="lni lni-briefcase"></i></span>
                                <input type="text" id="edit_position" name="position" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_employee_id" class="form-label">Employee ID</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="lni lni-id-card"></i></span>
                                <input type="text" id="edit_employee_id" name="employee_id" class="form-control"
                                    required>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="lni lni-save me-1"></i> Save Changes
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
    <script src="../../../assets/js/script.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const logoutLink = document.getElementById('logout-link');
            logoutLink.addEventListener('click', function (event) {
                const confirmLogout = confirm('Are you sure you want to log out?');
                if (!confirmLogout) {
                    event.preventDefault();
                }
            });
        });
    </script>

    <script>
        document.getElementById('print-button').addEventListener('click', function () {
            const table = document.getElementById('staff-records-table');
            const tableClone = table.cloneNode(true);

            const headerRow = tableClone.querySelector('thead tr');
            const bodyRows = tableClone.querySelectorAll('tbody tr');

            // Remove the Action column from header and body rows
            if (headerRow && headerRow.lastElementChild) {
                headerRow.removeChild(headerRow.lastElementChild);
            }
            bodyRows.forEach(row => {
                if (row && row.lastElementChild) {
                    row.removeChild(row.lastElementChild);
                }
            });

            const printWindow = window.open('', '', 'height=800,width=1000');

            printWindow.document.write('<html><head><title>Staff Performance Assessment, Recording and Keeping</title>');
            printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">');
            printWindow.document.write('<style>');
            printWindow.document.write('body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; padding: 20px; }');
            printWindow.document.write('h1 { color: #800000; font-weight: 700; margin-bottom: 25px; text-align: center; }');
            printWindow.document.write('.logo { width: 200px; height: auto; margin-bottom: 20px; }');
            printWindow.document.write('table { width: 100%; border-collapse: collapse; margin-top: 20px; }');
            printWindow.document.write('th, td { border: 1px solid #000; padding: 12px; text-align: left; }');
            printWindow.document.write('th { background-color: #800000; color: white; font-weight: 600; }');
            printWindow.document.write('tr:nth-child(even) { background-color: #f9f9f9; }');
            printWindow.document.write('tr:nth-child(odd) { background-color: #ffffff; }');
            printWindow.document.write('.position-badge { background-color: #FFD700; color: #000; font-weight: 600; padding: 5px 10px; border-radius: 20px; display: inline-block; }');
            printWindow.document.write('.employee-badge { background-color: #f5f5f5; color: #343a40; font-weight: 500; padding: 5px 10px; border-radius: 20px; display: inline-block; }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<div style="text-align: center;"><img src="../../../assets/images/spark_logo.png" alt="Spark Logo" class="logo"/></div>');
            printWindow.document.write('<h1>Staff Records</h1>');
            printWindow.document.write('<table>' + tableClone.innerHTML + '</table>');
            printWindow.document.write('<div style="text-align: center; margin-top: 30px; font-size: 12px; color: #666;">');
            printWindow.document.write('Generated on: ' + new Date().toLocaleString());
            printWindow.document.write('</div>');
            printWindow.document.write('</body></html>');

            printWindow.document.close();

            // Enhanced print handling with multiple fallbacks
            let printExecuted = false;
            let attempts = 0;
            const maxAttempts = 3;

            function executePrint() {
                if (printExecuted || attempts >= maxAttempts) return;

                attempts++;
                printExecuted = true;

                try {
                    printWindow.focus();
                    printWindow.print();
                    printWindow.close();
                } catch (error) {
                    console.warn('Print attempt failed:', error);
                    printExecuted = false; // Reset for retry

                    if (attempts < maxAttempts) {
                        setTimeout(executePrint, 500);
                    } else {
                        printWindow.close();
                        alert('Print failed. Please try again.');
                    }
                }
            }

            // Multiple approaches to trigger print
            const img = printWindow.document.querySelector('img');

            if (img) {
                // Method 1: Image load event
                img.onload = executePrint;
                img.onerror = executePrint;
            }

            // Method 2: Document ready state
            if (printWindow.document.readyState === 'complete') {
                setTimeout(executePrint, 100);
            } else {
                printWindow.document.addEventListener('DOMContentLoaded', executePrint);
            }

            // Method 3: Fallback timers
            setTimeout(executePrint, 1000);  // 1 second fallback
            setTimeout(executePrint, 3000);  // 3 second fallback (final)

            // Method 4: Window load event
            printWindow.addEventListener('load', executePrint);

            // Emergency cleanup if window is still open after 10 seconds
            setTimeout(() => {
                if (!printWindow.closed) {
                    printWindow.close();
                }
            }, 10000);
        });
    </script>

    <script>
        // Function to filter staff records
        function filterStaff() {
            const searchInput = document.getElementById('search-input').value.toLowerCase();
            const table = document.getElementById('staff-records-table');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            let recordFound = false;

            for (let i = 0; i < rows.length; i++) {
                if (rows[i].id === 'no-record') continue;

                const cells = rows[i].getElementsByTagName('td');
                let matchFound = false;

                // Search through first name, last name, contact, email, employee ID, and position
                for (let j = 0; j < cells.length - 1; j++) { // -1 to exclude action column
                    if (cells[j].textContent.toLowerCase().includes(searchInput)) {
                        matchFound = true;
                        break;
                    }
                }

                if (matchFound) {
                    rows[i].style.display = '';
                    recordFound = true;
                } else {
                    rows[i].style.display = 'none';
                }
            }

            // Show/hide "no record found" message
            const noRecordRow = document.getElementById('no-record');
            if (noRecordRow) {
                noRecordRow.style.display = recordFound ? 'none' : '';
            }
        }

        // Function to populate edit modal with staff data
        function editStaff(staffId, firstName, lastName, contactNo, emailAddress, employeeId, position) {
            document.getElementById('edit_staff_id').value = staffId;
            document.getElementById('edit_first_name').value = firstName;
            document.getElementById('edit_last_name').value = lastName;
            document.getElementById('edit_contact_no').value = contactNo;
            document.getElementById('edit_email_address').value = emailAddress;
            document.getElementById('edit_employee_id').value = employeeId;
            document.getElementById('edit_position').value = position;
        }

        document.querySelectorAll('.btn-warning').forEach(button => {
            button.addEventListener('click', function (event) {
                const staff_id = this.getAttribute('href').split('=')[1]; // Get staff_id from URL

                // Use AJAX to fetch staff details based on staff_id
                fetch('../api/get_staff_details.php?staff_id=' + staff_id)
                    .then(response => response.json())
                    .then(data => {
                        // Populate modal form fields with the fetched data
                        document.getElementById('edit_staff_id').value = data.staff_id;
                        document.getElementById('edit_first_name').value = data.first_name;
                        document.getElementById('edit_last_name').value = data.last_name;
                        document.getElementById('edit_contact_no').value = data.contact_no;
                        document.getElementById('edit_email_address').value = data.email_address;
                        document.getElementById('edit_employee_id').value = data.employee_id;
                        document.getElementById('edit_position').value = data.position;

                        // Show the modal
                        var editModal = new bootstrap.Modal(document.getElementById('editModal'));
                        editModal.show();
                    });

                event.preventDefault(); // Prevent the default action of the link
            });
        });

        function toggleOtherInput() {
            const positionSelect = document.getElementById('position');
            const otherPositionDiv = document.getElementById('otherPositionDiv');

            // If 'Other' is selected, show the text input field, else hide it
            if (positionSelect.value === 'Other') {
                otherPositionDiv.style.display = 'block';
            } else {
                otherPositionDiv.style.display = 'none';
            }
        }

        function updateDropdown() {
            const otherPositionInput = document.getElementById('otherPosition');
            const positionSelect = document.getElementById('position');

            // If the user has typed something in the input field, update the dropdown with that value
            if (otherPositionInput.value.trim() !== '') {
                // Create a new option element and set its value and text to the custom input value
                let customOption = new Option(otherPositionInput.value, otherPositionInput.value);

                // Find and remove the existing 'Other' option
                const otherOption = positionSelect.querySelector('option[value="Other"]');
                if (otherOption) {
                    otherOption.remove();
                }

                // Add the new custom option to the dropdown
                positionSelect.add(customOption);
                positionSelect.value = otherPositionInput.value; // Set the custom input as the selected value
            }
        }
    </script>
</body>

</html>