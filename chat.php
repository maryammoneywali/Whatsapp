<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}
include('config.php');

$current_user_id = $_SESSION['user_id'];
$current_unique_number = $_SESSION['unique_number'];
$current_username = $_SESSION['username'];

// Get list of contacts (all other users)
$contacts = [];
$sql = "SELECT id, name, unique_number FROM users WHERE id != $current_user_id";
$result = $conn->query($sql);
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $contacts[] = $row;
    }
}

// Determine chat partner from GET parameter
$chat_with = isset($_GET['chat_with']) ? (int)$_GET['chat_with'] : 0;
$chat_partner = null;
if($chat_with > 0){
    $sql = "SELECT id, name, unique_number FROM users WHERE id = $chat_with";
    $res = $conn->query($sql);
    if($res->num_rows > 0){
        $chat_partner = $res->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat - WhatsApp Clone</title>
    <style>
        /* Internal CSS for chat interface */
        body { margin: 0; font-family: Arial, sans-serif; background: #e5ddd5; }
        .header { background: #075E54; color: #fff; padding: 10px; text-align: center; }
        .container { display: flex; height: calc(100vh - 50px); }
        .contacts { width: 30%; background: #fff; overflow-y: auto; border-right: 1px solid #ccc; }
        .contacts ul { list-style: none; padding: 0; margin: 0; }
        .contacts li { padding: 10px; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: background 0.3s; }
        .contacts li:hover { background: #f5f5f5; }
        .chat-box { width: 70%; display: flex; flex-direction: column; }
        .messages { flex: 1; padding: 10px; overflow-y: auto; }
        .message { margin: 5px 0; padding: 10px; border-radius: 10px; max-width: 60%; position: relative; }
        .sent { background: #dcf8c6; align-self: flex-end; }
        .received { background: #fff; align-self: flex-start; }
        .input-area { padding: 10px; background: #f0f0f0; }
        .input-area input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 20px; }
        .input-area button { background: #075E54; color: #fff; border: none; padding: 10px 20px; margin-top: 5px; border-radius: 20px; cursor: pointer; transition: background 0.3s; }
        .input-area button:hover { background: #064e45; }
        .contact-active { background: #e0f7fa; }
        /* Animation for messages */
        .message {
            opacity: 0;
            animation: fadeIn 0.5s forwards;
        }
        @keyframes fadeIn {
            to { opacity: 1; }
        }
        /* Reply box style for messages */
        .reply-box {
            background: #f1f1f1;
            padding: 5px;
            margin-bottom: 5px;
            border-left: 3px solid #075E54;
            font-size: 0.9em;
            color: #555;
        }
        /* Style for the reply preview above input */
        #replyPreview {
            background: #eee;
            padding: 5px;
            border-left: 3px solid #075E54;
            margin-bottom: 5px;
        }
        .reply-btn {
            background: none;
            border: none;
            color: #075E54;
            cursor: pointer;
            font-size: 0.8em;
            position: absolute;
            top: 5px;
            right: 5px;
        }
        /* Search box style */
        .search-box { padding: 10px; }
        .search-box input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 20px; }
    </style>
</head>
<body>
<div class="header">
    WhatsApp Clone - <?php echo htmlspecialchars($current_username); ?> (Your Number: <?php echo $current_unique_number; ?>) | 
    <a href="logout.php" style="color:#fff; text-decoration:none;">Logout</a>
</div>
<div class="container">
    <div class="contacts">
        <div class="search-box">
            <input type="text" id="search" placeholder="Search by Unique Number">
        </div>
        <ul id="contactList">
            <?php foreach($contacts as $contact): ?>
                <li onclick="window.location.href='chat.php?chat_with=<?php echo $contact['id']; ?>'" <?php if($chat_partner && $chat_partner['id'] == $contact['id']) echo 'class="contact-active"'; ?>>
                    <?php echo htmlspecialchars($contact['name']); ?> (<?php echo htmlspecialchars($contact['unique_number']); ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="chat-box">
        <div class="messages" id="messages">
            <?php
            // Load initial messages if a chat partner is selected
            if($chat_partner){
                $chat_with_id = $chat_partner['id'];
                $sql = "SELECT * FROM messages WHERE (sender_id=$current_user_id AND receiver_id=$chat_with_id) OR (sender_id=$chat_with_id AND receiver_id=$current_user_id) ORDER BY timestamp ASC";
                $res = $conn->query($sql);
                if($res->num_rows > 0){
                    while($msg = $res->fetch_assoc()){
                        $class = ($msg['sender_id'] == $current_user_id) ? 'sent' : 'received';
                        echo "<div class='message $class' id='msg-{$msg['id']}'>";
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
            } else {
                echo "<p style='text-align:center;'>Select a contact to start chatting.</p>";
            }
            ?>
        </div>
        <?php if($chat_partner): ?>
        <div class="input-area">
            <!-- Reply preview area -->
            <div id="replyPreview" style="display:none;">
                Replying to: <span id="replyText"></span>
                <button onclick="cancelReply()" style="background:none; border:none; color:#075E54; cursor:pointer;">Cancel</button>
            </div>
            <!-- Hidden field to hold reply message id -->
            <input type="hidden" id="reply_to" value="0">
            <input type="text" id="messageInput" placeholder="Type a message...">
            <button id="sendBtn">Send</button>
        </div>
        <?php endif; ?>
    </div>
</div>
<script>
// Function to set the reply: fills the hidden field and shows the preview
function setReply(messageId, messageText) {
    document.getElementById("reply_to").value = messageId;
    document.getElementById("replyPreview").style.display = "block";
    document.getElementById("replyText").innerText = messageText;
}

// Function to cancel the reply action
function cancelReply() {
    document.getElementById("reply_to").value = 0;
    document.getElementById("replyPreview").style.display = "none";
}

<?php if($chat_partner): ?>
let chatWith = <?php echo $chat_partner['id']; ?>;
function fetchMessages(){
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_messages.php?chat_with=" + chatWith, true);
    xhr.onload = function(){
        if(xhr.status === 200){
            document.getElementById("messages").innerHTML = xhr.responseText;
            document.getElementById("messages").scrollTop = document.getElementById("messages").scrollHeight;
        }
    }
    xhr.send();
}
setInterval(fetchMessages, 2000); // Poll every 2 seconds

document.getElementById("sendBtn").addEventListener("click", function(){
    let message = document.getElementById("messageInput").value;
    let reply_to = document.getElementById("reply_to").value;
    if(message.trim() !== ""){
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "send_message.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function(){
            if(xhr.status === 200){
                document.getElementById("messageInput").value = "";
                cancelReply(); // Reset reply after sending
                fetchMessages();
            }
        }
        xhr.send("chat_with=" + chatWith + "&message=" + encodeURIComponent(message) + "&reply_to=" + reply_to);
    }
});
<?php endif; ?>

// Search functionality for contacts
document.getElementById("search").addEventListener("keyup", function(){
    let filter = this.value.toUpperCase();
    let li = document.getElementById("contactList").getElementsByTagName("li");
    for(let i = 0; i < li.length; i++){
        let txtValue = li[i].textContent || li[i].innerText;
        if(txtValue.toUpperCase().indexOf(filter) > -1){
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
});
</script>
</body>
</html>
