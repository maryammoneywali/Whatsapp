<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('config.php');

if(isset($_POST['login'])){
    $name = $conn->real_escape_string($_POST['name']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE name='$name' LIMIT 1";
    $result = $conn->query($sql);
    if($result && $result->num_rows > 0){
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['unique_number'] = $user['unique_number'];
            $_SESSION['username'] = $user['name'];
            header("Location: chat.php");
            exit();
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - WhatsApp Clone</title>
    <style>
        /* Internal CSS for login page */
        body { 
            font-family: Arial, sans-serif; 
            background: #f0f0f0; 
            margin: 0;
            padding: 0;
        }
        .container { 
            width: 300px; 
            margin: 100px auto; 
            background: #fff; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.3); 
        }
        h2 { 
            text-align: center; 
        }
        input[type="text"], input[type="password"] { 
            width: 100%; 
            padding: 10px; 
            margin: 10px 0; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
        }
        input[type="submit"] { 
            width: 100%; 
            padding: 10px; 
            background: #25D366; 
            border: none; 
            border-radius: 5px; 
            color: #fff; 
            font-weight: bold; 
            cursor: pointer; 
            transition: background 0.3s; 
        }
        input[type="submit"]:hover { 
            background: #1ebe5d; 
        }
        .error { 
            color: red; 
            text-align: center; 
        }
        p { 
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Login</h2>
    <?php if(isset($error)) { echo "<p class='error'>$error</p>"; } ?>
    <form method="post" action="login.php">
        <input type="text" name="name" placeholder="Your Name" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" name="login" value="Login">
    </form>
    <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
</div>
</body>
</html>
