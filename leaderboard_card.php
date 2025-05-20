<?php
require 'database.php'; 


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


echo json_encode($top_performers);
?>
