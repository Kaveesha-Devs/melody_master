<?php
session_start();
include("config/db.php");

if(isset($_POST['login'])){
    
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();

        if(password_verify($password, $row['password'])){
            
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['name'];

            echo "Login Success!";
        } else {
            echo "Wrong Password!";
        }
    } else {
        echo "User Not Found!";
    }
}
?>

<h2>Login</h2>
<form method="POST">
    Email: <input type="email" name="email"><br><br>
    Password: <input type="password" name="password"><br><br>
    <button type="submit" name="login">Login</button>
</form>