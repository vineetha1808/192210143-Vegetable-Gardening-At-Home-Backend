<?php
include('db.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for required fields
    if (
        isset($_POST['register_id']) &&
        isset($_POST['overall_experience']) &&
        isset($_POST['user_friendly']) &&
        isset($_POST['helpful_features']) &&
        isset($_POST['tips_accuracy']) &&
        isset($_POST['recommendation_rating'])
    ) {
        // Get and sanitize input values
        $register_id = (int)$_POST['register_id']; // Ensure it's an integer
        $overall_experience = $conn->real_escape_string($_POST['overall_experience']);
        $user_friendly = $conn->real_escape_string($_POST['user_friendly']);
        
        // Ensure helpful_features is properly formatted (convert array to string)
        if (is_array($_POST['helpful_features'])) {
            $helpful_features = implode(', ', array_map([$conn, 'real_escape_string'], $_POST['helpful_features']));
        } else {
            $helpful_features = $conn->real_escape_string($_POST['helpful_features']);
        }

        $tips_accuracy = $conn->real_escape_string($_POST['tips_accuracy']);
        $recommendation_rating = (int)$conn->real_escape_string($_POST['recommendation_rating']);

        // Check if the register_id exists in the register table
        $userCheck = $conn->prepare("SELECT id FROM register WHERE id = ?");
        $userCheck->bind_param("i", $register_id);
        $userCheck->execute();
        $userResult = $userCheck->get_result();

        if ($userResult->num_rows === 0) {
            echo json_encode(["success" => false, "message" => "User not found. Please register first."]);
            exit;
        }

        // Check if the same feedback already exists
        $checkSql = "SELECT id FROM feedbackform WHERE 
                     register_id = ? AND
                     overall_experience = ? AND
                     user_friendly = ? AND
                     helpful_features = ? AND
                     tips_accuracy = ? AND
                     recommendation_rating = ?";
        $stmt = $conn->prepare($checkSql);
        
        if ($stmt) {
            $stmt->bind_param("issssi", $register_id, $overall_experience, $user_friendly, $helpful_features, $tips_accuracy, $recommendation_rating);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                echo json_encode(["success" => false, "message" => "Feedback already submitted."]);
            } else {
                // Insert feedback data into the database
                $sql = "INSERT INTO feedbackform (register_id, overall_experience, user_friendly, helpful_features, tips_accuracy, recommendation_rating) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issssi", $register_id, $overall_experience, $user_friendly, $helpful_features, $tips_accuracy, $recommendation_rating);
                
                if ($stmt->execute()) {
                    $last_id = $conn->insert_id; // Get last inserted feedback ID
                    $result = $conn->query("SELECT * FROM feedbackform WHERE id = $last_id");

                    if ($result && $row = $result->fetch_assoc()) {
                        // Convert helpful_features back into an array for JSON response
                        $row['helpful_features'] = !empty($row['helpful_features']) ? explode(', ', $row['helpful_features']) : [];

                        echo json_encode(["success" => true, "message" => "Feedback submitted successfully.", "data" => $row]);
                    } else {
                        echo json_encode(["success" => false, "message" => "Error fetching inserted data."]);
                    }
                } else {
                    echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
                }
            }
        } else {
            echo json_encode(["success" => false, "message" => "Error preparing the check query: " . $conn->error]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid input or missing required fields."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method. Use POST."]);
}

$conn->close();
?>
