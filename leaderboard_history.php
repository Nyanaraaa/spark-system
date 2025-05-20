<?php
session_start(); 
require 'database.php'; 

$selected_month = isset($_POST['month']) ? $_POST['month'] : '';
$selected_year = isset($_POST['year']) ? $_POST['year'] : '';

$sql = "SELECT * FROM leaderboard_history";

$filters = [];
if ($selected_month) {
    $filters[] = "month(created_at) = ?";
}
if ($selected_year) {
    $filters[] = "YEAR(created_at) = ?";
}

if (!empty($filters)) {
    $sql .= " WHERE " . implode(' AND ', $filters);
}

$sql .= " ORDER BY total_rating DESC, created_at DESC"; 

$stmt = $conn->prepare($sql);

$params = [];
if ($selected_month) {
    $params[] = $selected_month; 
}
if ($selected_year) {
    $params[] = $selected_year; 
}

if (!empty($params)) {
    $stmt->bind_param(str_repeat("i", count($params)), ...$params); 
}

$stmt->execute();
$result = $stmt->get_result(); 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPARK - Leaderboard History</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="supervisor_leaderboard.css?v=<?php echo filemtime('supervisor_leaderboard.css'); ?>">
</head>

<body>
    <div class="main p-9">
        <div class="container">
            <div class="d-flex align-items-center mb-4">
                <a href="supervisor_leaderboard.php" class="btn btn-print me-3">
                    <i class="lni lni-arrow-left me-1"></i> Back
                </a>
                <h1 class="mb-0 flex-grow-1">
                    <i class="lni lni-archive me-2" style="color: var(--gold);"></i>
                    Leaderboard History
                </h1>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="lni lni-filter me-2"></i>
                                Filter Options
                            </h5>
                            <button type="button" class="btn btn-print" id="print-button">
                                <i class="lni lni-printer me-1"></i> Print
                            </button>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="row g-3">
                                <div class="col-md-4">
                                    <label for="month" class="form-label">Filter by Month:</label>
                                    <select name="month" id="month" class="form-select">
                                        <option value="">All Months</option>
                                        <?php
                                        for ($month = 1; $month <= 12; $month++) {
                                            $selected = ($month == $selected_month) ? 'selected' : '';
                                            echo "<option value='{$month}' {$selected}>" . date("F", mktime(0, 0, 0, $month, 1)) . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="year" class="form-label">Filter by Year:</label>
                                    <select name="year" id="year" class="form-select">
                                        <option value="">All Years</option>
                                        <?php
                                        $current_year = date("Y");
                                        for ($year = $current_year; $year >= $current_year - 5; $year--) {
                                            $selected = ($year == $selected_year) ? 'selected' : '';
                                            echo "<option value='{$year}' {$selected}>$year</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" style="visibility:hidden;">Apply</label>
                                    <button type="submit" class="btn btn-filter w-100 mt-2 mt-md-0">
                                        <i class="lni lni-filter me-1"></i> Apply Filters
                                    </button>
                                </div>
                            </form>

                            <div class="mt-4">
                                <div class="input-group">
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
                                <i class="lni lni-list me-2"></i>
                                Historical Rankings
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="leaderboard-history-table" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="10%">Rank</th>
                                            <th width="20%">Employee ID</th>
                                            <th width="30%">Full Name</th>
                                            <th width="15%">Total Rating</th>
                                            <th width="12.5%">Month</th>
                                            <th width="12.5%">Year</th>
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
                                                    $ratingIcon = '<i class="bi bi-star-fill me-1 trophy-icon"></i>';
                                                } elseif ($totalRating >= 50) {
                                                    $textClass = 'text-warning';
                                                    $ratingIcon = '<i class="bi bi-star-half me-1 trophy-icon"></i>';
                                                } else {
                                                    $textClass = 'text-danger';
                                                }

                                                echo "<td class='{$textClass}'>{$ratingIcon}{$totalRating}</td>";
                                                echo "<td>" . date("F", strtotime($row['created_at'])) . "</td>";
                                                echo "<td>" . date("Y", strtotime($row['created_at'])) . "</td>";

                                                echo "</tr>";
                                                $rank++; 
                                            }
                                        } else {
                                            echo "<tr><td colspan='6' class='text-center py-4'><i class='lni lni-information me-2'></i>No history available</td></tr>";
                                        }
                                        $stmt->close(); 
                                        $conn->close(); 
                                        ?>
                                        <tr id="no-record" style="display:none;">
                                            <td colspan="6" class="text-center py-4">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>
    <script>
        function filterStaff() {
            const searchInput = document.getElementById('search-input').value.toLowerCase();
            const rows = document.querySelectorAll('#leaderboard-history-table tbody tr:not(#no-record)');
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
    <script>
        document.getElementById('print-button').addEventListener('click', function () {
            const table = document.getElementById('leaderboard-history-table');
            const tableClone = table.cloneNode(true);

            const printWindow = window.open('', '', 'height=800,width=1000');
            
            printWindow.document.write('<html><head><title>Staff Performance Assessment, Recording and Keeping</title>');
            printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">');
            printWindow.document.write('<style>body { padding: 20px; } .header { margin-bottom: 20px; text-align: center; } .logo { width: 200px; height: auto; margin-bottom: 15px; } h1 { color: #800000; margin-top: 20px; } table { width: 100%; border-collapse: collapse; } th { background-color: #800000; color: white; } </style>');
            printWindow.document.write('</head><body>');

            printWindow.document.write('<div class="header">');
            printWindow.document.write('<img src="spark_logo.png" alt="Spark Logo" class="logo"/>');
            printWindow.document.write('<h1>Leaderboard History</h1>');
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
</body>
</html>
