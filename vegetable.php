<?php
// Include database connection
include('db.php'); // Ensure db.php contains the connection to your database
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate name field
    if (!isset($_POST['name']) || empty($_POST['name'])) {
        echo json_encode(["success" => false, "message" => "Vegetable name is required."]);
        exit;
    }

    $name = $conn->real_escape_string($_POST['name']);

    // Validate image upload
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["success" => false, "message" => "No file uploaded or there was an error."]);
        exit;
    }

    // Check if the vegetable already exists
    $checkSql = "SELECT id FROM vegetable WHERE name = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Vegetable already exists in the database."]);
        exit;
    }

    // Create uploads directory if not exists
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Generate unique file name
    $fileName = uniqid() . "_" . basename($_FILES['image']['name']);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Validate file type
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(["success" => false, "message" => "Only JPG, JPEG, PNG, and GIF file types are allowed."]);
        exit;
    }

    // Move file to uploads folder
    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
        echo json_encode(["success" => false, "message" => "Failed to upload the image."]);
        exit;
    }

    // Insert data into database
    $insertSql = "INSERT INTO vegetable (name, image_path) VALUES (?, ?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("ss", $name, $fileName);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Vegetable added successfully!",
            "data" => [
                "id" => $conn->insert_id,
                "name" => $name,
                "image_path" => $fileName
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    }

    $stmt->close();
}

$conn->close();
?>
