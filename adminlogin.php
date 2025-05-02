<?php 
// Include database connection
include('db.php');
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    // Check if email and password are provided
    if (isset($_POST['email']) && isset($_POST['password'])) 
    {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Fetch the admin data from the database
        $sql = "SELECT id, email, password FROM adminlogin WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) 
        {
            $admin = $result->fetch_assoc();
            
            // Verify the hashed password
            if ($password == $admin['password']) {
                echo json_encode([
                    "success" => true,
                    "message" => "Admin login successful.",
                    "data" => [
                        "id" => $admin['id'],
                        "email" => $admin['email']
                    ]
                ]);
            } else {
                echo json_encode(["success" => false, "message" => "Invalid email or password."]);
            }
        } 
        else 
        {
            echo json_encode(["success" => false, "message" => "Invalid email or password."]);
        }
    } 
    else 
    {
        echo json_encode(["success" => false, "message" => "Email and password are required."]);
    }
} 
else 
{
    echo json_encode(["success" => false, "message" => "Invalid request method. Use POST."]);
}

$conn->close();
?>
