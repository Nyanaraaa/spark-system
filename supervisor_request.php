<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPARK - Supplies Request</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="supervisor_request.css?v=<?php echo filemtime('supervisor_request.css'); ?>">
</head>

<body>
    <?php include 'supervisor_navbar.php'; ?>

    <div class="main p-9">
        <div class="container">
            <h1 class="mb-4">
                <i class="lni lni-clipboard me-2" style="color: var(--gold);"></i>
                Supplies Request
            </h1>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= isset($_SESSION['msg_type']) ? $_SESSION['msg_type'] : 'info'; ?>" id="alert-message">
                    <?= htmlspecialchars($_SESSION['message']); ?>
                </div>
                <script>
                    setTimeout(function () {
                        const alertMessage = document.getElementById('alert-message');
                        if (alertMessage) {
                            alertMessage.style.display = 'none';
                        }
                    }, 5000);
                </script>
                <?php
                unset($_SESSION['message']);
                unset($_SESSION['msg_type']);
                ?>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="lni lni-hourglass me-2"></i>
                                Pending Requests
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover" id="requestsTable">
                                    <thead>
                                        <tr>
                                            <th>Staff Name</th>
                                            <th>Supplies</th>
                                            <th>Quantity</th>
                                            <th>Request Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="requestsTableBody">
                                        <!-- Table content will be loaded dynamically via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>

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

        function fetchPendingRequests() {
            fetch('get_pending_requests.php')
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.getElementById('requestsTableBody');
                    tableBody.innerHTML = ''; // Clear the table body

                    if (data.length === 0) {
                        const noRecordRow = document.createElement('tr');
                        noRecordRow.innerHTML = '<td colspan="5" class="text-center py-4"><i class="lni lni-search me-2"></i>No Request Found</td>';
                        tableBody.appendChild(noRecordRow);
                    } else {
                        data.forEach(request => {
                            const requestDate = new Date(request.request_date);
                            const formattedDate = requestDate.toLocaleString('en-US', {
                                month: 'long',
                                day: 'numeric',
                                year: 'numeric',
                                hour: 'numeric',
                                minute: 'numeric',
                                hour12: true
                            });

                            // Create a new row for the request
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>
                                    <i class="lni lni-user me-2" style="color: var(--maroon);"></i>
                                    ${request.first_name} ${request.last_name}
                                </td>
                                <td>
                                    <span class="supply-badge">
                                        <i class="lni lni-package me-1"></i>
                                        ${request.supply_name}
                                    </span>
                                </td>
                                <td>
                                    <span class="quantity-badge">
                                        ${request.quantity}
                                    </span>
                                </td>
                                <td>${formattedDate}</td>
                                <td>
                                    <form method="POST" action="approve_request.php" class="action-buttons">
                                        <input type="hidden" name="request_id" value="${request.request_id}">
                                        <button type="submit" name="action" value="approve" class="btn-approve">
                                            <i class="lni lni-checkmark me-1"></i> Approve
                                        </button>
                                        <button type="submit" name="action" value="reject" class="btn-reject">
                                            <i class="lni lni-close me-1"></i> Reject
                                        </button>
                                    </form>
                                </td>
                            `;

                            // Add the new row to the table body
                            tableBody.insertBefore(row, tableBody.firstChild);
                        });
                    }
                })
                .catch(error => console.error('Error fetching requests:', error));
        }

        // Refresh the table every 5 seconds
        setInterval(fetchPendingRequests, 5000);

        // Fetch requests when the page loads
        fetchPendingRequests();
    </script>
</body>
</html>
