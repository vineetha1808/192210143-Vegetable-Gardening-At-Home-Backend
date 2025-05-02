<?php
header("Content-Type: application/json");

// DB config
$conn = new mysqli("localhost", "root", "vinitha", "veg_garden");
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed."]));
}

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);
$title = $data["title"] ?? "";
$url = $data["url"] ?? "";

if (!empty($title) && !empty($url)) {
    $stmt = $conn->prepare("INSERT INTO youtube_videos (title, url) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $url);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Video added successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Insert failed."]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Title or URL missing."]);
}

$conn->close();
?>
