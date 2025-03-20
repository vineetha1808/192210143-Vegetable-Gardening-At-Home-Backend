<?php
include('db.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for required fields
    if (isset($_POST['name'])) {
        $name = $_POST['name'];

        // Check if the vegetable name exists in the database
        $vegetableCheck = $conn->prepare("SELECT id, name, image_path FROM vegetable WHERE name = ?");
        $vegetableCheck->bind_param("s", $name);
        $vegetableCheck->execute();
        $vegetableCheckResult = $vegetableCheck->get_result();

        if ($vegetableCheckResult->num_rows > 0) {
            // Vegetable found
            $row = $vegetableCheckResult->fetch_assoc();
            echo json_encode([
                "message" => "Vegetable found.",
                "data" => [
                    "id" => $row['id'],
                    "name" => $row['name'],
                    "image_path" => $row['image_path']
                ]
            ]);
        } else {
            // Vegetable not found
            echo json_encode(["message" => "Vegetable not found."]);
        }
    } else {
        // Missing required field
        echo json_encode(["message" => "Invalid input or missing vegetable name."]);
    }
} else {
    // Invalid request method
    echo json_encode(["message" => "Invalid request method. Use POST."]);
}

$conn->close();
?>
