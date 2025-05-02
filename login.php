<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$host = "localhost";
$username = "root";
$password = "vinitha";
$dbname = "veg_garden"; // ✔️ No backticks

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed."]));
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->password)) {
    echo json_encode(["success" => false, "message" => "Email and password are required."]);
    exit();
}

$email = $conn->real_escape_string($data->email);
$password = $conn->real_escape_string($data->password);

// Make sure the table name is correct (use `login` if that's your table)
$sql = "SELECT * FROM register WHERE email = ? AND password = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $password);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(["success" => true, "message" => "Login successful", 'username' => $row['name']]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid email or password"]);
}

$conn->close();
