<?php
// Include database connection
include('db.php'); // Ensure db.php contains the connection to your database
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate required fields
    $required_fields = ['vegetable_id', 'name', 'container_liters', 'typeofsoil', 'planting', 'watering_schedule', 'nutrients_for_soil', 'disease', 'harvest_time', 'benefits'];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            echo json_encode(["success" => false, "message" => "$field is required."]);
            exit;
        }
    }

    $vegetable_id = $conn->real_escape_string($_POST['vegetable_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $container_liters = $conn->real_escape_string($_POST['container_liters']);
    $type_of_soil = $conn->real_escape_string($_POST['typeofsoil']);
    $planting = $conn->real_escape_string($_POST['planting']);
    $watering_schedule = $conn->real_escape_string($_POST['watering_schedule']);
    $nutrients_for_soil = $conn->real_escape_string($_POST['nutrients_for_soil']);
    $disease = $conn->real_escape_string($_POST['disease']);
    $harvest_time = $conn->real_escape_string($_POST['harvest_time']);
    $benefits = $conn->real_escape_string($_POST['benefits']);

    // Check if vegetable details exist
    $checkSql = "SELECT id FROM vegetabledetails WHERE vegetable_id = ? AND name = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("is", $vegetable_id, $name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "Vegetable details do not exist in the database."]);
        exit;
    }
    $stmt->close();

    // Update vegetable details
    $updateSql = "UPDATE vegetabledetails SET container_liters = ?, typeofsoil = ?, planting = ?, watering_schedule = ?, nutrients_for_soil = ?, disease = ?, harvest_time = ?, benefits = ? WHERE vegetable_id = ? AND name = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssssssssis", $container_liters, $type_of_soil, $planting, $watering_schedule, $nutrients_for_soil, $disease, $harvest_time, $benefits, $vegetable_id, $name);

    if ($stmt->execute()) {
        // Retrieve the updated data
        $fetchSql = "SELECT * FROM vegetabledetails WHERE vegetable_id = ? AND name = ?";
        $fetchStmt = $conn->prepare($fetchSql);
        $fetchStmt->bind_param("is", $vegetable_id, $name);
        $fetchStmt->execute();
        $result = $fetchStmt->get_result();
        $vegetableDetails = $result->fetch_assoc();
        
        echo json_encode([
            "success" => true,
            "message" => "Vegetable details updated successfully!",
            "data" => $vegetableDetails
        ]);
        
        $fetchStmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    }

    $stmt->close();
}

$conn->close();
?>
