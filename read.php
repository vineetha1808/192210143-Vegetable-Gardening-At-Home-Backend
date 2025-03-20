<?php
include('db.php');

// Set the content type to JSON
header('Content-Type: application/json');

// GET - Retrieve all records
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // SQL query to fetch users
    $sql = "SELECT id, name, email, password FROM users";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Initialize an array to hold the results
        $users = array();

        // Fetch all rows from the result
        while($row = $result->fetch_assoc()) {
            // Add each user to the array
            $users[] = $row;
        }

        // Return a success message along with the users as a JSON response
        echo json_encode(array(
            "message" => "Records retrieved successfully.",
            "data" => $users
        ));
    } else {
        // If no records are found, return a message with an empty array
        echo json_encode(array(
            "message" => "No records found.",
            "data" => array()
        ));
    }
}
?>
