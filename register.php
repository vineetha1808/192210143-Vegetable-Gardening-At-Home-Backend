<?php
include('db.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for required fields
    if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Check if email already exists
        $emailCheck = $conn->prepare("SELECT id FROM register WHERE email = ?");
        $emailCheck->bind_param("s", $email);
        $emailCheck->execute();
        $emailCheckResult = $emailCheck->get_result();

        if ($emailCheckResult->num_rows > 0) {
            echo json_encode(["success" => false, "message" => "Email ID already exists."]);
        } else {
            // Insert data into the database
            $stmt = $conn->prepare("INSERT INTO register (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $password);

            if ($stmt->execute()) {
                // Fetch the newly inserted record
                $last_id = $conn->insert_id;
                $result = $conn->query("SELECT id, name, email FROM register WHERE id = $last_id");

                if ($result && $row = $result->fetch_assoc()) {
                    echo json_encode(["success" => true, "message" => "Registered successfully.", "data" => $row]);
                } else {
                    echo json_encode(["success" => false, "message" => "Error fetching inserted data."]);
                }
            } else {
                echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
            }
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid input or missing required fields."]);
    }
}
?>
