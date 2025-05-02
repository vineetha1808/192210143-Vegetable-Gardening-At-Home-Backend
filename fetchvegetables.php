<?php
// Database connection
$host = "localhost";
$user = "root";  // Default XAMPP user
$password = "vinitha";  // Default XAMPP password
$database = "veg_garden";

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Fetch vegetables
$sql = "SELECT id, name, image_path FROM vegetable";
$result = $conn->query($sql);

$vegetables = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $vegetables[] = $row;
    }
}

// Return JSON response
header("Content-Type: application/json");
echo json_encode($vegetables);

$conn->close();
?>
