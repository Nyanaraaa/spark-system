<?php
session_start(); 
require 'database.php'; 

$current_month = date("Y-m");
$current_date = date("Y-m-d");
$last_day_of_month = date("Y-m-t", strtotime($current_date));

$check_sql = "SELECT COUNT(*) AS count FROM leaderboard_history WHERE month = '$current_month'";
$check_result = $conn->query($check_sql); 
$check_row = $check_result->fetch_assoc();

if ($current_date === $last_day_of_month && $check_row['count'] == 0) {
    $archive_sql = "
        INSERT INTO leaderboard_history (employee_id, full_name, total_rating, month)
        SELECT e.employee_id, CONCAT(s.first_name, ' ', s.last_name), SUM(e.rating), '$current_month'
        FROM evaluations e
        INNER JOIN staff s ON e.employee_id = s.employee_id
        GROUP BY e.employee_id
    ";
    $conn->query($archive_sql); 
    
    $delete_sql = "DELETE FROM evaluations WHERE MONTH(created_at) = MONTH(CURRENT_DATE())"; 
    $conn->query($delete_sql);
}

$sql = "
    SELECT 
        e.employee_id,
        CONCAT(s.first_name, ' ', s.last_name) AS full_name,
        ROUND(AVG(e.rating), 2) AS total_rating
    FROM (
        SELECT *,
               ROW_NUMBER() OVER (PARTITION BY employee_id ORDER BY rating DESC) AS rn
        FROM evaluations
    ) e
    INNER JOIN staff s ON e.employee_id = s.employee_id
    WHERE e.rn <= 5
    GROUP BY e.employee_id, full_name
    ORDER BY total_rating DESC
";
$result = $conn->query($sql); 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPARK - Leaderboard</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="supervisor_leaderboard.css?v=<?php echo filemtime('supervisor_leaderboard.css'); ?>">
</head>

<body>
    <?php include 'supervisor_navbar.php'; ?>

    <div class="main p-9">
        <div class="container">
            <h1 class="mb-4">
                <i class="lni lni-crown me-2" style="color: var(--gold);"></i>
                Leaderboard
            </h1>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="lni lni-star-filled me-2"></i>
                                Top Performers
                            </h5>
                            <a href="leaderboard_history.php" class="btn btn-secondary">
                                <i class="lni lni-archive me-1"></i> View Leaderboard History
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <div class="input-group w-auto" style="min-width: 50px;">
                                    <span class="input-group-text">
                                        <i class="lni lni-search"></i>
                                    </span>
                                    <input 
                                        type="text" 
                                        id="search-input" 
                                        class="form-control" 
                                        placeholder="Search by name or ID" 
                                        onkeyup="filterStaff()">
                                </div>

                                <button type="button" class="btn btn-print" id="print-button">
                                    <i class="lni lni-printer me-1"></i> Print
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table id="leaderboard-table" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="10%">Rank</th>
                                            <th width="25%">Employee ID</th>
                                            <th width="40%">Full Name</th>
                                            <th width="25%">Total Rating</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result && $result->num_rows > 0) {
                                            $rank = 1; 
                                            while ($row = $result->fetch_assoc()) {
                                                $rankClass = '';
                                                $rankBadge = '';
                                                
                                                if ($rank === 1) {
                                                    $rankClass = 'rank-1';
                                                    $rankBadge = '<span class="badge badge-gold me-2"><i class="lni lni-crown"></i> 1st</span>';
                                                } elseif ($rank === 2) {
                                                    $rankClass = 'rank-2';
                                                    $rankBadge = '<span class="badge badge-silver me-2">2nd</span>';
                                                } elseif ($rank === 3) {
                                                    $rankClass = 'rank-3';
                                                    $rankBadge = '<span class="badge badge-bronze me-2">3rd</span>';
                                                }
                                                
                                                echo "<tr>";
                                                echo "<td class='{$rankClass}'>{$rankBadge}{$rank}</td>"; 
                                                echo "<td>{$row['employee_id']}</td>"; 
                                                echo "<td>{$row['full_name']}</td>"; 

                                                $totalRating = $row['total_rating'];
                                                $textClass = ''; 
                                                $ratingIcon = '';

                                                if ($totalRating >= 100) {
                                                    $textClass = 'text-success'; 
                                                    $ratingIcon = '<i class="lni lni-star-filled trophy-icon"></i>';
                                                } elseif ($totalRating >= 50) {
                                                    $textClass = 'text-warning';
                                                    $ratingIcon = '<i class="lni lni-star-half trophy-icon"></i>';
                                                } else {
                                                    $textClass = 'text-danger';
                                                }

                                                echo "<td class='{$textClass}'>{$ratingIcon}{$totalRating}</td>";
                                                echo "</tr>";
                                                $rank++; 
                                            }
                                        } else {
                                            echo "<tr><td colspan='4' class='text-center py-4'><i class='lni lni-information me-2'></i>No data available</td></tr>"; 
                                        }
                                        $conn->close(); 
                                        ?>
                                        <tr id="no-record" style="display:none;">
                                            <td colspan="4" class="text-center py-4">
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

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="lni lni-information me-2"></i>
                                Leaderboard Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <p>The leaderboard displays the current rankings based on total ratings received. Rankings are reset at the end of each month.</p>
                            <ul>
                                <li><span class="text-success"><i class="bi bi-star-fill me-1"></i>100+ points</span> - Excellent performance</li>
                                <li><span class="text-warning"><i class="bi bi-star-half me-1"></i>50-99 points</span> - Good performance</li>
                                <li><span class="text-danger"><i class="lni lni-star-half me-1"></i>Below 50 points</span> - Needs improvement</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script src="script.js"></script>
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
    </script>
    <script>
        document.getElementById('print-button').addEventListener('click', function () {
            const table = document.getElementById('leaderboard-table');
            const tableClone = table.cloneNode(true);

            const printWindow = window.open('', '', 'height=800,width=1000');
            
            printWindow.document.write('<html><head><title>Staff Performance Assessment, Recording and Keeping</title>');
            printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">');
            printWindow.document.write('<style>body { padding: 20px; } .header { margin-bottom: 20px; text-align: center; } .logo { width: 200px; height: auto; margin-bottom: 15px; } h1 { color: #800000; margin-top: 20px; } table { width: 100%; border-collapse: collapse; } th { background-color: #800000; color: white; } </style>');
            printWindow.document.write('</head><body>');

            printWindow.document.write('<div class="header">');
            printWindow.document.write('<img src="spark_logo.png" alt="Spark Logo" class="logo"/>');
            printWindow.document.write('<h1>Leaderboard</h1>');
            printWindow.document.write('</div>');

            printWindow.document.write('<table class="table table-bordered">' + tableClone.outerHTML + '</table>');
            printWindow.document.write('<div class="mt-4 text-center text-muted"><small>Generated on ' + new Date().toLocaleString() + '</small></div>');
            printWindow.document.write('</body></html>');

            printWindow.document.close();
            printWindow.document.querySelector('img').onload = function() {
                printWindow.focus();
                printWindow.print();
                printWindow.close();
            };
        });
    </script>
    <script>
        function filterStaff() {
            const searchInput = document.getElementById('search-input').value.toLowerCase();
            const rows = document.querySelectorAll('#leaderboard-table tbody tr:not(#no-record)');
            let recordFound = false;

            rows.forEach(row => {
                if (row.id === 'no-record') return;
                
                const employeeID = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                const fullName = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                
                if (employeeID.includes(searchInput) || fullName.includes(searchInput)) {
                    row.style.display = ''; 
                    recordFound = true;
                } else {
                    row.style.display = 'none'; 
                }
            });

            const noRecordMessage = document.getElementById('no-record');
            if (recordFound) {
                noRecordMessage.style.display = 'none'; 
            } else {
                noRecordMessage.style.display = ''; 
            }
        }
    </script>
</body>
</html>
