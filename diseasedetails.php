<?php
// Include database connection
include('db.php'); // Ensure db.php contains the connection to your database
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate required fields
    $required_fields = ['disease_name', 'description', 'treatment'];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            echo json_encode(["success" => false, "message" => "$field is required."]);
            exit;
        }
    }

    // Sanitize input data
    $disease_name = $conn->real_escape_string($_POST['disease_name']);
    $description = $conn->real_escape_string($_POST['description']);
    $treatment = $conn->real_escape_string($_POST['treatment']);

    // Check if the disease already exists in the plagues_and_diseases table
    $checkSql = "SELECT id FROM plagues_and_diseases WHERE disease_name = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $disease_name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "Disease does not exist in the plagues_and_diseases database."]);
        exit;
    }

    // Get the disease ID
    $stmt->bind_result($disease_id);
    $stmt->fetch();
    $stmt->close();

    // Check if disease details already exist
    $checkDetailsSql = "SELECT id FROM diseasedetails WHERE disease_id = ?";
    $stmt = $conn->prepare($checkDetailsSql);
    $stmt->bind_param("i", $disease_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Disease details already exist in the database."]);
        exit;
    }
    $stmt->close();

    // Insert data into disease details table
    $insertSql = "INSERT INTO diseasedetails (disease_id, disease_name, description, treatment) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("isss", $disease_id, $disease_name, $description, $treatment);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Disease details added successfully!",
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
