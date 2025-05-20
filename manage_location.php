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
    <title>SPARK - Manage Location</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="manage_location.css?v=<?php echo filemtime('manage_location.css'); ?>">
</head>

<body>
    <?php include 'supervisor_navbar.php'; ?>

    <div class="main p-9">
        <div class="container">
            <h1 class="mb-4">
                <i class="lni lni-map me-2" style="color: var(--gold);"></i>
                Manage Location
            </h1>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="lni lni-search me-2"></i>
                                View All Locations
                            </h5>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-primary" id="viewLocationsBtn" data-bs-toggle="modal" data-bs-target="#viewLocationsModal">
                                <i class="lni lni-eye me-2"></i>View All Locations
                            </button>
                            <div id="errorMessageContainer" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="lni lni-plus me-2"></i>
                                Add New Location
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="addLocationForm">
                                <div class="mb-3">
                                    <label for="newBuildingInput" class="form-label">Building Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="lni lni-apartment"></i>
                                        </span>
                                        <input type="text" class="form-control" id="newBuildingInput" name="building" placeholder="Enter building name">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="newLocationInput" class="form-label">Location Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="lni lni-map-marker"></i>
                                        </span>
                                        <input type="text" class="form-control" id="newLocationInput" name="location" placeholder="Enter location name" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary" id="saveNewLocation">
                                    <i class="lni lni-plus me-2"></i>Add Location
                                </button>
                            </form>
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
                                Locations
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <ul id="locationList" class="list-group">
                                <!-- Locations will be populated here -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Locations Modal -->
    <div class="modal fade" id="viewLocationsModal" tabindex="-1" aria-labelledby="viewLocationsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewLocationsModalLabel">
                        <i class="lni lni-map me-2"></i>
                        Locations and Staff
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="lni lni-search"></i>
                            </span>
                            <input
                                type="text"
                                id="searchInput"
                                class="form-control"
                                placeholder="Search by building, location or staff"
                                onkeyup="filterLocations()">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="locationsTable">
                            <thead>
                                <tr>
                                    <th width="20%">Building</th>
                                    <th width="25%">Location Name</th>
                                    <th width="40%">Assigned Staff</th>
                                    <th width="15%">Number of Assigned Staff</th>
                                </tr>
                            </thead>
                            <tbody id="locationsTableBody">
                                <!-- Table rows will be populated here -->
                            </tbody>
                        </table>
                        <div id="noRecordMessage" style="display: none;" class="text-center py-4">
                            <i class="lni lni-search-alt" style="font-size: 2rem; color: var(--maroon-light);"></i>
                            <p class="mt-2 mb-0">No matching records found</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" id="printLocationsBtn">
                        <i class="lni lni-printer me-2"></i>Print
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="lni lni-close me-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>

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
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const newLocationInput = document.getElementById('newLocationInput');
            const newBuildingInput = document.getElementById('newBuildingInput');
            const saveNewLocation = document.getElementById('saveNewLocation');
            const locationList = document.getElementById('locationList');
            const viewLocationsBtn = document.getElementById('viewLocationsBtn');
            const locationsTableBody = document.getElementById('locationsTableBody');

            // Fetch locations on page load
            fetchLocations();

            // Add event listener for form submission
            document.getElementById('addLocationForm').addEventListener('submit', function(event) {
                event.preventDefault();
                
                const newLocation = newLocationInput.value.trim();
                const newBuilding = newBuildingInput.value.trim();

                if (newLocation) { // Only check if the location is provided
                    fetch('add_location.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `location=${encodeURIComponent(newLocation)}&building=${encodeURIComponent(newBuilding)}`,
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                // Add new location to the list
                                const li = createLocationListItem(data.id, newLocation, newBuilding || 'No Building'); // Show "No Building" if empty
                                locationList.appendChild(li);

                                // Clear the input fields
                                newLocationInput.value = '';
                                newBuildingInput.value = '';
                            } else {
                                alert(data.message);
                            }
                        });
                } else {
                    alert('Please enter a valid location.');
                }
            });

            // Fetch and display locations
            function fetchLocations() {
                fetch('get_locations.php')
                    .then(response => response.json())
                    .then(data => {
                        locationList.innerHTML = '';
                        if (data.status === 'success') {
                            data.locations.forEach(location => {
                                const li = createLocationListItem(location.id, location.location_name, location.building);
                                locationList.appendChild(li);
                            });
                        } else {
                            locationList.innerHTML = '<li class="list-group-item">No locations available.</li>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching locations:', error);
                    });
            }

            // Create location list item
            function createLocationListItem(id, name, building) {
                const li = document.createElement('li');
                li.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');
                
                const locationInfo = document.createElement('div');
                locationInfo.innerHTML = `
                    <i class="lni lni-map-marker me-2" style="color: var(--maroon);"></i>
                    <span class="fw-medium">${building || 'No Building'}</span> - ${name}
                `;
                li.appendChild(locationInfo);

                const deleteBtn = document.createElement('button');
                deleteBtn.classList.add('btn', 'btn-danger', 'btn-sm');
                deleteBtn.innerHTML = '<i class="lni lni-trash me-1"></i> Delete';
                deleteBtn.addEventListener('click', () => deleteLocation(id, li));

                li.appendChild(deleteBtn);
                return li;
            }

            // Delete location
            function deleteLocation(locationId, listItem) {
                if (confirm('Are you sure you want to delete this location?')) {
                    fetch('delete_location.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `location_id=${encodeURIComponent(locationId)}`
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                listItem.remove();
                                alert('Location deleted successfully!');
                            } else {
                                alert('Failed to delete location. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting location:', error);
                        });
                }
            }

            // View all locations in a table
            viewLocationsBtn.addEventListener('click', () => {
                fetch('get_locations_with_staff.php')
                    .then(response => response.json())
                    .then(data => {
                        locationsTableBody.innerHTML = '';

                        if (data.status === 'success') {
                            data.locations.forEach(location => {
                                const row = document.createElement('tr');

                                const buildingCell = document.createElement('td');
                                buildingCell.innerHTML = `
                                    <i class="lni lni-apartment me-2" style="color: var(--maroon);"></i>
                                    ${location.building || 'No Building'}
                                `;
                                row.appendChild(buildingCell);

                                const locationNameCell = document.createElement('td');
                                locationNameCell.innerHTML = `
                                    <i class="lni lni-map-marker me-2" style="color: var(--gold-dark);"></i>
                                    ${location.location_name}
                                `;
                                row.appendChild(locationNameCell);

                                const staffCell = document.createElement('td');
                                const staffList = document.createElement('ul');
                                staffList.classList.add('list-unstyled', 'mb-0');
                                
                                if (location.assigned_staff.length > 0) {
                                    location.assigned_staff.forEach(staff => {
                                        const listItem = document.createElement('li');
                                        listItem.innerHTML = `
                                            <i class="lni lni-user me-2" style="color: var(--maroon);"></i>
                                            ${staff}
                                        `;
                                        staffList.appendChild(listItem);
                                    });
                                } else {
                                    const listItem = document.createElement('li');
                                    listItem.innerHTML = '<em>No staff assigned</em>';
                                    staffList.appendChild(listItem);
                                }
                                
                                staffCell.appendChild(staffList);
                                row.appendChild(staffCell);

                                const staffCountCell = document.createElement('td');
                                staffCountCell.innerHTML = `
                                    <span class="badge bg-secondary">
                                        <i class="lni lni-users me-1"></i>
                                        ${location.assigned_staff.length}
                                    </span>
                                `;
                                row.appendChild(staffCountCell);

                                locationsTableBody.appendChild(row);
                            });
                        } else {
                            locationsTableBody.innerHTML = '<tr><td colspan="4" class="text-center py-4">No locations found.</td></tr>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching locations with staff:', error);
                    });
            });
        });
    </script>

    <script>
        function filterLocations() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#locationsTable tbody tr');
            let recordFound = false;

            rows.forEach(row => {
                const building = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
                const locationName = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                const assignedStaff = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                
                if (
                    building.includes(searchInput) ||
                    locationName.includes(searchInput) ||
                    assignedStaff.includes(searchInput)
                ) {
                    row.style.display = '';
                    recordFound = true;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show or hide "No records found" message
            const noRecordMessage = document.getElementById('noRecordMessage');
            if (noRecordMessage) {
                if (recordFound) {
                    noRecordMessage.style.display = 'none';
                } else {
                    noRecordMessage.style.display = 'block';
                }
            }
        }
    </script>

    <script>
        document.getElementById('printLocationsBtn').addEventListener('click', function () {
            const table = document.getElementById('locationsTable');
            const tableClone = table.cloneNode(true);

            // Open a new window for printing
            const printWindow = window.open('', '', 'height=800,width=1000');

            // Write content to the new window
            printWindow.document.write('<html><head><title>Locations and Staff</title>');
            printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">');
            printWindow.document.write('<style>body { padding: 20px; } .header { margin-bottom: 20px; } .logo { width: 200px; height: auto; } h1 { color: #800000; margin-top: 20px; } table { width: 100%; border-collapse: collapse; } th { background-color: #800000; color: white; } </style>');
            printWindow.document.write('</head><body>');
            
            printWindow.document.write('<div class="header">');
            printWindow.document.write('<img src="spark_logo.png" alt="Spark Logo" class="logo"/>');
            printWindow.document.write('<h1>Locations and Staff</h1>');
            printWindow.document.write('</div>');
            
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
</body>
</html>