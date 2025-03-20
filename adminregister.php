<?php
include('db.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for required fields
    if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['password'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Check if email already exists in the adminregister table
        $emailCheck = $conn->prepare("SELECT id FROM adminregister WHERE email = ?");
        $emailCheck->bind_param("s", $email);
        $emailCheck->execute();
        $emailCheckResult = $emailCheck->get_result();

        if ($emailCheckResult->num_rows > 0) {
            echo json_encode(["success" => false, "message" => "Admin with this email ID already exists."]);
        } else {
            // Hash the password before storing it
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert data into the adminregister table
            $stmt = $conn->prepare("INSERT INTO adminregister (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashedPassword);

            if ($stmt->execute()) {
                // Fetch the newly inserted record
                $last_id = $conn->insert_id;
                $result = $conn->query("SELECT id, name, email FROM adminregister WHERE id = $last_id");

                if ($result && $row = $result->fetch_assoc()) {
                    echo json_encode(["success" => true, "message" => "Admin registered successfully.", "data" => $row]);
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
