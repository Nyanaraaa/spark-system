<?php
$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "spark_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->query("SET time_zone = '+08:00'");
?>