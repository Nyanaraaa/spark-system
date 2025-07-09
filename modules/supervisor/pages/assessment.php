<?php 
/**
 * Supervisor Assessment Page
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
    <title>SPARK - Progress Assessment</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="manifest" href="../../../manifest.json">
    <link rel="stylesheet" href="../../../assets/css/global.css?v=<?php echo filemtime('../../../assets/css/global.css'); ?>">
    <link rel="stylesheet"
        href="../../../assets/css/supervisor_assessment.css?v=<?php echo filemtime('../../../assets/css/supervisor_assessment.css'); ?>">
</head>

<body>
    <?php include '../../../components/navbar/supervisor_navbar.php'; ?>

    <div class="main p-9">
        <div class="container">
            <h1 class="mb-4">
                <i class="lni lni-bar-chart me-2" style="color: var(--gold);"></i>
                Progress Assessment
            </h1>

            <?php 
            // Check for flash messages from SessionManager
            $flashMessage = SessionManager::getFlashMessage();
            if ($flashMessage): ?>
                <div class="alert alert-<?= $flashMessage['type'] === 'success' ? 'success' : ($flashMessage['type'] === 'error' ? 'danger' : 'info'); ?>" id="alert-message">
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

            <?php 
            // Also check for legacy session messages (for backward compatibility)
            if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= isset($_SESSION['msg_type']) ? $_SESSION['msg_type'] : 'info'; ?>" id="alert-message-legacy">
                    <?= htmlspecialchars($_SESSION['message']); ?>
                </div>
                <script>
                    setTimeout(function () {
                        const alertMessage = document.getElementById('alert-message-legacy');
                        if (alertMessage) {
                            alertMessage.style.display = 'none';
                        }
                    }, 5000);
                </script>
                <?php
                unset($_SESSION['message']);
                if (isset($_SESSION['msg_type'])) {
                    unset($_SESSION['msg_type']);
                }
                ?>
            <?php endif; ?>

            <!-- Filter Controls -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="lni lni-search me-2"></i>
                                Filter Reports
                            </h5>
                            <button type="button" class="btn btn-gold" id="print-button">
                                <i class="lni lni-printer me-1"></i> Print Reports
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
                                            placeholder="Search by name, ID or location" onkeyup="filterStaff()">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="lni lni-calendar"></i>
                                        </span>
                                        <input type="date" id="date-filter" class="form-control"
                                            onchange="filterStaff()">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="btn-group w-100" id="status-filter-group">
                                        <button type="button" class="btn btn-outline-secondary active" id="status-all"
                                            onclick="setStatusFilter('all')">All</button>
                                        <button type="button" class="btn btn-outline-secondary" id="status-pending"
                                            onclick="setStatusFilter('pending')">Pending</button>
                                        <button type="button" class="btn btn-outline-secondary" id="status-evaluated"
                                            onclick="setStatusFilter('evaluated')">Evaluated</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Summary -->
            <div id="staff-summary" class="row mb-4" style="display:none;">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="lni lni-user me-2"></i>
                                Staff Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="mb-1">Staff Name: <span id="summary-name" class="fw-normal"></span></h6>
                                    <h6 class="mb-0 text-muted">Employee ID: <span id="summary-id"
                                            class="fw-normal"></span></h6>
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
                                <i class="lni lni-clipboard-check me-2"></i>
                                Assessment Reports
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="assessment-table" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Full Name</th>
                                            <th>Employee ID</th>
                                            <th>Report</th>
                                            <th>Time Submitted</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // SQL query to fetch data, including the evaluation's created_at
                                        $sql = "SELECT pr.full_name, pr.employee_id, pr.report_id, pr.report_image, pr.location, pr.description, pr.created_at AS report_created_at, 
                                               e.created_at AS evaluation_created_at, pr.is_evaluated 
                                              FROM progress_reports pr
                                             LEFT JOIN evaluations e ON pr.report_id = e.report_id
                                            ORDER BY pr.is_evaluated ASC, pr.created_at DESC";

                                        $result = $conn->query($sql);

                                        if ($result->num_rows > 0) {
                                            while ($row = $result->fetch_assoc()) {
                                                $formattedReportDate = $row["report_created_at"] ? date("F j, Y g:ia", strtotime($row["report_created_at"])) : 'N/A';

                                                // Smart handling for base64 images and file paths
                                                $imagePath = $row["report_image"];
                                                if ($imagePath) {
                                                    // Check if it's base64 data
                                                    if (strpos($imagePath, 'data:image/') === 0) {
                                                        // Already a complete data URL, use as is
                                                    } elseif (preg_match('/^[A-Za-z0-9+\/=]+$/', $imagePath) && strlen($imagePath) > 100) {
                                                        // Looks like base64 without data URL prefix, add it
                                                        $imagePath = 'data:image/jpeg;base64,' . $imagePath;
                                                    } elseif (strpos($imagePath, 'uploads/') === 0) {
                                                        // Handle uploads/ prefix - convert to correct path
                                                        $imagePath = '../../../assets/images/' . $imagePath;
                                                    } elseif (!preg_match('/^(https?:\/\/|\/|\.\.\/)/i', $imagePath)) {
                                                        // Regular file path, add relative path to assets/images/uploads
                                                        $imagePath = '../../../assets/images/uploads/' . $imagePath;
                                                    }
                                                }

                                                echo '<tr>
                                                     <td>
                                                         <i class="lni lni-user me-2" style="color: var(--maroon);"></i>
                                                         ' . htmlspecialchars($row["full_name"]) . '
                                                     </td>
                                                     <td>
                                                         <span class="employee-badge">
                                                             <i class="lni lni-id-card me-1"></i>
                                                             ' . htmlspecialchars($row["employee_id"]) . '
                                                         </span>
                                                     </td>
                                                     <td>
                                                         <img src="' . htmlspecialchars($imagePath) . '" alt="Report Image" class="img-fluid report-thumbnail" 
                                                              onclick="showImageModal(\'' . htmlspecialchars($imagePath) . '\', \'' . htmlspecialchars($row["description"]) . '\')" />
                                                     </td>
                                                     <td>' . $formattedReportDate . '</td>
                                                     <td>
                                                         <span class="location-badge">
                                                             <i class="lni lni-map-marker me-1"></i>
                                                             ' . htmlspecialchars($row["location"]) . '
                                                         </span>
                                                     </td>
                                                     <td>';

                                                echo $row['is_evaluated'] == 1 ?
                                                    '<span class="status-badge status-evaluated">Evaluated</span>' :
                                                    '<span class="status-badge status-pending">Pending</span>';

                                                echo '</td>
                                                  <td>';

                                                if ($row['is_evaluated'] == 1) {
                                                    echo '<button class="btn-view-profile view-assessment" data-report-id="' . htmlspecialchars($row["report_id"]) . '" data-bs-toggle="modal" data-bs-target="#viewAssessmentModal">
                                                            <i class="lni lni-eye me-1"></i> View
                                                        </button>';
                                                } else {
                                                    echo '<button class="btn-evaluate evaluate-btn" 
                                                        data-report-id="' . htmlspecialchars($row["report_id"]) . '" 
                                                        data-employee-id="' . htmlspecialchars($row["employee_id"]) . '" 
                                                        data-image="' . htmlspecialchars($imagePath) . '"
                                                        data-description="' . htmlspecialchars($row["description"]) . '"   
                                                        id="evaluate-btn-' . htmlspecialchars($row["report_id"]) . '">
                                                        <i class="lni lni-pencil me-1"></i> Evaluate
                                                        </button>';
                                                }

                                                echo '</td></tr>';
                                            }
                                        } else {
                                            echo '<tr><td colspan="8" class="text-center">No Report Found</td></tr>';
                                        }
                                        ?>

                                        <tr id="no-record" style="display:none;">
                                            <td colspan="8" class="text-center py-4">
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

    <?php include '../../../components/modals/supervisor_image_preview_modal.php'; ?>
    <?php include '../../../components/modals/supervisor_assessment_details_modal.php'; ?>
    <?php include '../../../components/modals/staff_evaluation_modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="../../../assets/js/script.js"></script>

    <script>

        function showImageModal(imageUrl, description) {
            document.getElementById('modalImage').src = imageUrl;
            document.getElementById('modalDescription').textContent = description || 'No description available';
            new bootstrap.Modal(document.getElementById('imageModal')).show();
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

            const evaluateButtons = document.querySelectorAll('.evaluate-btn');
            console.log('Found evaluate buttons:', evaluateButtons.length); // Debug log
            
            evaluateButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    console.log('Evaluate button clicked'); // Debug log
                    e.preventDefault(); // Prevent any default behavior
                    
                    const reportId = this.getAttribute('data-report-id');
                    const employeeId = this.getAttribute('data-employee-id');
                    const reportImage = this.getAttribute('data-image');
                    const description = this.getAttribute('data-description');

                    console.log('Modal data:', { reportId, employeeId, reportImage, description }); // Debug log

                    // Populate modal with data
                    const evaluationImage = document.getElementById('evaluationImage');
                    const employeeIdInput = document.querySelector('input[name="employee_id"]');
                    const reportIdInput = document.querySelector('input[name="report_id"]');
                    const descriptionSpan = document.querySelector('#evaluationDescription span');

                    if (evaluationImage) evaluationImage.src = reportImage;
                    if (employeeIdInput) employeeIdInput.value = employeeId;
                    if (reportIdInput) reportIdInput.value = reportId;
                    if (descriptionSpan) descriptionSpan.textContent = description;

                    // Manually show the modal to ensure it opens
                    const modalElement = document.getElementById('evaluateModal');
                    console.log('Modal element found:', modalElement); // Debug log
                    
                    if (modalElement) {
                        const modal = new bootstrap.Modal(modalElement);
                        modal.show();
                        console.log('Modal show() called'); // Debug log
                    } else {
                        console.error('Modal element not found!');
                    }
                });
            });

            document.getElementById('saveEvaluation').addEventListener('click', function () {
                const form = document.getElementById('evaluationForm');
                let totalScore = 0;
                const maxScore = 20;

                const scores = {
                    "Excellent": 4,
                    "Good": 3,
                    "Needs Improvement": 2,
                    "Unsatisfactory": 1
                };

                const criteria = ['task_completion', 'attention_to_detail', 'trash_management', 'floor_care', 'organization'];
                criteria.forEach(criterion => {
                    const checkedRadio = document.querySelector(`input[name="${criterion}"]:checked`);
                    if (checkedRadio) {
                        totalScore += scores[checkedRadio.value];
                    }
                });

                const percentageScore = (totalScore / maxScore) * 100;

                const scoreInput = document.createElement('input');
                scoreInput.type = 'hidden';
                scoreInput.name = 'total_score';
                scoreInput.value = percentageScore.toFixed(0);
                form.appendChild(scoreInput);

                const ratingDisplay = document.getElementById('ratingDisplay');
                ratingDisplay.textContent = `Score: ${percentageScore.toFixed(2)}%`;

                form.submit();
            });

            const viewAssessmentButtons = document.querySelectorAll('.view-assessment');
            viewAssessmentButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const reportId = this.getAttribute('data-report-id');

                    fetch(`../../../modules/api/fetch_assessment.php?report_id=${reportId}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('ratingDisplay').textContent = data.rating || '';
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
                            document.getElementById('remarksText').textContent = data.remark || '';

                            document.getElementById('criteria-task_completion').textContent = displayCriteriaScore(data.task_completion);
                            document.getElementById('criteria-attention_to_detail').textContent = displayCriteriaScore(data.attention_to_detail);
                            document.getElementById('criteria-trash_management').textContent = displayCriteriaScore(data.trash_management);
                            document.getElementById('criteria-floor_care').textContent = displayCriteriaScore(data.floor_care);
                            document.getElementById('criteria-organization').textContent = displayCriteriaScore(data.organization);
                        })
                        .catch(error => console.error('Error fetching assessment:', error));
                });
            });
        });

        function filterStaff() {
            const searchInput = document.getElementById('search-input').value.toLowerCase();
            const dateFilter = document.getElementById('date-filter').value;
            const rows = document.querySelectorAll('#assessment-table tbody tr:not(#no-record)');
            const headerRow = document.querySelector('#assessment-table thead tr');
            let recordFound = false;

            let fullNames = [];
            let employeeIDs = [];

            const headerColumns = Array.from(headerRow.children);
            const fullNameIndex = headerColumns.findIndex(th => th.textContent.trim() === 'Full Name');
            const employeeidIndex = headerColumns.findIndex(th => th.textContent.trim() === 'Employee ID');

            rows.forEach(row => {
                const fullName = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
                const employeeID = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                const reportDateText = row.querySelector('td:nth-child(4)')?.textContent || '';
                const statusText = row.querySelector('td:nth-child(6)')?.textContent.trim().toLowerCase() || '';
                const location = row.querySelector('td:nth-child(5)')?.textContent.toLowerCase() || '';

                let reportDate = '';
                if (reportDateText && reportDateText !== 'N/A') {

                    const match = reportDateText.match(/^([A-Za-z]+) (\d{1,2}), (\d{4})/);
                    if (match) {
                        const monthNames = ["january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december"];
                        const month = monthNames.indexOf(match[1].toLowerCase()) + 1;
                        const day = match[2].padStart(2, '0');
                        const year = match[3];
                        reportDate = `${year}-${month.toString().padStart(2, '0')}-${day}`;
                    }
                }


                const matchesSearch = fullName.includes(searchInput) || employeeID.includes(searchInput) || location.includes(searchInput);
                const matchesDate = !dateFilter || reportDate === dateFilter;
                let matchesStatus = true;
                if (window.currentStatusFilter === 'pending') {
                    matchesStatus = statusText.includes('pending');
                } else if (window.currentStatusFilter === 'evaluated') {
                    matchesStatus = statusText.includes('evaluated');
                }

                if (matchesSearch && matchesDate && matchesStatus) {
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


            const staffSummary = document.getElementById('staff-summary');
            const summaryName = document.getElementById('summary-name');
            const summaryId = document.getElementById('summary-id');

            const isNameOrIdSearch =
                searchInput !== '' &&
                (
                    (fullNames.length === 1 && fullNames[0].toLowerCase().includes(searchInput)) ||
                    (employeeIDs.length === 1 && employeeIDs[0].toLowerCase().includes(searchInput))
                );

            if (isNameOrIdSearch) {
                staffSummary.style.display = '';
                summaryName.textContent = fullNames[0] || '';
                summaryId.textContent = employeeIDs[0] || '';

                // Only hide the Full Name column, keep Employee ID visible
                if (headerRow.children[fullNameIndex]) headerRow.children[fullNameIndex].style.display = 'none';

                rows.forEach(row => {
                    if (row.children[fullNameIndex]) row.children[fullNameIndex].style.display = 'none';
                });
            } else {
                staffSummary.style.display = 'none';

                // Show both columns when not filtering for specific staff
                if (headerRow.children[employeeidIndex]) headerRow.children[employeeidIndex].style.display = '';
                if (headerRow.children[fullNameIndex]) headerRow.children[fullNameIndex].style.display = '';

                rows.forEach(row => {
                    if (row.children[employeeidIndex]) row.children[employeeidIndex].style.display = '';
                    if (row.children[fullNameIndex]) row.children[fullNameIndex].style.display = '';
                });
            }

            const noRecordMessage = document.getElementById('no-record');
            noRecordMessage.style.display = recordFound ? 'none' : '';
        }

        document.getElementById('print-button').addEventListener('click', function () {
            const table = document.getElementById('assessment-table');
            const tableClone = table.cloneNode(true);

            const bodyRows = tableClone.querySelectorAll('tbody tr');
            bodyRows.forEach((row, idx) => {
                const originalRow = table.querySelector(`tbody tr:nth-child(${idx + 1})`);
                if (originalRow && originalRow.style.display === 'none') {
                    row.remove();
                }
            });

            const staffSummary = document.getElementById('staff-summary');
            const isStaffSummaryVisible = staffSummary && staffSummary.style.display !== 'none';
            let staffName = '';
            let staffId = '';

            const headerRow = tableClone.querySelector('thead tr');
            const headerColumns = Array.from(headerRow.children);
            const fullNameIndex = headerColumns.findIndex(th => th.textContent.trim() === 'Full Name');
            const employeeIdIndex = headerColumns.findIndex(th => th.textContent.trim() === 'Employee ID');
            const actionIndex = headerColumns.findIndex(th => th.textContent.trim() === 'Action');

            if (isStaffSummaryVisible) {

                staffName = document.getElementById('summary-name')?.textContent.trim() || '';
                staffId = document.getElementById('summary-id')?.textContent.trim() || '';

                const removeIndexes = [actionIndex, employeeIdIndex, fullNameIndex].filter(i => i > -1).sort((a, b) => b - a);
                removeIndexes.forEach(idx => {
                    if (headerRow.children[idx]) headerRow.removeChild(headerRow.children[idx]);
                });
                tableClone.querySelectorAll('tbody tr').forEach(row => {
                    removeIndexes.forEach(idx => {
                        if (row.children[idx]) row.removeChild(row.children[idx]);
                    });
                });
            } else {
              
                if (actionIndex > -1) headerRow.removeChild(headerRow.children[actionIndex]);
                tableClone.querySelectorAll('tbody tr').forEach(row => {
                    if (actionIndex > -1 && row.children[actionIndex]) row.removeChild(row.children[actionIndex]);
                });
            }

            const printWindow = window.open('', '', 'height=800,width=1000');

            printWindow.document.write('<html><head><title>Progress Assessment</title>');
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
            printWindow.document.write('.badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; }');
            printWindow.document.write('.employee-badge { background-color: #f8f9fa; color: #495057; }');
            printWindow.document.write('.location-badge { background-color: #fff3cd; color: #856404; }');
            printWindow.document.write('.status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; }');
            printWindow.document.write('.status-evaluated { background-color: #28a745; color: white; }');
            printWindow.document.write('.status-pending { background-color: #ffc107; color: black; }');
            printWindow.document.write('td img.report-thumbnail { max-width: 80px !important; max-height: 80px !important; width: auto !important; height: auto !important; display: block; margin: 0 auto; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.08); }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');

            printWindow.document.write('<div class="header">');
            printWindow.document.write('<img src="../../../assets/images/spark_logo.png" alt="Spark Logo" class="logo"/>');
            if (isStaffSummaryVisible) {
                printWindow.document.write('<h1>Progress Reports of ' + staffName + ' (' + staffId + ')</h1>');
            } else {
                printWindow.document.write('<h1>Progress Assessment</h1>');
            }
            printWindow.document.write('</div>');

            printWindow.document.write('<table class="table table-bordered">' + tableClone.innerHTML + '</table>');
            printWindow.document.write('<div style="margin-top: 20px; text-align: center; color: #666; font-size: 0.9rem;">Generated on ' + new Date().toLocaleString() + '</div>');
            printWindow.document.write('</body></html>');

            printWindow.document.close();

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
                    printExecuted = false; 

                    if (attempts < maxAttempts) {
                        setTimeout(executePrint, 500);
                    } else {
                        printWindow.close();
                        alert('Print failed. Please try again.');
                    }
                }
            }

            const img = printWindow.document.querySelector('img');

            if (img) {

                img.onload = executePrint;
                img.onerror = executePrint;
            }

            if (printWindow.document.readyState === 'complete') {
                setTimeout(executePrint, 100);
            } else {
                printWindow.document.addEventListener('DOMContentLoaded', executePrint);
            }


            setTimeout(executePrint, 1000);  
            setTimeout(executePrint, 3000); 

            printWindow.addEventListener('load', executePrint);

            setTimeout(() => {
                if (!printWindow.closed) {
                    printWindow.close();
                }
            }, 10000);
        });

        window.currentStatusFilter = 'all';

        function setStatusFilter(status) {
            currentStatusFilter = status;

            document.querySelectorAll('#status-filter-group button').forEach(btn => {
                btn.classList.remove('active');
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-secondary');
            });
            const activeBtn = document.getElementById('status-' + status);
            activeBtn.classList.add('active');
            activeBtn.classList.remove('btn-outline-secondary');
            activeBtn.classList.add('btn-primary');
            filterStaff();
        }

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
    </script>
</body>

</html>

<?php
} catch (Exception $e) {
    ErrorHandler::logError('Supervisor assessment page error: ' . $e->getMessage());
    SessionManager::setFlashMessage('An error occurred loading the assessment page. Please try again.', 'error');
    header('Location: ../../../index.php');
    exit();
}
?>