<?php
session_start();
require '../../../config/database.php';

if (!isset($_SESSION['staff_id'])) {
    echo "Access denied. Please log in.";
    exit();
}

$stmt = $conn->prepare("SELECT staff_id, first_name, last_name FROM staff WHERE staff_id = ?");
$stmt->bind_param("i", $_SESSION['staff_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $staff = $result->fetch_assoc();
    $staff_id = $staff['staff_id'];
    $first_name = $staff['first_name'];
    $last_name = $staff['last_name'];
} else {
    echo "No staff record found.";
    exit();
}

$schedule_stmt = $conn->prepare("
    SELECT 
        staff_schedule.days, 
        staff_schedule.shift_time, 
        staff_schedule.location, 
        staff_schedule.break_time, 
        location.building
    FROM 
        staff_schedule
    INNER JOIN 
        location 
    ON 
        staff_schedule.location = location.location_name
    WHERE 
        staff_schedule.staff_id = ?
");

$schedule_stmt->bind_param("i", $staff_id);
$schedule_stmt->execute();
$schedule_result = $schedule_stmt->get_result();

$leaderboard_stmt = $conn->prepare("
    SELECT evaluations.employee_id, 
           CONCAT(first_name, ' ', last_name) AS full_name, 
           SUM(rating) AS total_rating 
    FROM evaluations 
    JOIN staff ON evaluations.employee_id = staff.employee_id 
    GROUP BY evaluations.employee_id
");

$leaderboard_stmt->execute();
$leaderboard_result = $leaderboard_stmt->get_result();

$max_rating = 0;
$top_performers = [];

if ($leaderboard_result->num_rows > 0) {
    while ($row = $leaderboard_result->fetch_assoc()) {
        if ($row['total_rating'] > $max_rating) {
            $max_rating = $row['total_rating'];
            $top_performers = [$row];
        } elseif ($row['total_rating'] == $max_rating) {
            $top_performers[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPARK-Housekeeping Staff</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link rel="manifest" href="../../../manifest.json">
    <link rel="stylesheet"
        href="../../../assets/css/dashboard.css?v=<?php echo filemtime('../../../assets/css/dashboard.css'); ?>">
</head>

<body>
    <?php include '../../../components/navbar/staff_navbar.php'; ?>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                <!-- Welcome Header -->
                <h1 class="welcome-header">Welcome, <?php echo $first_name; ?></h1>

                <!-- Dashboard Content -->
                <div class="row">
                    <!-- Recent Reports Card -->
                    <div class="col-12 col-lg-8 mb-4">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="lni lni-files me-2"></i>Recent Reports
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Report</th>
                                                <th>Submitted At</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            require '../../../config/database.php';

                                            $employee_id = $_SESSION['employee_id'];

                                            $stmt = $conn->prepare("SELECT report_id, report_image, created_at, description FROM progress_reports WHERE employee_id = ? ORDER BY created_at DESC LIMIT 10");
                                            $stmt->bind_param("s", $employee_id);
                                            $stmt->execute();
                                            $reports_result = $stmt->get_result();
                                            $stmt->close();

                                            if ($reports_result->num_rows > 0) {
                                                while ($row = $reports_result->fetch_assoc()) {
                                                    $formattedDate = date("F j, Y g:ia", strtotime($row["created_at"]));

                                                    echo "<tr>
                                                        <td>
                                                            <button class='btn btn-primary btn-sm me-2' data-bs-toggle='modal' data-bs-target='#imageModal' onclick='previewImage(\"{$row['report_image']}\")'>
                                                                View
                                                            </button>
                                                            <button class='btn btn-info btn-sm view-assessment' data-report-id=\"{$row['report_id']}\" data-bs-toggle='modal' data-bs-target='#viewAssessmentModal'>
                                                            Assessment
                                                            </button>
                                                        </td>
                                                        <td>{$formattedDate}</td>
                                                        <td>{$row['description']}</td>
                                                    </tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='3' class='text-center'>No reports available</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Card -->
                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="lni lni-calendar me-2"></i>Calendar
                                </h5>
                            </div>
                            <div class="card-body p-2">
                                <div id='calendar' class="calendar-container"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Card -->
                    <div class="col-12 col-lg-8 mb-4">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="lni lni-timer me-2"></i>My Schedule
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Days</th>
                                                <th>Shift Time</th>
                                                <th>Break Time</th>
                                                <th>Location</th>
                                                <th>Building</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($schedule_result->num_rows > 0) {
                                                while ($row = $schedule_result->fetch_assoc()) {
                                                    echo "<tr>
                                                        <td>{$row['days']}</td>
                                                        <td>{$row['shift_time']}</td>
                                                        <td>{$row['break_time']}</td>
                                                        <td>{$row['location']}</td>
                                                        <td>{$row['building']}</td>
                                                    </tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='5' class='text-center'>No schedule found</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Leaderboard Card -->
                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <div class="dashboard-card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="lni lni-crown me-2"></i>Leaderboard
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($top_performers)): ?>
                                    <div class="leaderboard-content">
                                        <h6 class="mb-3">Top Performers</h6>
                                        <?php foreach ($top_performers as $performer): ?>
                                            <div class="top-performer">
                                                <div class="performer-name">
                                                    <i class="lni lni-user me-2"></i>
                                                    <?php echo htmlspecialchars($performer['full_name']); ?>
                                                </div>
                                                <div class="performer-rating mt-2">
                                                    <i class="lni lni-star-filled me-1" style="color: #800000;"></i>
                                                    Rating: <?php echo htmlspecialchars($performer['total_rating']); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="lni lni-empty-file" style="font-size: 2rem; color: #6c757d;"></i>
                                        <p class="mt-2">No ratings available yet.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">
                        <i class="lni lni-image me-2"></i>Image Preview
                    </h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img id="imagePreview" src="/placeholder.svg" alt="Report Image" class="img-fluid">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Modal -->
    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scheduleModalLabel">
                        <i class="lni lni-calendar me-2"></i>Schedule Details
                    </h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="scheduleModalBody">
                    <!-- Schedule details will be populated here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assessment Details Modal -->
    <div class="modal fade" id="viewAssessmentModal" tabindex="-1" aria-labelledby="viewAssessmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAssessmentModalLabel">
                        <i class="lni lni-star-filled me-2"></i>Assessment Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="dashboard-card mb-3">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="lni lni-star me-2"></i>Overall Rating
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    <div class="rating-display" id="ratingDisplay">-</div>
                                    <div class="text-muted">Performance Score</div>
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-center">
                                            <div class="px-2">
                                                <i class="lni lni-star-filled" style="color: var(--gold);"></i>
                                            </div>
                                            <div class="px-2">
                                                <i class="lni lni-star-filled" style="color: var(--gold);"></i>
                                            </div>
                                            <div class="px-2">
                                                <i class="lni lni-star-filled" style="color: var(--gold);"></i>
                                            </div>
                                            <div class="px-2">
                                                <i class="lni lni-star-filled" style="color: var(--gold);"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-muted mt-2" id="evaluationTime">Evaluated on: <span>-</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="dashboard-card mb-3">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="lni lni-list me-2"></i>Evaluation Criteria
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <ul class="criteria-list">
                                        <li class="criteria-item">
                                            <span class="criteria-label">
                                                <i class="lni lni-checkmark me-2" style="color: var(--maroon);"></i>Task
                                                Completion
                                            </span>
                                            <span class="criteria-score" id="criteria-task_completion">-</span>
                                        </li>
                                        <li class="criteria-item">
                                            <span class="criteria-label">
                                                <i class="lni lni-eye me-2" style="color: var(--maroon);"></i>Attention
                                                to Detail
                                            </span>
                                            <span class="criteria-score" id="criteria-attention_to_detail">-</span>
                                        </li>
                                        <li class="criteria-item">
                                            <span class="criteria-label">
                                                <i class="lni lni-cart me-2" style="color: var(--maroon);"></i>Trash
                                                Management
                                            </span>
                                            <span class="criteria-score" id="criteria-trash_management">-</span>
                                        </li>
                                        <li class="criteria-item">
                                            <span class="criteria-label">
                                                <i class="lni lni-brush me-2" style="color: var(--maroon);"></i>Floor
                                                Care
                                            </span>
                                            <span class="criteria-score" id="criteria-floor_care">-</span>
                                        </li>
                                        <li class="criteria-item">
                                            <span class="criteria-label">
                                                <i class="lni lni-grid-alt me-2"
                                                    style="color: var(--maroon);"></i>Organization
                                            </span>
                                            <span class="criteria-score" id="criteria-organization">-</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="dashboard-card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="lni lni-comments me-2"></i>Supervisor Remarks
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="remarks-container" id="remarksText">No remarks provided.</div>
                                </div>
                            </div>
                        </div>
                    </div>
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
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'today'
                },

            });
            calendar.render();

            const logoutLink = document.getElementById('logout-link');
            logoutLink.addEventListener('click', function (event) {
                event.preventDefault();
                const confirmLogout = confirm('Are you sure you want to log out?');
                if (confirmLogout) {
                    window.location.href = logoutLink.getAttribute('href');
                }
            });
        });

        function previewImage(imageSrc) {
            document.getElementById('imagePreview').src = imageSrc;
        }

        function updateLeaderboard() {
            $.ajax({
                url: '../../../modules/staff/api/leaderboard_card.php',
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    const leaderboardCard = $('.leaderboard-content');
                    leaderboardCard.empty();

                    if (data.length > 0) {
                        leaderboardCard.append('<h6 class="mb-3">Top Performers</h6>');
                        data.forEach(performer => {
                            leaderboardCard.append(`
                                <div class="top-performer">
                                    <div class="performer-name">
                                        <i class="lni lni-user me-2"></i>
                                        ${performer.full_name}
                                    </div>
                                    <div class="performer-rating mt-2">
                                        <i class="lni lni-star-filled me-1" style="color: #800000;"></i>
                                        Rating: ${performer.total_rating}
                                    </div>
                                </div>
                            `);
                        });
                    } else {
                        leaderboardCard.append(`
                            <div class="text-center py-4">
                                <i class="lni lni-empty-file" style="font-size: 2rem; color: #6c757d;"></i>
                                <p class="mt-2">No ratings available yet.</p>
                            </div>
                        `);
                    }
                },
                error: function () {
                    console.error('Error fetching leaderboard data');
                }
            });
        }

        $(document).ready(function () {
            updateLeaderboard();

            setInterval(updateLeaderboard, 60000);
        });


        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const staffId = <?php echo json_encode($staff_id); ?>;
            let scheduledDaysSet = new Set(); 

            function fetchScheduledDates() {
                return fetch(`../../../modules/staff/api/get_scheduled_dates.php?staff_id=${staffId}`)
                    .then(response => response.json())
                    .catch(error => console.error('Error fetching scheduled dates:', error));
            }

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'today'
                },
                dateClick: function (info) {

                    if (scheduledDaysSet.has(info.dateStr)) {
                        fetchPastSchedules(info.dateStr);
                    }
                },
                eventDidMount: function (info) {
                    if (
                        info.event.display === 'background' &&
                        info.event.classNames.includes('schedule-highlight')
                    ) {
                        info.el.style.backgroundColor = 'rgba(128, 0, 0, 0.1)';
                        const dayNumbers = document.querySelectorAll('.fc .fc-daygrid-day-number');
                        dayNumbers.forEach((dayNumber) => {
                            const cell = dayNumber.closest('.fc-daygrid-day');
                            if (cell && cell.getAttribute('data-date') === info.event.startStr) {
                                dayNumber.classList.add('custom-bold');
                            }
                        });
                    }
                },
                events: []
            });

            fetchScheduledDates().then((scheduledDates) => {
                if (!Array.isArray(scheduledDates)) return;

                const dayMap = { 'S': 1, 'M': 2, 'T': 3, 'W': 4, 'Th': 5, 'F': 6, 'Sat': 0 };

                function addScheduleHighlights(calendar, view) {

                    calendar.getEvents().forEach(ev => {
                        if (ev.display === 'background' && ev.classNames.includes('schedule-highlight')) {
                            ev.remove();
                        }
                    });

                    const start = new Date(view.start);
                    const end = new Date(view.end);

                    for (let d = new Date(start); d < end; d.setDate(d.getDate() + 1)) {
                        const jsDay = d.getDay();
                        const dateStr = d.toISOString().split('T')[0];

                        scheduledDates.forEach((daysString) => {
                            if (!daysString) return;
                            const daysArr = daysString.split(',').map(day => day.trim());
                            if (daysArr.some(day => dayMap[day] === jsDay)) {
                                scheduledDaysSet.add(dateStr);
                                calendar.addEvent({
                                    start: dateStr,
                                    display: 'background',
                                    classNames: ['schedule-highlight'],
                                });
                            }
                        });
                    }
                }

                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next',
                        center: 'title',
                        right: 'today'
                    },
                    dateClick: function (info) {
                        if (scheduledDaysSet.has(info.dateStr)) {
                            fetchPastSchedules(info.dateStr);
                        }
                    },
                    eventDidMount: function (info) {
                        if (
                            info.event.display === 'background' &&
                            info.event.classNames.includes('schedule-highlight')
                        ) {
                            info.el.style.backgroundColor = 'rgba(128, 0, 0, 0.1)';
                            const dayNumbers = document.querySelectorAll('.fc .fc-daygrid-day-number');
                            dayNumbers.forEach((dayNumber) => {
                                const cell = dayNumber.closest('.fc-daygrid-day');
                                if (cell && cell.getAttribute('data-date') === info.event.startStr) {
                                    dayNumber.classList.add('custom-bold');
                                }
                            });
                        }
                    },
                    events: [],
                    datesSet: function (view) {
                        addScheduleHighlights(calendar, view);
                    }
                });

                calendar.render();
                addScheduleHighlights(calendar, calendar.view);
            });

            function fetchPastSchedules(date) {
                fetch(`../../../api/get_past_schedules.php?&staff_id=${staffId}`)
                    .then(response => response.json())
                    .then(data => {
                        displayScheduleModal(data, date);
                    })
                    .catch(error => console.error('Error fetching schedules:', error));
            }

            function displayScheduleModal(schedules, selectedDate) {
                const modalBody = document.getElementById('scheduleModalBody');
                const modalTitle = document.getElementById('scheduleModalLabel');

                modalTitle.innerHTML = `<i class="lni lni-calendar me-2"></i>Schedule for ${selectedDate}`;
                modalBody.innerHTML = '';

                if (!schedules || schedules.length === 0) {
                    modalBody.innerHTML = '<div class="alert alert-info">No schedule found for this date.</div>';
                    console.log(schedules)
                } else {
                    const table = document.createElement('table');
                    table.className = 'table table-striped table-bordered';
                    table.innerHTML = `
                        <thead>
                            <tr>
                                <th>Days</th>
                                <th>Shift Time</th>
                                <th>Break Time</th>
                                <th>Location</th>
                                <th>Building</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${schedules.map(schedule => `
                                <tr>
                                    <td>${schedule.days}</td>
                                    <td>${schedule.shift_time}</td>
                                    <td>${schedule.break_time}</td>
                                    <td>${schedule.location}</td>
                                    <td>${schedule.building}</td>
                                </tr>`).join('')}
                        </tbody>
                    `;
                    modalBody.appendChild(table);
                }

                const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
                modal.show();
            }
        });

        const scoreMap = {
            "Excellent": "4",
            "Good": "3",
            "Needs Improvement": "2",
            "Unsatisfactory": "1"
        };

        function displayCriteriaScore(val) {
            if (!val) return "-";
            return scoreMap[val] || val;
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.view-assessment').forEach(button => {
                button.addEventListener('click', function () {
                    const reportId = this.getAttribute('data-report-id');
                    console.log('Fetching assessment for report ID:', reportId);
                    fetch(`../../api/fetch_assessment.php?report_id=${reportId}`)
                        .then(response => {
                            console.log('Response status:', response.status);
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Assessment data received:', data);
                            if (data.error) {
                                console.error('API error:', data.error);
                                return;
                            }
                            document.getElementById('ratingDisplay').textContent = data.rating || '-';
                            document.getElementById('remarksText').textContent = data.remark || 'No remarks provided.';
                            document.getElementById('criteria-task_completion').textContent = displayCriteriaScore(data.task_completion);
                            document.getElementById('criteria-attention_to_detail').textContent = displayCriteriaScore(data.attention_to_detail);
                            document.getElementById('criteria-trash_management').textContent = displayCriteriaScore(data.trash_management);
                            document.getElementById('criteria-floor_care').textContent = displayCriteriaScore(data.floor_care);
                            document.getElementById('criteria-organization').textContent = displayCriteriaScore(data.organization);

                            const evaluationTimeSpan = document.querySelector('#evaluationTime span');
                            if (data.evaluation_created_at && typeof data.evaluation_created_at === 'string') {
                                const d = new Date(data.evaluation_created_at.replace(' ', 'T'));
                                if (!isNaN(d)) {
                                    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true };
                                    evaluationTimeSpan.textContent = d.toLocaleString('en-US', options).replace(',', '').replace('AM', 'am').replace('PM', 'pm');
                                } else {
                                    evaluationTimeSpan.textContent = data.evaluation_created_at;
                                }
                            } else {
                                evaluationTimeSpan.textContent = 'N/A';
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching assessment:', error);
                            // Show error message to user
                            document.getElementById('ratingDisplay').textContent = 'Error loading data';
                            document.getElementById('remarksText').textContent = 'Failed to load assessment data.';
                        });
                });
            });
        });
    </script>
</body>

</html>