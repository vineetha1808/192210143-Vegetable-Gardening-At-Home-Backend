<?php
// Include database connection
include('db.php'); // Ensure db.php contains the connection to your database
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Check if a specific vegetable_id is requested
    $vegetable_id = isset($_GET['vegetable_id']) ? $conn->real_escape_string($_GET['vegetable_id']) : null;

    if ($vegetable_id) {
        // Fetch details of a specific vegetable
        $sql = "SELECT * FROM vegetabledetails WHERE vegetable_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $vegetable_id);
    } else {
        // Fetch all vegetable details
        // $sql = "SELECT * FROM vegetabledetails";
        // $stmt = $conn->prepare($sql);
    }

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $vegetableDetails = [];

        while ($row = $result->fetch_assoc()) {
            $vegetableDetails[] = $row;
        }

        echo json_encode([
            "success" => true,
            "message" => $vegetable_id ? "Vegetable details fetched successfully!" : "All vegetable details fetched successfully!",
            "data" => $vegetableDetails[0] ?? ''
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    }

    $stmt->close();
}

$conn->close();
?>
