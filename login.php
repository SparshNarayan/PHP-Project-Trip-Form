<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "trip");

if(isset($_POST['logout'])){
    session_destroy();
    header("Location: login.php");
    exit;
}

if(isset($_SESSION['loggedin'])){
    header("Location: index.php"); exit;
}

$login_error = "";
if(isset($_POST['login_user'])){
    $user = $_POST['login_user'];
    $pass = $_POST['login_pass'];

    $stmt = $con->prepare("SELECT username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if($row && password_verify($pass, $row['password'])){
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $row['username'];
        header("Location: index.php");
        exit;
    } else {
        $login_error = "Invalid username or password.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Doon University Trip Planner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">✈</div>
        <h2>Welcome Back</h2>
        <p class="auth-sub">Login to access the Doon University Trip Portal</p>

        <?php if($login_error): ?>
        <div class="alert-error" style="margin-bottom:20px;">
            <span class="alert-icon">⚠️</span>
            <div><?php echo htmlspecialchars($login_error); ?></div>
        </div>
        <?php endif; ?>

        <form method="post" id="loginForm">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="login_user" placeholder="your_username" required autocomplete="username">
            </div>
            <div class="form-group" style="position:relative;">
                <label>Password</label>
                <input type="password" name="login_pass" id="login_pass" placeholder="••••••••" required autocomplete="current-password">
                <span class="password-toggle" onclick="togglePassword('login_pass')">👁</span>
            </div>
            <button class="btn" style="margin-top:8px;">
                <span>Login</span> <span>→</span>
            </button>
        </form>

        <p class="auth-link">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>
<script>
function togglePassword(id) {
    const f = document.getElementById(id);
    f.type = f.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>