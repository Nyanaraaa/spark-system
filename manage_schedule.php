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
    <title>SPARK - Manage Schedule</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="manage_schedule.css?v=<?php echo filemtime('manage_schedule.css'); ?>">
</head>

<body>
    <?php include 'supervisor_navbar.php'; ?>


    <div class="main p-9">
        <div class="container">
            <h1 class="mb-4">
                <i class="lni lni-calendar me-2" style="color: var(--gold);"></i>
                Manage Schedule
            </h1>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="lni lni-eye me-2"></i>
                                View All Schedules
                            </h5>
                        </div>
                        <div class="card-body">
                            <button class="btn btn-primary" id="viewAllSchedulesBtn" data-bs-toggle="modal"
                                data-bs-target="#viewAllSchedulesModal">
                                <i class="lni lni-calendar-check me-2"></i>View All Schedules
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="lni lni-user me-2"></i>
                                Staff Schedule
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label for="staffNameDropdown" class="form-label">Select Staff Member</label>
                                <select class="form-select" id="staffNameDropdown" required>
                                    <option value="" disabled selected>Select a staff</option>
                                </select>
                            </div>
                            <div id="scheduleList" class="list-group">
                                <!-- The schedule entries will be populated here -->
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
                                <i class="lni lni-plus me-2"></i>
                                Create Schedule
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="alert-container" class="mt-3"></div>
                            <form id="scheduleForm">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header bg-maroon">
                                                <h6 class="mb-0">Select Days</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-check mb-2">
                                                            <input type="checkbox" class="form-check-input" id="monday"
                                                                value="M">
                                                            <label class="form-check-label" for="monday">Monday</label>
                                                        </div>
                                                        <div class="form-check mb-2">
                                                            <input type="checkbox" class="form-check-input" id="tuesday"
                                                                value="T">
                                                            <label class="form-check-label"
                                                                for="tuesday">Tuesday</label>
                                                        </div>
                                                        <div class="form-check mb-2">
                                                            <input type="checkbox" class="form-check-input"
                                                                id="wednesday" value="W">
                                                            <label class="form-check-label"
                                                                for="wednesday">Wednesday</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input"
                                                                id="thursday" value="Th">
                                                            <label class="form-check-label"
                                                                for="thursday">Thursday</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check mb-2">
                                                            <input type="checkbox" class="form-check-input" id="friday"
                                                                value="F">
                                                            <label class="form-check-label" for="friday">Friday</label>
                                                        </div>
                                                        <div class="form-check mb-2">
                                                            <input type="checkbox" class="form-check-input"
                                                                id="saturday" value="Sat">
                                                            <label class="form-check-label"
                                                                for="saturday">Saturday</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" id="sunday"
                                                                value="S">
                                                            <label class="form-check-label" for="sunday">Sunday</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header bg-maroon">
                                                <h6 class="mb-0">Location Assignment</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="location" class="form-label">Assigned Location</label>
                                                    <select class="form-select" id="location" required>
                                                        <option value="" disabled selected>Select a location</option>
                                                    </select>
                                                </div>
                                                <input type="hidden" id="staffId">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header bg-maroon">
                                                <h6 class="mb-0">Shift Time</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="shiftTime" class="form-label">Shift Start Time</label>
                                                    <div class="d-flex align-items-center">
                                                        <input type="number" class="form-control me-2" id="startHour"
                                                            min="1" max="12" value="9" style="width: 80px;" required>
                                                        <input type="number" class="form-control me-2" id="startMinute"
                                                            min="0" max="59" value="00" style="width: 80px;" required>
                                                        <select class="form-select" id="startPeriod"
                                                            style="width: 100px;" required>
                                                            <option value="AM" selected>AM</option>
                                                            <option value="PM">PM</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="mb-0">
                                                    <label for="shiftTime" class="form-label">Shift End Time</label>
                                                    <div class="d-flex align-items-center">
                                                        <input type="number" class="form-control me-2" id="endHour"
                                                            min="1" max="12" value="5" style="width: 80px;" required>
                                                        <input type="number" class="form-control me-2" id="endMinute"
                                                            min="0" max="59" value="00" style="width: 80px;" required>
                                                        <select class="form-select" id="endPeriod" style="width: 100px;"
                                                            required>
                                                            <option value="AM">AM</option>
                                                            <option value="PM" selected>PM</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <div class="card">
                                            <div class="card-header bg-maroon">
                                                <h6 class="mb-0">Break Time</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="breakTime" class="form-label">Break Start Time</label>
                                                    <div class="d-flex align-items-center">
                                                        <input type="number" class="form-control me-2"
                                                            id="breakStartHour" min="1" max="12" value="12"
                                                            style="width: 80px;" required>
                                                        <input type="number" class="form-control me-2"
                                                            id="breakStartMinute" min="0" max="59" value="00"
                                                            style="width: 80px;" required>
                                                        <select class="form-select" id="breakStartPeriod"
                                                            style="width: 100px;" required>
                                                            <option value="AM">AM</option>
                                                            <option value="PM" selected>PM</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="mb-0">
                                                    <label for="breakTime" class="form-label">Break End Time</label>
                                                    <div class="d-flex align-items-center">
                                                        <input type="number" class="form-control me-2" id="breakEndHour"
                                                            min="1" max="12" value="1" style="width: 80px;" required>
                                                        <input type="number" class="form-control me-2"
                                                            id="breakEndMinute" min="0" max="59" value="00"
                                                            style="width: 80px;" required>
                                                        <select class="form-select" id="breakEndPeriod"
                                                            style="width: 100px;" required>
                                                            <option value="AM">AM</option>
                                                            <option value="PM" selected>PM</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="lni lni-save me-2"></i> Save Schedule
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View All Schedules Modal -->
    <div class="modal fade" id="viewAllSchedulesModal" tabindex="-1" aria-labelledby="viewAllSchedulesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAllSchedulesModalLabel">
                        <i class="lni lni-calendar me-2"></i>
                        All Schedules
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="lni lni-search"></i>
                            </span>
                            <input type="text" id="scheduleSearchInput" class="form-control"
                                placeholder="Search by name, location, position or day">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover" id="scheduletable">
                            <thead>
                                <tr>
                                    <th width="15%">Location</th>
                                    <th width="15%">Building</th>
                                    <th width="20%">Staff Name</th>
                                    <th width="15%">Position</th>
                                    <th width="10%">Day</th>
                                    <th width="15%">Shift Time</th>
                                    <th width="15%">Break Time</th>
                                </tr>
                            </thead>
                            <tbody id="allSchedulesBody">
                                <!-- Table rows will be populated here -->
                            </tbody>
                        </table>
                        <div id="emptyState" style="display: none;" class="text-center py-4">
                            <i class="lni lni-search-alt" style="font-size: 2rem; color: var(--maroon-light);"></i>
                            <p class="mt-2 mb-0">No matching records found</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <span id="paginationInfo" class="text-muted me-3">Showing <strong>1-10</strong> of
                            <strong>0</strong> schedules</span>
                        <div class="btn-group" role="group" aria-label="Pagination">
                            <button type="button" id="prevPageBtn" class="btn btn-outline-secondary">
                                <i class="lni lni-chevron-left"></i>
                            </button>
                            <button type="button" id="nextPageBtn" class="btn btn-outline-secondary">
                                <i class="lni lni-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    <button class="btn btn-primary" id="printLocationsBtn">
                        <i class="lni lni-printer me-2"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        let allSchedulesData = [];
        let currentPage = 1;
        const pageSize = 10;

        function renderSchedulesTable(page = 1) {
            const allSchedulesBody = document.getElementById('allSchedulesBody');
            allSchedulesBody.innerHTML = '';

            const total = allSchedulesData.length;
            const start = (page - 1) * pageSize;
            const end = Math.min(start + pageSize, total);

            // Render only the current page
            for (let i = start; i < end; i++) {
                const schedule = allSchedulesData[i];
                allSchedulesBody.innerHTML += `
                    <tr>
                        <td>
                            <i class="lni lni-map-marker me-2" style="color: var(--maroon);"></i>
                            ${schedule.location}
                        </td>
                        <td>
                            <i class="lni lni-apartment me-2" style="color: var(--gold-dark);"></i>
                            ${schedule.building}
                        </td>
                        <td>
                            <i class="lni lni-user me-2" style="color: var(--maroon);"></i>
                            ${schedule.staff_name}
                        </td>
                        <td>${schedule.position}</td>
                        <td>
                            <span class="badge bg-secondary">
                                <i class="lni lni-calendar me-1"></i>
                                ${schedule.days}
                            </span>
                        </td>
                        <td>
                            <i class="lni lni-timer me-2" style="color: var(--maroon);"></i>
                            ${schedule.shift_time}
                        </td>
                        <td>
                            <i class="lni lni-coffee-cup me-2" style="color: var(--maroon);"></i>
                            ${schedule.break_time || 'N/A'}
                        </td>
                    </tr>
                `;
            }

            // Update the pagination text
            document.getElementById('paginationInfo').innerHTML =
                `Showing <strong>${total === 0 ? 0 : start + 1}-${end}</strong> of <strong>${total}</strong> schedules`;

            // Enable/disable buttons
            document.getElementById('prevPageBtn').disabled = page === 1;
            document.getElementById('nextPageBtn').disabled = end >= total;

            // Show/hide empty state
            document.getElementById('emptyState').style.display = total === 0 ? 'block' : 'none';
        }

        document.getElementById('viewAllSchedulesBtn').addEventListener('click', function () {
            fetch('get_all_schedules.php')
                .then(response => response.json())
                .then(data => {
                    allSchedulesData = data;
                    currentPage = 1;
                    renderSchedulesTable(currentPage);
                })
                .catch(error => {
                    console.error('Error fetching all schedules:', error);
                    document.getElementById('allSchedulesBody').innerHTML = '<tr><td colspan="7" class="text-center">Error fetching data</td></tr>';
                });
        });

        document.getElementById('prevPageBtn').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                renderSchedulesTable(currentPage);
            }
        });
        document.getElementById('nextPageBtn').addEventListener('click', function () {
            if ((currentPage * pageSize) < allSchedulesData.length) {
                currentPage++;
                renderSchedulesTable(currentPage);
            }
        });
    </script>

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

        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const staffNameDropdown = document.getElementById('staffNameDropdown');

            // Fetch staff names and populate the dropdown
            fetch('get_staff_names.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (Array.isArray(data)) {
                        data.forEach(staff => {
                            const option = document.createElement('option');
                            option.value = staff.staff_id;
                            option.textContent = staff.name;
                            staffNameDropdown.appendChild(option);
                        });

                        // Initialize Select2 after populating options
                        $('#staffNameDropdown').select2({
                            placeholder: "Select a staff",
                            width: 'resolve'
                        });

                        // Attach the change event listener to the Select2 dropdown
                        $('#staffNameDropdown').on('change', function () {
                            const selectedStaffId = $(this).val();
                            if (selectedStaffId) {
                                fetchSchedules(selectedStaffId);
                            }
                        });
                    } else {
                        console.error('Unexpected data format:', data);
                        alert('Error loading staff names. Please try again later.');
                    }
                })
                .catch(error => {
                    console.error('Error fetching staff names:', error);
                    alert('Unable to fetch staff names. Please try again later.');
                });
        });

        // Function to fetch and display the schedule for the selected staff
        function fetchSchedules(staffId) {
            const scheduleList = document.getElementById('scheduleList');
            fetch(`get_schedules.php?staff_id=${staffId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (Array.isArray(data) && data.length > 0) {
                        scheduleList.innerHTML = `
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Days</th>
                                            <th>Shift Time</th>
                                            <th>Location</th>
                                            <th>Building</th>
                                            <th>Break Time</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${data.map(schedule => `
                                            <tr data-schedule-id="${schedule.schedule_id}" 
                                                data-days="${schedule.days}" 
                                                data-shift-time="${schedule.shift_time}" 
                                                data-location="${schedule.location}" 
                                                data-building="${schedule.building || 'N/A'}" 
                                                data-break-time="${schedule.break_time || 'N/A'}">
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <i class="lni lni-calendar me-1"></i>
                                                        ${schedule.days}
                                                    </span>
                                                </td>
                                                <td>
                                                    <i class="lni lni-timer me-2" style="color: var(--maroon);"></i>
                                                    ${schedule.shift_time}
                                                </td>
                                                <td>
                                                    <i class="lni lni-map-marker me-2" style="color: var(--maroon);"></i>
                                                    ${schedule.location}
                                                </td>
                                                <td>
                                                    <i class="lni lni-apartment me-2" style="color: var(--gold-dark);"></i>
                                                    ${schedule.building || 'N/A'}
                                                </td>
                                                <td>
                                                    <i class="lni lni-coffee-cup me-2" style="color: var(--maroon);"></i>
                                                    ${schedule.break_time || 'N/A'}
                                                </td>
                                                <td>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteSchedule(${schedule.schedule_id})">
                                                        <i class="lni lni-trash me-1"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        `;
                    } else {
                        scheduleList.innerHTML = `
                            <div class="alert alert-info">
                                <i class="lni lni-information me-2"></i>
                                No schedule found for this staff member.
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error fetching schedules:', error);
                    scheduleList.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="lni lni-warning me-2"></i>
                            Unable to fetch schedule. Please try again later.
                        </div>
                    `;
                });
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const locationSelect = document.getElementById('location');
            const alertContainer = document.getElementById('alert-container');
            const scheduleForm = document.getElementById('scheduleForm');

            // Fetch locations and populate the dropdown
            fetch('get_location.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (Array.isArray(data)) {
                        data.forEach(location => {
                            const option = document.createElement('option');
                            option.value = location.location_name;
                            option.textContent = location.location_name;
                            locationSelect.appendChild(option);
                        });
                    } else {
                        console.error('Unexpected data format:', data);
                        showAlert('Error loading locations. Please try again later.', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error fetching locations:', error);
                    showAlert('Unable to fetch locations. Please try again later.', 'danger');
                });

            // Function to display alert messages
            function showAlert(message, type = 'success') {
                alertContainer.innerHTML = `
                    <div class="alert alert-${type}" role="alert">
                        <i class="lni lni-${type === 'success' ? 'checkmark-circle' : 'warning'} me-2"></i>
                        ${message}
                    </div>
                `;
                setTimeout(() => {
                    alertContainer.innerHTML = '';
                }, 3000);
            }
        });

        // Handle schedule form submission
        document.getElementById('scheduleForm').addEventListener('submit', function (event) {
            event.preventDefault();

            const staffId = document.getElementById('staffNameDropdown').value.trim();
            const selectedDays = Array.from(
                document.querySelectorAll('#scheduleForm input[type="checkbox"]:checked')
            ).map(checkbox => checkbox.value);

            const startHour = document.getElementById('startHour').value.trim();
            const startMinute = document.getElementById('startMinute').value.trim();
            const startPeriod = document.getElementById('startPeriod').value.trim();
            const endHour = document.getElementById('endHour').value.trim();
            const endMinute = document.getElementById('endMinute').value.trim();
            const endPeriod = document.getElementById('endPeriod').value.trim();

            const breakStartHour = document.getElementById('breakStartHour').value.trim();
            const breakStartMinute = document.getElementById('breakStartMinute').value.trim();
            const breakStartPeriod = document.getElementById('breakStartPeriod').value.trim();
            const breakEndHour = document.getElementById('breakEndHour').value.trim();
            const breakEndMinute = document.getElementById('breakEndMinute').value.trim();
            const breakEndPeriod = document.getElementById('breakEndPeriod').value.trim();

            const shiftTime = `${startHour}:${startMinute} ${startPeriod} - ${endHour}:${endMinute} ${endPeriod}`;
            const breakTime = `${breakStartHour}:${breakStartMinute} ${breakStartPeriod} - ${breakEndHour}:${breakEndMinute} ${breakEndPeriod}`;
            const location = document.getElementById('location').value.trim();

            const requestData = JSON.stringify({
                staff_id: staffId,
                days: selectedDays.join(','),
                shift_time: shiftTime,
                break_time: breakTime,
                location: location,
            });

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'save_schedule.php', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alert(response.success);

                            const schedule = {
                                staff_id: staffId,
                                schedule_id: response.schedule_id,
                                days: selectedDays.join(','),
                                shift_time: shiftTime,
                                break_time: breakTime,
                                location: location,
                            };

                            // Refresh the schedule list
                            fetchSchedules(staffId);
                        } else {
                            alert(response.error || 'Failed to save schedule.');
                        }
                    } catch (error) {
                        console.error('Error parsing response:', error, xhr.responseText);
                        alert('There is already an existing schedule for this staff member.');
                    }
                } else {
                    console.error('Server error:', xhr.responseText);
                    alert('Error saving schedule. Please try again.');
                }
            };
            xhr.onerror = function () {
                alert('Request failed. Please check your network connection.');
            };
            xhr.send(requestData);
        });

        function deleteSchedule(scheduleId) {
            if (confirm('Are you sure you want to delete this schedule?')) {
                fetch('delete_schedule.php?schedule_id=' + scheduleId)
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);

                        // Refresh the schedule list after deleting
                        const selectedStaffId = document.getElementById('staffNameDropdown').value;
                        fetchSchedules(selectedStaffId);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the schedule.');
                    });
            }
        }
    </script>

    <script>
        document.getElementById('scheduleSearchInput').addEventListener('input', function () {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#scheduletable tbody tr');
            let recordFound = false;

            tableRows.forEach(row => {
                const location = row.cells[0].textContent.toLowerCase();
                const building = row.cells[1].textContent.toLowerCase();
                const staffName = row.cells[2].textContent.toLowerCase();
                const position = row.cells[3].textContent.toLowerCase();
                const day = row.cells[4].textContent.toLowerCase();
                const shiftTime = row.cells[5].textContent.toLowerCase();
                const breakTime = row.cells[6].textContent.toLowerCase();

                // Check if the search value matches any column
                if (
                    location.includes(searchValue) ||
                    building.includes(searchValue) ||
                    staffName.includes(searchValue) ||
                    position.includes(searchValue) ||
                    day.includes(searchValue) ||
                    shiftTime.includes(searchValue) ||
                    breakTime.includes(searchValue)
                ) {
                    row.style.display = '';
                    recordFound = true;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show or hide "No records found" message
            document.getElementById('emptyState').style.display = recordFound ? 'none' : 'block';
        });
    </script>

    <script>
        document.getElementById('printLocationsBtn').addEventListener('click', function () {
            const table = document.getElementById('scheduletable');
            const tableClone = table.cloneNode(true);

            const searchInput = document.getElementById('scheduleSearchInput').value.trim();
            const headerRow = tableClone.querySelector('thead tr');
            const bodyRows = tableClone.querySelectorAll('tbody tr');

            // Identify the index of the "Staff Name" column
            const headerColumns = Array.from(headerRow.children);
            const staffNameIndex = headerColumns.findIndex(th => th.textContent.trim() === 'Staff Name');

            // Collect all the Staff Names for the filtered rows
            let staffNames = [];
            if (searchInput !== '') {
                staffNames = Array.from(bodyRows)
                    .filter(row => row.style.display !== 'none') // Only consider visible rows
                    .map(row => row.children[staffNameIndex]?.textContent.trim()) // Get Staff Name from the column
                    .filter(name => name); // Remove any empty names

                // Ensure that only unique names are displayed
                staffNames = [...new Set(staffNames)];

                // Remove the "Staff Name" column if there is a search input
                if (staffNameIndex > -1) {
                    headerRow.removeChild(headerRow.children[staffNameIndex]);
                    bodyRows.forEach(row => {
                        if (row.children[staffNameIndex]) {
                            row.removeChild(row.children[staffNameIndex]);
                        }
                    });
                }
            }

            // Open a new window for printing
            const printWindow = window.open('', '', 'height=800,width=1000');

            // Write content to the new window
            printWindow.document.write('<html><head><title>Staff Schedule</title>');
            printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">');
            printWindow.document.write('<style>body { padding: 20px; } .header { margin-bottom: 20px; } .logo { width: 200px; height: auto; } h1 { color: #800000; margin-top: 20px; } table { width: 100%; border-collapse: collapse; } th { background-color: #800000; color: white; } </style>');
            printWindow.document.write('</head><body>');

            printWindow.document.write('<div class="header">');
            printWindow.document.write('<img src="spark_logo.png" alt="Spark Logo" class="logo"/>');
            printWindow.document.write('<h1>Staff Schedule</h1>');
            printWindow.document.write('</div>');

            // Add the "Schedule for" header only if a search was performed and staff names were found
            if (searchInput !== '' && staffNames.length > 0) {
                printWindow.document.write(`<div class="staff-info mb-4 p-3 bg-light rounded">`);
                printWindow.document.write(`<h4 style="color: #800000;">Schedule for: <span style="font-weight: normal;">${staffNames.join(', ')}</span></h4>`);
                printWindow.document.write('</div>');
            }

            // Add the table (with or without "Staff Name" column)
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