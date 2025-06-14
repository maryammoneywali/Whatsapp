<?php
session_start();
if(!isset($_SESSION['user_id'])){
    exit("Not logged in");
}
include('config.php');
$current_user_id = $_SESSION['user_id'];

// Check if the necessary POST parameters are received
if(isset($_POST['chat_with']) && isset($_POST['message'])){
    $chat_with = (int)$_POST['chat_with'];
    $message = $conn->real_escape_string($_POST['message']);
    $reply_to = isset($_POST['reply_to']) ? (int)$_POST['reply_to'] : 0;
    
    // Log the received parameters for debugging (check your error log)
    error_log("User $current_user_id sending message to $chat_with: '$message' with reply_to: $reply_to");
    
    if(!empty($message)){
        if($reply_to > 0){
            $sql = "INSERT INTO messages (sender_id, receiver_id, message, reply_to) VALUES ($current_user_id, $chat_with, '$message', $reply_to)";
        } else {
            $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES ($current_user_id, $chat_with, '$message')";
        }
        
        if(!$conn->query($sql)){
            // Log and echo error if query fails
            error_log("Error sending message: " . $conn->error);
            echo "Error: " . $conn->error;
        } else {
            echo "OK";
        }
    } else {
        echo "Empty message";
    }
} else {
    echo "Invalid parameters";
}
?>
