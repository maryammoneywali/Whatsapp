<?php
session_start();
if(!isset($_SESSION['user_id'])){
    exit("Not logged in");
}
include('config.php');

$current_user_id = $_SESSION['user_id'];
if(isset($_GET['chat_with'])){
    $chat_with = (int)$_GET['chat_with'];
    $sql = "SELECT * FROM messages WHERE (sender_id=$current_user_id AND receiver_id=$chat_with) OR (sender_id=$chat_with AND receiver_id=$current_user_id) ORDER BY timestamp ASC";
    $result = $conn->query($sql);
    if($result->num_rows > 0){
        while($msg = $result->fetch_assoc()){
            $class = ($msg['sender_id'] == $current_user_id) ? 'sent' : 'received';
            echo "<div class='message $class' id='msg-{$msg['id']}'>";
            // If the message is a reply, fetch and display the original message
            if(!empty($msg['reply_to'])){
                $replyQuery = "SELECT message FROM messages WHERE id = " . (int)$msg['reply_to'];
                $replyResult = $conn->query($replyQuery);
                $replyMessage = "";
                if($replyResult && $replyResult->num_rows > 0){
                   $replyRow = $replyResult->fetch_assoc();
                   $replyMessage = $replyRow['message'];
                }
                echo "<div class='reply-box'>Reply: " . htmlspecialchars($replyMessage) . "</div>";
            }
            echo "<span class='msg-text'>" . htmlspecialchars($msg['message']) . "</span>";
            echo "<button class='reply-btn' onclick=\"setReply({$msg['id']}, '".addslashes(htmlspecialchars($msg['message']))."')\">Reply</button>";
            echo "<br><small>" . date("H:i", strtotime($msg['timestamp'])) . "</small>";
            echo "</div>";
        }
    }
}
?>
