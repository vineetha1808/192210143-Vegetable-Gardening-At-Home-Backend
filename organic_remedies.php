<?php
// Include database connection
include('db.php'); // Ensure db.php contains the connection to your database
header('Content-Type: application/json');

// Check if the database connection was successful
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate remedy_name field
    if (!isset($_POST['remedy_name']) || empty($_POST['remedy_name'])) {
        echo json_encode(["success" => false, "message" => "Remedy name is required."]);
        exit;
    }

    $remedy_name = $conn->real_escape_string($_POST['remedy_name']);

    // Check if the remedy already exists in the database
    $checkSql = "SELECT id FROM organic_remedies WHERE remedy_name = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $remedy_name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Remedy already exists in the database."]);
        exit;
    }

    // Insert data into the organic_remedies table
    $insertSql = "INSERT INTO organic_remedies (remedy_name) VALUES (?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("s", $remedy_name);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Remedy added successfully!",
            "data" => [
                "id" => $conn->insert_id,
                "remedy_name" => $remedy_name
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    }

    $stmt->close();
}

$conn->close();
?>
