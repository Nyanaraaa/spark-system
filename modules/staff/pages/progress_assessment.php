<?php
/**
 * Progress Assessment Page
 * Uses new security components and authentication
 */

// Include bootstrap for security components
require_once dirname(dirname(dirname(__DIR__))) . '/includes/bootstrap.php';

// Check authentication
SessionManager::requireAuth();
SessionManager::requireRole('housekeeping_staff');

try {
    // Get current user data
    $userData = SessionManager::getCurrentUser();
    if (!$userData || !isset($userData['employee_id'])) {
        SessionManager::setFlashMessage('Session expired. Please log in again.', 'error');
        header('Location: ../../../index.php');
        exit();
    }

    $employee_id = $userData['employee_id'];

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
    <link rel="manifest" href="../../../manifest.json">
    <link rel="stylesheet" href="../../../assets/css/global.css?v=<?php echo filemtime('../../../assets/css/global.css'); ?>">
    <link rel="stylesheet"
        href="../../../assets/css/progress_assessment.css?v=<?php echo filemtime('../../../assets/css/progress_assessment.css'); ?>">

</head>

<body>
    <?php include '../../../components/navbar/staff_navbar.php'; ?>
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
                                        
                                        // Handle image path
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
                                                <img src="' . htmlspecialchars($imagePath) . '" alt="Report Image" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
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
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
} catch (Exception $e) {
    ErrorHandler::logError('Progress assessment error: ' . $e->getMessage(), [
        'employee_id' => $employee_id ?? 'unknown'
    ]);
    
    SessionManager::setFlashMessage('An error occurred loading your assessments. Please try again.', 'error');
    header('Location: ../../../index.php');
    exit();
}
?>

<?php include '../../../components/modals/image_preview_modal.php'; ?>
<?php include '../../../components/modals/progress_details_modal.php'; ?>
<?php include '../../../components/modals/assessment_details_modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="../../../assets/js/script.js"></script>

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
            logoutLink.addEventListener('click', function (event) {
                event.preventDefault();
                const confirmLogout = confirm('Are you sure you want to log out?');
                if (confirmLogout) {
                    window.location.href = logoutLink.href;
                }
            });


            const viewImageButtons = document.querySelectorAll('.view-image');
            viewImageButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const imageSrc = this.dataset.image;
                    const description = this.dataset.description;


                    document.getElementById('modalImage').src = imageSrc;
                    document.getElementById('modalDescription').innerHTML = description;
                });
            });

            // Details modal logic
            const viewDetailsButtons = document.querySelectorAll('.view-details');
            viewDetailsButtons.forEach(button => {
                button.addEventListener('click', function () {
                    document.getElementById('detailsImage').src = this.dataset.image;
                    document.getElementById('detailsDescription').textContent = this.dataset.description;
                    document.getElementById('detailsCreated').textContent = this.dataset.created;
                });
            });

            // Assessment details logic
            document.querySelectorAll('.view-assessment').forEach(button => {
                button.addEventListener('click', function () {
                    const reportId = this.getAttribute('data-report-id');
                    fetch(`../../../modules/api/fetch_assessment.php?report_id=${reportId}`)
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