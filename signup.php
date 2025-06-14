<?php
session_start();
include('config.php');

if(isset($_POST['signup'])){
    $name = $conn->real_escape_string($_POST['name']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Insert the new user into the database
    $sql = "INSERT INTO users (name, password) VALUES ('$name', '$password')";
    if($conn->query($sql) === TRUE){
        $user_id = $conn->insert_id;
        // Generate unique number based on the user ID (starting at 1001)
        $unique_number = "+786-" . (1000 + $user_id);
        $conn->query("UPDATE users SET unique_number='$unique_number' WHERE id=$user_id");
        
        // Save username, unique number and user id in session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['unique_number'] = $unique_number;
        $_SESSION['username'] = $name;
        header("Location: chat.php");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Signup - WhatsApp Clone</title>
    <style>
        /* Internal CSS for signup page */
        body { font-family: Arial, sans-serif; background: #f0f0f0; }
        .container { width: 300px; margin: 100px auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.3); }
        h2 { text-align: center; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        input[type="submit"] { width: 100%; padding: 10px; background: #25D366; border: none; border-radius: 5px; color: #fff; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        input[type="submit"]:hover { background: #1ebe5d; }
        .error { color: red; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <h2>Signup</h2>
    <?php if(isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    <form method="post" action="signup.php">
        <input type="text" name="name" placeholder="Your Name" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" name="signup" value="Sign Up">
    </form>
    <p style="text-align:center;">Already have an account? <a href="login.php">Login</a></p>
</div>
</body>
</html>
