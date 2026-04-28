<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "trip");

if(isset($_POST['login_user'])){
    $user = $_POST['login_user'];
    $pass = $_POST['login_pass'];

    $sql = "SELECT * FROM users WHERE username='$user'";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);

    if($row && password_verify($pass, $row['password'])){
        $_SESSION['loggedin'] = true;
        header("Location: index.php"); // redirect after login
    } else {
        echo "<script>alert('Invalid Login');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Login</h2>
    <form method="post">
        <input type="text" name="login_user" placeholder="Username" required>
        <input type="password" name="login_pass" placeholder="Password" required>
        <button class="btn">Login</button>
    </form>

    <p>Don't have an account?</p>
    <a href="register.php"><button class="btn">Register</button></a>
</div>

</body>
</html>