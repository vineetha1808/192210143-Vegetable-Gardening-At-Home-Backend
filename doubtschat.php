<?php
include('db.php'); // Ensure this file contains database connection setup
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (isset($_POST['register_id']) && isset($_POST['message'])) {
        $register_id = intval($_POST['register_id']);
        $message = trim($_POST['message']);

        // Check if register_id exists in the register table
        $nameCheck = $conn->prepare("SELECT name FROM register WHERE id = ?");
        $nameCheck->bind_param("i", $register_id);
        $nameCheck->execute();
        $nameCheckResult = $nameCheck->get_result();

        if ($nameCheckResult->num_rows > 0) {
            $row = $nameCheckResult->fetch_assoc();
            $name = $row['name']; // Get the user's name

            // Insert the new message into the doubtschat table (Allows duplicate messages)
            $sql = "INSERT INTO doubtschat (register_id, message) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $register_id, $message);

            if ($stmt->execute()) {
                echo json_encode([
                    "success" => true,
                    "message" => "Message sent successfully.",
                    "data" => [
                        "register_id" => $register_id,
                        "name" => $name,
                        "message" => $message
                    ]
                ]);
            } else {
                echo json_encode(["error" => "Error: " . $stmt->error]);
            }
        } else {
            echo json_encode(["error" => "User not found. Please register first."]);
        }
    } else {
        echo json_encode(["error" => "Invalid input or missing required fields."]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch all messages with user names
    $sql = "SELECT d.id, d.register_id, r.name, d.message, d.created_at 
            FROM doubtschat d
            JOIN register r ON d.register_id = r.id
            ORDER BY d.created_at ASC";

    $result = $conn->query($sql);
    if ($result) {
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        echo json_encode(["messages" => $messages]);
    } else {
        echo json_encode(["error" => "Error: " . $conn->error]);
    }
}
?>
