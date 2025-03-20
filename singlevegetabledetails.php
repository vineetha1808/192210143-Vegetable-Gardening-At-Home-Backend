<?php
// Include database connection
include('db.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Check if vegetable_id is provided
    if (!isset($_GET['vegetable_id']) || empty($_GET['vegetable_id'])) {
        echo json_encode(["success" => false, "message" => "vegetable_id is required."]);
        exit;
    }

    $vegetable_id = intval($_GET['vegetable_id']);

    // Prepare SQL statement to fetch vegetable details
    $sql = "SELECT `id`, `vegetable_id`, `name`, `container_liters`, `typeofsoil`, `planting`, `watering_schedule`, `nutrients_for_soil`, `disease`, `harvest_time`, `benefits`
            FROM `vegetabledetails`
            WHERE `vegetable_id` = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $vegetable_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $vegetableDetails = $result->fetch_assoc();
        echo json_encode(["success" => true, "data" => $vegetableDetails]);
    } else {
        echo json_encode(["success" => false, "message" => "No vegetable found with the provided ID."]);
    }

    $stmt->close();
}

$conn->close();
?>
