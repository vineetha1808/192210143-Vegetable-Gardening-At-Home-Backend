<?php
// db.php
$servername = "localhost";
$username = "root"; // replace with your MySQL username
$password = "vinitha"; // replace with your MySQL password
$dbname = "veg_garden"; // replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// else{
//     echo "Connected successfully";
// }
?>
