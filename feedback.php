<?php
// Include database connection
include('db.php');
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    if (
        isset($_POST['overall_experience']) &&
        isset($_POST['user_friendly']) &&
        isset($_POST['helpful_features']) &&
        isset($_POST['tips_accuracy']) &&
        isset($_POST['recommendation_rating'])
    ) {
        $overall_experience = $conn->real_escape_string($_POST['overall_experience']);
        $user_friendly = $conn->real_escape_string($_POST['user_friendly']);
        $helpful_features = implode(', ', $_POST['helpful_features']); // Handle multiple selections
        $tips_accuracy = $conn->real_escape_string($_POST['tips_accuracy']);
        $recommendation_rating = (int)$_POST['recommendation_rating'];

        // Insert data into the database
        $sql = "INSERT INTO feedback (overall_experience, user_friendly, helpful_features, tips_accuracy, recommendation_rating) 
                VALUES ('$overall_experience', '$user_friendly', '$helpful_features', '$tips_accuracy', '$recommendation_rating')";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(array("message" => "Feedback submitted successfully."));
        } else {
            echo json_encode(array("message" => "Error: " . $conn->error));
        }
    } else {
        echo json_encode(array("message" => "All fields are required."));
    }
} else {
    echo json_encode(array("message" => "Invalid request method. Use POST."));
}

$conn->close();
?>
