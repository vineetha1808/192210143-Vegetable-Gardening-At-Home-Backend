<?php
$host = "localhost";
$db = "veg_garden";
$user = "root";
$pass = "vinitha";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT title, url FROM youtube_videos";
$result = $conn->query($sql);

$videos = [];
while ($row = $result->fetch_assoc()) {
    $videos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($videos);
$conn->close();
?>
