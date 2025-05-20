<?php session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'supervisor') {
    // Redirect logic would go here if needed
}
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
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="supervisorassessment.css?v=<?php echo filemtime('supervisorassessment.css'); ?>">
</head>

<body>
    <?php include 'supervisor_navbar.php'; ?>

    <div class="main p-9">
        <div class="container">
            <h1 class="mb-4">
                <i class="lni lni-bar-chart me-2" style="color: var(--gold);"></i>
                Progress Assessment
            </h1>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?= $_SESSION['msg_type']; ?>" id="alert-message">
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
                                        <input type="text" id="search-input" class="form-control" placeholder="Search by name, ID or location" onkeyup="filterStaff()">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="lni lni-calendar"></i>
                                        </span>
                                        <input type="date" id="date-filter" class="form-control" onchange="filterStaff()">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="btn-group w-100" id="status-filter-group">
                                        <button type="button" class="btn btn-outline-secondary active" id="status-all" onclick="setStatusFilter('all')">All</button>
                                        <button type="button" class="btn btn-outline-secondary" id="status-pending" onclick="setStatusFilter('pending')">Pending</button>
                                        <button type="button" class="btn btn-outline-secondary" id="status-evaluated" onclick="setStatusFilter('evaluated')">Evaluated</button>
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
                                    <h5 class="mb-1">Staff Name: <span id="summary-name" class="fw-normal"></span></h5>
                                    <h6 class="mb-0 text-muted">Employee ID: <span id="summary-id" class="fw-normal"></span></h6>
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
                                        require 'database.php';

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
                                                         <img src="' . htmlspecialchars($row["report_image"]) . '" alt="Report Image" class="img-fluid report-thumbnail" 
                                                              onclick="showImageModal(\'' . htmlspecialchars($row["report_image"]) . '\', \'' . htmlspecialchars($row["description"]) . '\')" />
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
                                                        data-image="' . htmlspecialchars($row["report_image"]) . '"
                                                        data-description="' . htmlspecialchars($row["description"]) . '"   
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#evaluateModal" 
                                                        id="evaluate-btn-' . htmlspecialchars($row["report_id"]) . '">
                                                        <i class="lni lni-pencil me-1"></i> Evaluate
                                                        </button>';
                                                }

                                                echo '</td></tr>';
                                            }
                                        } else {
                                            echo '<tr><td colspan="8" class="text-center">No Report Found</td></tr>';
                                        }
                                        $conn->close();
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

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">
                        <i class="lni lni-image me-2"></i> Image Preview
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        <div class="col-md-8">
                            <div class="p-3 d-flex justify-content-center align-items-center bg-light" style="min-height: 300px;">
                                <img id="modalImage" src="/placeholder.svg" alt="Report Image" class="img-fluid rounded shadow-sm" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3">
                                <h6 class="border-bottom pb-2 mb-3" style="color: var(--maroon);">Description</h6>
                                <div id="modalDescription" class="text-start"></div>
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

    <!-- Assessment Details Modal -->
    <div class="modal fade" id="viewAssessmentModal" tabindex="-1" aria-labelledby="viewAssessmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAssessmentModalLabel">
                        <i class="lni lni-clipboard-check me-2"></i>Assessment Details
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
                                    <div class="text-muted mt-2" id="evaluationTime">Evaluated on: <span>-</span></div>
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
                                                <i class="lni lni-checkmark me-2" style="color: var(--maroon);"></i>Task Completion
                                            </span>
                                            <span class="criteria-score" id="criteria-task_completion">-</span>
                                        </li>
                                        <li class="criteria-item">
                                            <span class="criteria-label">
                                                <i class="lni lni-eye me-2" style="color: var(--maroon);"></i>Attention to Detail
                                            </span>
                                            <span class="criteria-score" id="criteria-attention_to_detail">-</span>
                                        </li>
                                        <li class="criteria-item">
                                            <span class="criteria-label">
                                                <i class="lni lni-cart me-2" style="color: var(--maroon);"></i>Trash Management
                                            </span>
                                            <span class="criteria-score" id="criteria-trash_management">-</span>
                                        </li>
                                        <li class="criteria-item">
                                            <span class="criteria-label">
                                                <i class="lni lni-brush me-2" style="color: var(--maroon);"></i>Floor Care
                                            </span>
                                            <span class="criteria-score" id="criteria-floor_care">-</span>
                                        </li>
                                        <li class="criteria-item">
                                            <span class="criteria-label">
                                                <i class="lni lni-grid-alt me-2" style="color: var(--maroon);"></i>Organization
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

    <!-- Staff Performance Evaluation Modal -->
    <div class="modal fade" id="evaluateModal" tabindex="-1" aria-labelledby="evaluateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="evaluateModalLabel">
                        <i class="lni lni-star me-2"></i> Staff Performance Evaluation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <img id="evaluationImage" src="/placeholder.svg" alt="Report Image"
                            class="img-fluid rounded shadow-sm"
                            style="max-width: 100%; height: auto; max-height: 300px; object-fit: contain;" />
                    </div>

                    <div id="evaluationDescription" class="mb-4 p-3 border rounded bg-light">
                        <strong style="color: var(--maroon);">Description:</strong> <span></span>
                    </div>

                    <div id="ratingDisplay" class="mt-3"></div>

                    <form id="evaluationForm" action="submit_evaluation.php" method="POST">
                        <input type="hidden" name="employee_id" value="">
                        <input type="hidden" name="report_id" value="">

                        <div class="bg-light p-4 rounded shadow-sm mb-4">
                            <h6 style="color: var(--maroon);" class="text-start mb-3">Standards Criteria</h6>
                            <div class="row text-center mb-4 border-bottom pb-3">
                                <div class="col border fw-bold p-2 rounded-start"
                                    style="background-color: var(--maroon); color: white;">4 <br><small
                                        class="fw-normal">Excellent</small>
                                </div>
                                <div class="col border fw-bold p-2" style="background-color: var(--maroon-light); color: white;">3
                                    <br><small class="fw-normal">Good</small>
                                </div>
                                <div class="col border fw-bold p-2" style="background-color: var(--gray-light); color: black;">2
                                    <br><small class="fw-normal">Needs
                                        Improvement</small>
                                </div>
                                <div class="col border fw-bold p-2 rounded-end"
                                    style="background-color: var(--black); color: white;">1 <br><small
                                        class="fw-normal">Unsatisfactory</small></div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label"><strong style="color: var(--maroon);">Task
                                        Completion</strong></label>
                                <div class="d-flex justify-content-between gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="task_completion"
                                            value="Excellent" id="taskCompletionExcellent" required>
                                        <label class="form-check-label rating-badge excellent"
                                            for="taskCompletionExcellent">4</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="task_completion" value="Good"
                                            id="taskCompletionGood">
                                        <label class="form-check-label rating-badge good"
                                            for="taskCompletionGood">3</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="task_completion"
                                            value="Needs Improvement" id="taskCompletionNeedsImprovement">
                                        <label class="form-check-label rating-badge needs-improvement"
                                            for="taskCompletionNeedsImprovement">2</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="task_completion"
                                            value="Unsatisfactory" id="taskCompletionUnsatisfactory">
                                        <label class="form-check-label rating-badge unsatisfactory"
                                            for="taskCompletionUnsatisfactory">1</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label"><strong style="color: var(--maroon);">Attention To
                                        Detail</strong></label>
                                <div class="d-flex justify-content-between gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="attention_to_detail"
                                            value="Excellent" id="attentionToDetailExcellent" required>
                                        <label class="form-check-label rating-badge excellent"
                                            for="attentionToDetailExcellent">4</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="attention_to_detail"
                                            value="Good" id="attentionToDetailGood">
                                        <label class="form-check-label rating-badge good"
                                            for="attentionToDetailGood">3</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="attention_to_detail"
                                            value="Needs Improvement" id="attentionToDetailNeedsImprovement">
                                        <label class="form-check-label rating-badge needs-improvement"
                                            for="attentionToDetailNeedsImprovement">2</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="attention_to_detail"
                                            value="Unsatisfactory" id="attentionToDetailUnsatisfactory">
                                        <label class="form-check-label rating-badge unsatisfactory"
                                            for="attentionToDetailUnsatisfactory">1</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label"><strong style="color: var(--maroon);">Trash
                                        Management</strong></label>
                                <div class="d-flex justify-content-between gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="trash_management"
                                            value="Excellent" id="trashManagementExcellent" required>
                                        <label class="form-check-label rating-badge excellent"
                                            for="trashManagementExcellent">4</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="trash_management"
                                            value="Good" id="trashManagementGood">
                                        <label class="form-check-label rating-badge good"
                                            for="trashManagementGood">3</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="trash_management"
                                            value="Needs Improvement" id="trashManagementNeedsImprovement">
                                        <label class="form-check-label rating-badge needs-improvement"
                                            for="trashManagementNeedsImprovement">2</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="trash_management"
                                            value="Unsatisfactory" id="trashManagementUnsatisfactory">
                                        <label class="form-check-label rating-badge unsatisfactory"
                                            for="trashManagementUnsatisfactory">1</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label"><strong style="color: var(--maroon);">Floor Care</strong></label>
                                <div class="d-flex justify-content-between gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="floor_care" value="Excellent"
                                            id="floorCareExcellent" required>
                                        <label class="form-check-label rating-badge excellent"
                                            for="floorCareExcellent">4</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="floor_care" value="Good"
                                            id="floorCareGood">
                                        <label class="form-check-label rating-badge good"
                                            for="floorCareGood">3</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="floor_care"
                                            value="Needs Improvement" id="floorCareNeedsImprovement">
                                        <label class="form-check-label rating-badge needs-improvement"
                                            for="floorCareNeedsImprovement">2</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="floor_care"
                                            value="Unsatisfactory" id="floorCareUnsatisfactory">
                                        <label class="form-check-label rating-badge unsatisfactory"
                                            for="floorCareUnsatisfactory">1</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label"><strong style="color: var(--maroon);">Organization</strong></label>
                                <div class="d-flex justify-content-between gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="organization"
                                            value="Excellent" id="organizationExcellent" required>
                                        <label class="form-check-label rating-badge excellent"
                                            for="organizationExcellent">4</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="organization" value="Good"
                                            id="organizationGood">
                                        <label class="form-check-label rating-badge good"
                                            for="organizationGood">3</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="organization"
                                            value="Needs Improvement" id="organizationNeedsImprovement">
                                        <label class="form-check-label rating-badge needs-improvement"
                                            for="organizationNeedsImprovement">2</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="organization"
                                            value="Unsatisfactory" id="organizationUnsatisfactory">
                                        <label class="form-check-label rating-badge unsatisfactory"
                                            for="organizationUnsatisfactory">1</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <label for="remark" class="form-label fw-bold" style="color: var(--maroon);">Remarks</label>
                            <textarea class="form-control" id="remark" name="remark" rows="4"
                                placeholder="Add detailed remarks here..." required
                                style="border-color: var(--maroon);"></textarea>
                            <div class="form-text">Please provide specific feedback to help with improvement.</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveEvaluation">
                        <i class="lni lni-checkmark-circle me-1"></i> Send Evaluation
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="script.js"></script>

    <script>
        // Function to show image modal
        function showImageModal(imageUrl, description) {
            document.getElementById('modalImage').src = imageUrl;
            document.getElementById('modalDescription').textContent = description || 'No description available';
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }
        
        document.addEventListener('DOMContentLoaded', function () {
            // Logout confirmation
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

            // Evaluate button functionality
            const evaluateButtons = document.querySelectorAll('.evaluate-btn');
            evaluateButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const reportId = this.getAttribute('data-report-id');
                    const employeeId = this.getAttribute('data-employee-id');
                    const reportImage = this.getAttribute('data-image');
                    const description = this.getAttribute('data-description');

                    document.getElementById('evaluationImage').src = reportImage;
                    document.querySelector('input[name="employee_id"]').value = employeeId;
                    document.querySelector('input[name="report_id"]').value = reportId;
                    document.querySelector('#evaluationDescription span').textContent = description;
                });
            });

            // Save evaluation functionality
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

            // View assessment functionality
            const viewAssessmentButtons = document.querySelectorAll('.view-assessment');
            viewAssessmentButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const reportId = this.getAttribute('data-report-id');

                    fetch(`fetch_assessment.php?report_id=${reportId}`)
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

                            // Set each criteria value using the mapping
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

            // Collect unique names and IDs for summary
            let fullNames = [];
            let employeeIDs = [];

            // Find column indexes
            const headerColumns = Array.from(headerRow.children);
            const fullNameIndex = headerColumns.findIndex(th => th.textContent.trim() === 'Full Name');
            const employeeidIndex = headerColumns.findIndex(th => th.textContent.trim() === 'Employee ID');

            rows.forEach(row => {
                const fullName = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
                const employeeID = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                const reportDateText = row.querySelector('td:nth-child(4)')?.textContent || '';
                const statusText = row.querySelector('td:nth-child(6)')?.textContent.trim().toLowerCase() || '';
                const location = row.querySelector('td:nth-child(5)')?.textContent.toLowerCase() || '';

                // --- Robust Date filter fix ---
                let reportDate = '';
                if (reportDateText && reportDateText !== 'N/A') {
                    // Example: "May 20, 2025 3:45pm"
                    const match = reportDateText.match(/^([A-Za-z]+) (\d{1,2}), (\d{4})/);
                    if (match) {
                        const monthNames = ["january","february","march","april","may","june","july","august","september","october","november","december"];
                        const month = monthNames.indexOf(match[1].toLowerCase()) + 1;
                        const day = match[2].padStart(2, '0');
                        const year = match[3];
                        reportDate = `${year}-${month.toString().padStart(2, '0')}-${day}`;
                    }
                }
                // -----------------------

                const matchesSearch = fullName.includes(searchInput) || employeeID.includes(searchInput) || location.includes(searchInput);
                const matchesDate = !dateFilter || reportDate === dateFilter;
                let matchesStatus = true;
                if (window.currentStatusFilter === 'pending') {
                    matchesStatus = statusText === 'pending';
                } else if (window.currentStatusFilter === 'evaluated') {
                    matchesStatus = statusText === 'evaluated';
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

            // Show/hide summary and columns
            const staffSummary = document.getElementById('staff-summary');
            const summaryName = document.getElementById('summary-name');
            const summaryId = document.getElementById('summary-id');

            // Only show summary if search matches exactly one name or one ID (not location)
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
                // Hide columns in header
                if (headerRow.children[employeeidIndex]) headerRow.children[employeeidIndex].style.display = 'none';
                if (headerRow.children[fullNameIndex]) headerRow.children[fullNameIndex].style.display = 'none';
                // Hide columns in body
                rows.forEach(row => {
                    if (row.children[employeeidIndex]) row.children[employeeidIndex].style.display = 'none';
                    if (row.children[fullNameIndex]) row.children[fullNameIndex].style.display = 'none';
                });
            } else {
                staffSummary.style.display = 'none';
                // Show columns in header
                if (headerRow.children[employeeidIndex]) headerRow.children[employeeidIndex].style.display = '';
                if (headerRow.children[fullNameIndex]) headerRow.children[fullNameIndex].style.display = '';
                // Show columns in body
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

            // Remove hidden rows (those filtered out)
            const bodyRows = tableClone.querySelectorAll('tbody tr');
            bodyRows.forEach((row, idx) => {
                const originalRow = table.querySelector(`tbody tr:nth-child(${idx + 1})`);
                if (originalRow && originalRow.style.display === 'none') {
                    row.remove();
                }
            });

            // Detect if staff summary is visible
            const staffSummary = document.getElementById('staff-summary');
            const isStaffSummaryVisible = staffSummary && staffSummary.style.display !== 'none';
            let staffName = '';
            let staffId = '';

            // Remove columns if staff summary is visible
            const headerRow = tableClone.querySelector('thead tr');
            const headerColumns = Array.from(headerRow.children);
            const fullNameIndex = headerColumns.findIndex(th => th.textContent.trim() === 'Full Name');
            const employeeIdIndex = headerColumns.findIndex(th => th.textContent.trim() === 'Employee ID');
            const actionIndex = headerColumns.findIndex(th => th.textContent.trim() === 'Action');

            if (isStaffSummaryVisible) {
                // Get staff name and id from summary
                staffName = document.getElementById('summary-name')?.textContent.trim() || '';
                staffId = document.getElementById('summary-id')?.textContent.trim() || '';

                // Remove columns in descending order to avoid index shift
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
                // Remove only the Action column
                if (actionIndex > -1) headerRow.removeChild(headerRow.children[actionIndex]);
                tableClone.querySelectorAll('tbody tr').forEach(row => {
                    if (actionIndex > -1 && row.children[actionIndex]) row.removeChild(row.children[actionIndex]);
                });
            }

            // Open a new window for printing
            const printWindow = window.open('', '', 'height=800,width=1000');
            printWindow.document.write('<html><head><title>Progress Assessment</title>');
            printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">');
            printWindow.document.write(`<style>
    body { padding: 20px; }
    .header { margin-bottom: 20px; text-align: center; }
    .logo { width: 200px; height: auto; margin-bottom: 15px; }
    h1 { color: #800000; margin-top: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th { background-color: #800000; color: white; }
    /* Make report images smaller in print */
    td img.report-thumbnail {
        max-width: 80px !important;
        max-height: 80px !important;
        width: auto !important;
        height: auto !important;
        display: block;
        margin: 0 auto;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
</style>`);
            printWindow.document.write('</head><body>');

            printWindow.document.write('<div class="header">');
            printWindow.document.write('<img src="spark_logo.png" alt="Spark Logo" class="logo"/>');
            if (isStaffSummaryVisible) {
                printWindow.document.write('<h1>Progress Reports of ' + staffName + ' (' + staffId + ')</h1>');
            } else {
                printWindow.document.write('<h1>Progress Assessment</h1>');
            }
            printWindow.document.write('</div>');

            // Add the filtered table
            printWindow.document.write('<table class="table table-bordered">' + tableClone.innerHTML + '</table>');
            printWindow.document.write('<div class="mt-4 text-center text-muted"><small>Generated on ' + new Date().toLocaleString() + '</small></div>');
            printWindow.document.write('</body></html>');

            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
        });

        window.currentStatusFilter = 'all';

        function setStatusFilter(status) {
            currentStatusFilter = status;
            // Update button styles
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
            return scoreMap[val] || val; // If already a number, just show it
        }
    </script>
</body>
</html>
