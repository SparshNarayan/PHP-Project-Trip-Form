<?php
$con = mysqli_connect("localhost", "root", "", "trip");

if(isset($_POST['reg_user'])){
    $user = $_POST['reg_user'];
    $pass = password_hash($_POST['reg_pass'], PASSWORD_DEFAULT);

    $check = "SELECT * FROM users WHERE username='$user'";
    $result = mysqli_query($con, $check);

    if(mysqli_num_rows($result) > 0){
        echo "<script>alert('Username exists');</script>";
    } else {
        $sql = "INSERT INTO users (username, password) VALUES ('$user', '$pass')";
        if(mysqli_query($con, $sql)){
            echo "<script>alert('Registered Successfully');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Register</h2>
    <form method="post">
        <input type="text" name="reg_user" placeholder="Username" required>
        <input type="password" name="reg_pass" placeholder="Password" required>
        <button class="btn">Register</button>
    </form>

    <p>Already have an account?</p>
    <a href="login.php"><button class="btn">Login</button></a>
</div>

</body>
</html>