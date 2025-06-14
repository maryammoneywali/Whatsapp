<?php
$servername = "localhost";
$username = "uqkwq5axfcp2c";
$password = "vsewloghrf6l";
$dbname = "dbngurqndsmmbl";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Automatically create the users table if it doesn't exist
$createUsersTable = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    unique_number VARCHAR(20)
)";
if (!$conn->query($createUsersTable)) {
    die("Error creating users table: " . $conn->error);
}

// Automatically create the messages table if it doesn't exist
$createMessagesTable = "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!$conn->query($createMessagesTable)) {
    die("Error creating messages table: " . $conn->error);
}
?>
