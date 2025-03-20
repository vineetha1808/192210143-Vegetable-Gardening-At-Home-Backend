<?php 
// Include database connection
include('db.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate required fields
    $required_fields = ['vegetable_id', 'name', 'container_liters', 'typeofsoil', 'soil_preparation', 'planting', 'watering_schedule', 'nutrients_for_soil', 'disease', 'harvest_time', 'benefits'];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            echo json_encode(["success" => false, "message" => "$field is required."]);
            exit;
        }
    }

    // Sanitize input
    $vegetable_id = $conn->real_escape_string($_POST['vegetable_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $container_liters = $conn->real_escape_string($_POST['container_liters']);
    $type_of_soil = $conn->real_escape_string($_POST['typeofsoil']);
    $soil_preparation = $conn->real_escape_string($_POST['soil_preparation']);
    $planting = $conn->real_escape_string($_POST['planting']);
    $watering_schedule = $conn->real_escape_string($_POST['watering_schedule']);
    $nutrients_for_soil = $conn->real_escape_string($_POST['nutrients_for_soil']);
    $disease = $conn->real_escape_string($_POST['disease']);
    $harvest_time = $conn->real_escape_string($_POST['harvest_time']);
    $benefits = $conn->real_escape_string($_POST['benefits']);

    // Handle Image Uploads
    $upload_dir = "uploads/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    function uploadImage($fileInputName, $upload_dir) {
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {
            $image_name = basename($_FILES[$fileInputName]['name']);
            $image_path = $upload_dir . time() . "_" . $image_name;
            move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $image_path);
            return $image_path;
        }
        return null;
    }

    $container_image = uploadImage('container_image', $upload_dir);
    $soil_image = uploadImage('soil_image', $upload_dir);
    $planting_image = uploadImage('planting_image', $upload_dir);

    // Check if the vegetable exists
    $checkSql = "SELECT id FROM vegetable WHERE id = ? AND name = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("is", $vegetable_id, $name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "Vegetable ID or Name does not exist in the vegetable database."]);
        exit;
    }
    $stmt->close();

    // Check if vegetable details already exist
    $checkDetailsSql = "SELECT id FROM vegetabledetails WHERE vegetable_id = ? AND name = ?";
    $stmt = $conn->prepare($checkDetailsSql);
    $stmt->bind_param("is", $vegetable_id, $name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Vegetable details already exist in the database."]);
        exit;
    }
    $stmt->close();

    // Insert data into vegetable details table
    $insertSql = "INSERT INTO vegetabledetails (vegetable_id, name, container_liters, typeofsoil, soil_preparation, planting, watering_schedule, nutrients_for_soil, disease, harvest_time, benefits, container_image, soil_image, planting_image) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("isssssssssssss", $vegetable_id, $name, $container_liters, $type_of_soil, $soil_preparation, $planting, $watering_schedule, $nutrients_for_soil, $disease, $harvest_time, $benefits, $container_image, $soil_image, $planting_image);

    if ($stmt->execute()) {
        // Retrieve the inserted data
        $fetchSql = "SELECT * FROM vegetabledetails WHERE vegetable_id = ? AND name = ?";
        $fetchStmt = $conn->prepare($fetchSql);
        $fetchStmt->bind_param("is", $vegetable_id, $name);
        $fetchStmt->execute();
        $result = $fetchStmt->get_result();
        $vegetableDetails = $result->fetch_assoc();

        echo json_encode([
            "success" => true,
            "message" => "Vegetable details added successfully!",
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
