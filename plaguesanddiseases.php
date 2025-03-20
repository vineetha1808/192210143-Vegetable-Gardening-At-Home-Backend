<?php
// Include database connection
include('db.php'); // Ensure db.php contains the connection to your database
header('Content-Type: application/json');

// Check if the database connection was successful
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate disease_name field
    if (!isset($_POST['disease_name']) || empty($_POST['disease_name'])) {
        echo json_encode(["success" => false, "message" => "Disease name is required."]);
        exit;
    }

    $disease_name = $conn->real_escape_string($_POST['disease_name']);

    // Check if the disease already exists in the database
    $checkSql = "SELECT id FROM plagues_and_diseases WHERE disease_name = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $disease_name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Disease already exists in the database."]);
        exit;
    }

    // Insert data into the plagues_and_diseases table
    $insertSql = "INSERT INTO plagues_and_diseases (disease_name) VALUES (?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("s", $disease_name);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Disease added successfully!",
            "data" => [
                "id" => $conn->insert_id,
                "disease_name" => $disease_name
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    }

    $stmt->close();
}

$conn->close();
?>
