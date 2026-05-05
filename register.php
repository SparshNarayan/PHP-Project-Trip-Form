<?php
$con = mysqli_connect("localhost", "root", "", "trip");
$reg_error = "";

if(isset($_POST['reg_user'])){
    $user = trim($_POST['reg_user']);
    $pass_raw = $_POST['reg_pass'];

    // Password strength validation
    if(strlen($pass_raw) < 8){
        $reg_error = "Password must be at least 8 characters.";
    } elseif(strlen($user) < 3){
        $reg_error = "Username must be at least 3 characters.";
    } else {
        $pass = password_hash($pass_raw, PASSWORD_DEFAULT);
        $check = $con->prepare("SELECT username FROM users WHERE username = ?");
        $check->bind_param("s", $user);
        $check->execute();
        if($check->get_result()->num_rows > 0){
            $reg_error = "Username already taken. Try another.";
        } else {
            $stmt = $con->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $user, $pass);
            if($stmt->execute()){
                // Auto-login after registration
                session_start();
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $user;
                header("Location: index.php");
                exit;
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Doon University Trip Planner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">🎓</div>
        <h2>Create Account</h2>
        <p class="auth-sub">Join the Doon University Trip Portal community</p>

        <?php if($reg_error): ?>
        <div class="alert-error" style="margin-bottom:20px;">
            <span class="alert-icon">⚠️</span>
            <div><?php echo htmlspecialchars($reg_error); ?></div>
        </div>
        <?php endif; ?>

        <form method="post" id="regForm">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="reg_user" placeholder="choose_a_username" required minlength="3"
                       value="<?php echo isset($_POST['reg_user']) ? htmlspecialchars($_POST['reg_user']) : ''; ?>">
                <span class="field-hint">Minimum 3 characters</span>
            </div>
            <div class="form-group" style="position:relative;">
                <label>Password</label>
                <input type="password" name="reg_pass" id="reg_pass" placeholder="Min 8 characters" required minlength="8">
                <span class="password-toggle" onclick="togglePassword('reg_pass')">👁</span>
            </div>
            <!-- Password strength indicator -->
            <div style="margin-bottom: 20px;">
                <div style="height:4px; background:rgba(255,255,255,0.1); border-radius:4px; overflow:hidden;">
                    <div id="strength-bar" style="height:100%; width:0; border-radius:4px; transition:all 0.3s;"></div>
                </div>
                <span id="strength-label" style="font-size:11px; color:rgba(255,255,255,0.4); margin-top:4px; display:block;"></span>
            </div>
            <button class="btn">
                <span>Create Account</span> <span>→</span>
            </button>
        </form>

        <p class="auth-link">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>
<script>
function togglePassword(id) {
    const f = document.getElementById(id);
    f.type = f.type === 'password' ? 'text' : 'password';
}

// Password strength meter
const passInput = document.getElementById('reg_pass');
const bar = document.getElementById('strength-bar');
const label = document.getElementById('strength-label');

passInput.addEventListener('input', () => {
    const val = passInput.value;
    let score = 0;
    if(val.length >= 8) score++;
    if(/[A-Z]/.test(val)) score++;
    if(/[0-9]/.test(val)) score++;
    if(/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { pct:'25%', color:'#ef4444', text:'Weak' },
        { pct:'50%', color:'#f59e0b', text:'Fair' },
        { pct:'75%', color:'#3b82f6', text:'Good' },
        { pct:'100%', color:'#10b981', text:'Strong' }
    ];
    const lvl = levels[score - 1] || { pct:'0%', color:'transparent', text:'' };
    bar.style.width = lvl.pct;
    bar.style.background = lvl.color;
    label.textContent = lvl.text;
    label.style.color = lvl.color;
});
</script>
</body>
</html>