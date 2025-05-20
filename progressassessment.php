<?php
session_start(); 

require 'database.php'; 


if (!isset($_SESSION['username'])) {
    
    header("Location: index.php");
    exit();
}


$employee_id = $_SESSION['employee_id']; 
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
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="progressassessment.css?v=<?php echo filemtime('progressassessment.css'); ?>">

</head>

<body>
    <?php include 'housekeeping_navbar.php'; ?>
    <div class="main p-9">
        <div class="container">
            <h1 class="mb-4">Progress Assessment</h1>
            
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center">
                    <i class="lni lni-star-filled me-2"></i>
                    <span class="card-title">Your Assessment Reports</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Report Image</th>
                                    <th>Evaluated At</th>
                                    <th>Assessment</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                require 'database.php'; 

                                // Fetch all progress reports for the logged-in employee
                                $sql = "SELECT report_id, report_image, employee_id, description, created_at 
                                        FROM progress_reports 
                                        WHERE employee_id = ?
                                        ORDER BY created_at DESC";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("s", $employee_id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $formattedDate = date("F j, Y g:ia", strtotime($row["created_at"]));
                                        echo '<tr>
                                            <td>
                                                <img src="' . htmlspecialchars($row["report_image"]) . '" alt="Report Image" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                            </td>
                                            <td>' . $formattedDate . '</td>
                                            <td>
                                                <button class="btn btn-info btn-sm view-assessment" 
                                                    data-report-id="' . $row["report_id"] . '" 
                                                    data-bs-toggle="modal" data-bs-target="#viewAssessmentModal">
                                                    <i class="lni lni-star me-1"></i> View Assessment
                                                </button>
                                            </td>
                                            <td>
                                                <button class="btn btn-secondary btn-sm view-details" 
                                                    data-image="' . htmlspecialchars($row["report_image"]) . '"
                                                    data-description="' . htmlspecialchars($row["description"]) . '"
                                                    data-created="' . $formattedDate . '"
                                                    data-bs-toggle="modal" data-bs-target="#detailsModal">
                                                    <i class="lni lni-eye me-1"></i> View Details
                                                </button>
                                            </td>
                                        </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="4" class="text-center py-4">No reports found. Submit a progress report to receive an assessment.</td></tr>';
                                }

                                $stmt->close();
                                $conn->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">
                        <i class="lni lni-image me-2"></i>Image Preview
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
                </div>
                <div class="modal-body text-center"> 
                    <img id="modalImage" src="/placeholder.svg" alt="Report Image" class="img-fluid mb-3" />
                    <div class="mt-3">
                        <h6 class="text-maroon mb-2" style="color: var(--maroon);">Description</h6>
                        <div id="modalDescription" class="p-3 bg-light rounded"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">
                        <i class="lni lni-files me-2"></i>Progress Report Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img id="detailsImage" src="/placeholder.svg" alt="Report Image" class="img-fluid" style="max-height: 250px;" />
                    </div>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <strong><i class="lni lni-text-format me-2" style="color: var(--maroon);"></i>Description:</strong> 
                            <p id="detailsDescription" class="mt-2 p-2 bg-light rounded"></p>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
    <span><i class="lni lni-calendar me-2" style="color: var(--maroon);"></i><strong>Submitted At:</strong></span>
    <span id="detailsCreated"></span>
</li>
                    </ul>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="script.js"></script>

    <script>
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
            
            const logoutLink = document.getElementById('logout-link');
            logoutLink.addEventListener('click', function(event) {
                event.preventDefault(); 
                const confirmLogout = confirm('Are you sure you want to log out?'); 
                if (confirmLogout) {
                    window.location.href = logoutLink.href; 
                }
            });

            
            const viewImageButtons = document.querySelectorAll('.view-image');
            viewImageButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const imageSrc = this.dataset.image; 
                    const description = this.dataset.description; 

                    
                    document.getElementById('modalImage').src = imageSrc;
                    document.getElementById('modalDescription').innerHTML = description;
                });
            });

            // Details modal logic
            const viewDetailsButtons = document.querySelectorAll('.view-details');
            viewDetailsButtons.forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('detailsImage').src = this.dataset.image;
                    document.getElementById('detailsDescription').textContent = this.dataset.description;
                    document.getElementById('detailsCreated').textContent = this.dataset.created;
                });
            });

            // Assessment details logic
            document.querySelectorAll('.view-assessment').forEach(button => {
                button.addEventListener('click', function () {
                    const reportId = this.getAttribute('data-report-id');
                    fetch(`fetch_assessment.php?report_id=${reportId}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('ratingDisplay').textContent = data.rating || '-';
                            document.getElementById('remarksText').textContent = data.remark || 'No remarks provided.';
                            document.getElementById('criteria-task_completion').textContent = displayCriteriaScore(data.task_completion);
                            document.getElementById('criteria-attention_to_detail').textContent = displayCriteriaScore(data.attention_to_detail);
                            document.getElementById('criteria-trash_management').textContent = displayCriteriaScore(data.trash_management);
                            document.getElementById('criteria-floor_care').textContent = displayCriteriaScore(data.floor_care);
                            document.getElementById('criteria-organization').textContent = displayCriteriaScore(data.organization);

                            // Set the evaluated at timestamp
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
                        .catch(error => console.error('Error fetching assessment:', error));
                });
            });
        });
    </script>
</body>

</html>
