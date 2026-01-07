<?php
// Allow CORS (frontend <-> backend communication)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Debug errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle preflight (OPTIONS request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'db.php';

// Only allow POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Read JSON input
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->full_name) && !empty($data->email) && !empty($data->password)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data->email]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Email already registered"
            ]);
            exit;
        }

        // Hash password
        $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$data->full_name, $data->email, $hashed_password])) {
            echo json_encode([
                "status" => "success",
                "message" => "Registration successful"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Registration failed"
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "All fields are required"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
}
