<?php 
include('db.php');
header('Content-Type: application/json');

// Enable full error reporting for development
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $required_fields = ['vegetable_id', 'name', 'container_liters', 'typeofsoil', 'soil_preparation', 'planting', 'watering_schedule', 'nutrients_for_soil', 'disease', 'harvest_time', 'benefits'];

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
    $soil_preparation = $conn->real_escape_string($_POST['soil_preparation']);
    $planting = $conn->real_escape_string($_POST['planting']);
    $watering_schedule = $conn->real_escape_string($_POST['watering_schedule']);
    $nutrients_for_soil = $conn->real_escape_string($_POST['nutrients_for_soil']);
    $disease = $conn->real_escape_string($_POST['disease']);
    $harvest_time = $conn->real_escape_string($_POST['harvest_time']);
    $benefits = $conn->real_escape_string($_POST['benefits']);

    $upload_dir = "uploads/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    function uploadImage($fileInputName, $upload_dir) {
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {
            $image_name = basename($_FILES[$fileInputName]['name']);
            $image_path = $upload_dir . time() . "_" . $image_name;
            if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $image_path)) {
                return $image_path;
            }
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
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Check if vegetable details already exist
    $checkDetailsSql = "SELECT id FROM vegetabledetails WHERE vegetable_id = ? AND name = ?";
    $stmt = $conn->prepare($checkDetailsSql);
    $stmt->bind_param("is", $vegetable_id, $name);
    $stmt->execute();
    $stmt->store_result();
    $detailsExist = $stmt->num_rows > 0;
    $stmt->close();

    if ($detailsExist) {
        // Update existing record
        $updateSql = "UPDATE vegetabledetails SET 
            container_liters = ?, typeofsoil = ?, soil_preparation = ?, planting = ?, 
            watering_schedule = ?, nutrients_for_soil = ?, disease = ?, harvest_time = ?, benefits = ?";

        if ($container_image) $updateSql .= ", container_image = ?";
        if ($soil_image) $updateSql .= ", soil_image = ?";
        if ($planting_image) $updateSql .= ", planting_image = ?";

        $updateSql .= " WHERE vegetable_id = ? AND name = ?";

        $stmt = $conn->prepare($updateSql);

        // Build dynamic binding
        $types = "sssssssss";
        $params = [
            &$container_liters, &$type_of_soil, &$soil_preparation, &$planting,
            &$watering_schedule, &$nutrients_for_soil, &$disease, &$harvest_time, &$benefits
        ];

        if ($container_image) { $types .= "s"; $params[] = &$container_image; }
        if ($soil_image) { $types .= "s"; $params[] = &$soil_image; }
        if ($planting_image) { $types .= "s"; $params[] = &$planting_image; }

        $types .= "is";
        $params[] = &$vegetable_id;
        $params[] = &$name;

        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Vegetable details updated successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Update executed but no rows affected (data may be identical)."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Update failed: " . $stmt->error]);
        }
        $stmt->close();

    } else {
        // Insert new record
        $insertSql = "INSERT INTO vegetabledetails 
        (vegetable_id, name, container_liters, typeofsoil, soil_preparation, planting, watering_schedule, nutrients_for_soil, disease, harvest_time, benefits, container_image, soil_image, planting_image) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param(
            "isssssssssssss",
            $vegetable_id,
            $name,
            $container_liters,
            $type_of_soil,
            $soil_preparation,
            $planting,
            $watering_schedule,
            $nutrients_for_soil,
            $disease,
            $harvest_time,
            $benefits,
            $container_image,
            $soil_image,
            $planting_image
        );

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Vegetable details added successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Insert executed but no rows affected."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Insert failed: " . $stmt->error]);
        }
        $stmt->close();
    }
}

$conn->close();
?>
