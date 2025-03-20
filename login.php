<?php
include('db.php');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        echo json_encode(["success" => false, "message" => "Invalid JSON input."]);
        exit();
    }

    if (isset($data['email']) && isset($data['password'])) {
        $email = $conn->real_escape_string($data['email']);
        $password = $conn->real_escape_string($data['password']);

        $stmt = $conn->prepare("SELECT id, name, email, password FROM register WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if ($password === $user['password']) {
                echo json_encode([
                    "success" => true,
                    "message" => "Login successful.",
                    "data" => [
                        "id" => $user['id'],
                        "name" => $user['name'],
                        "email" => $user['email']
                    ]
                ]);
            } else {
                echo json_encode(["success" => false, "message" => "Invalid email or password."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Invalid email or password."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Email and password are required."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method. Use POST."]);
}

$conn->close();
?>
