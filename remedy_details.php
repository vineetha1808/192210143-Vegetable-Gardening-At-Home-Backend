<?php
// Include database connection
include('db.php'); // Ensure db.php contains the connection to your database
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate required fields
    $required_fields = ['remedy_name', 'description', 'benefits'];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            echo json_encode(["success" => false, "message" => "$field is required."]);
            exit;
        }
    }

    // Sanitize input data
    $remedy_name = $conn->real_escape_string($_POST['remedy_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $benefits = $conn->real_escape_string($_POST['benefits']);

    // Check if the remedy exists in the organic_remedies table
    $checkSql = "SELECT id FROM organic_remedies WHERE remedy_name = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $remedy_name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "Remedy does not exist in the organic_remedies database."]);
        exit;
    }

    // Get the remedy ID
    $stmt->bind_result($remedy_id);
    $stmt->fetch();
    $stmt->close();

    // Check if remedy details already exist
    $checkDetailsSql = "SELECT id FROM remedy_details WHERE remedy_id = ?";
    $stmt = $conn->prepare($checkDetailsSql);
    $stmt->bind_param("i", $remedy_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Remedy details already exist in the database."]);
        exit;
    }
    $stmt->close();

    // Insert data into remedy details table
    $insertSql = "INSERT INTO remedy_details (remedy_id, remedy_name, description, benefits) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("isss", $remedy_id, $remedy_name, $description, $benefits);

    if ($stmt->execute()) {
        // Retrieve the inserted data
        $fetchSql = "SELECT * FROM remedy_details WHERE remedy_id = ?";
        $fetchStmt = $conn->prepare($fetchSql);
        $fetchStmt->bind_param("i", $remedy_id);
        $fetchStmt->execute();
        $result = $fetchStmt->get_result();
        $remedyDetails = $result->fetch_assoc();

        echo json_encode([
            "success" => true,
            "message" => "Remedy details added successfully!",
            "data" => $remedyDetails
        ]);

        $fetchStmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    }

    $stmt->close();
}

$conn->close();
?>
