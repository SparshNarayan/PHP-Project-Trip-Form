<?php
session_start();

if(!isset($_SESSION['loggedin'])){
    header("Location: login.php"); // block access
}

$con = mysqli_connect("localhost", "root", "", "trip");

if(isset($_POST['logout'])){
    session_destroy();
    header("Location: login.php");
}

$insert = false;

if(isset($_POST['name'])){
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $phone_no = $_POST['phone'];
    $other = $_POST['desc'];

    $sql = "INSERT INTO participants(name, age, gender, email, phone_no, other, date)
            VALUES ('$name', '$age', '$gender', '$email', '$phone_no', '$other', CURRENT_TIMESTAMP())";

    if(mysqli_query($con, $sql)){
        $insert = true;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Form</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <form method="post">
        <button name="logout" class="btn">Logout</button>
    </form>

    <h1>Trip Form</h1>

    <?php if($insert){ echo "<p class='submitMessage'>Submitted!</p>"; } ?>

    <form method="post">
        <input type="text" name="name" placeholder="Name" required>
        <input type="text" name="age" placeholder="Age">
        <input type="text" name="gender" placeholder="Gender">
        <input type="email" name="email" placeholder="Email">
        <input type="text" name="phone" placeholder="Phone">
        <textarea name="desc"></textarea>
        <button class="btn">Submit</button>
    </form>

</div>

</body>
</html>