<?php
// Include database connection
include('db.php'); // Ensure db.php contains the connection to your database
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate required fields
    $required_fields = ['disease_id', 'disease_name', 'description', 'treatment'];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            echo json_encode(["success" => false, "message" => "$field is required."]);
            exit;
        }
    }

    // Sanitize input data
    $disease_id = intval($_POST['disease_id']);
    $disease_name = $conn->real_escape_string($_POST['disease_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $treatment = $conn->real_escape_string($_POST['treatment']);

    // Check if the disease exists in the diseasedetails table
    $checkSql = "SELECT id FROM diseasedetails WHERE disease_id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("i", $disease_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "Disease details not found in the database."]);
        exit;
    }
    $stmt->close();

    // Update disease details
    $updateSql = "UPDATE diseasedetails SET disease_name = ?, description = ?, treatment = ? WHERE disease_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("sssi", $disease_name, $description, $treatment, $disease_id);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Disease details updated successfully!",
            "data" => [
                "disease_id" => $disease_id,
                "disease_name" => $disease_name,
                "description" => $description,
                "treatment" => $treatment
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    }

    $stmt->close();
}

$conn->close();
?>
