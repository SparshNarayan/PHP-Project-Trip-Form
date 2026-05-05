<?php
session_start();

if(!isset($_SESSION['loggedin'])){
    header("Location: login.php"); exit;
}

// ─── ADMIN CREDENTIALS ───────────────────────────────────────────
// Aap yahan apna admin username aur password set karo
$ADMIN_USERNAME = "Sparsh Narayan";   // <-- apna username
$ADMIN_PASSWORD = "Sparsh@123";        // <-- apna admin password
// ─────────────────────────────────────────────────────────────────

if(isset($_POST['admin_user']) && isset($_POST['admin_pass'])){
    $entered_user = trim($_POST['admin_user']);
    $entered_pass = $_POST['admin_pass'];

    if($entered_user === $ADMIN_USERNAME && $entered_pass === $ADMIN_PASSWORD){
        $_SESSION['admin_verified'] = true;
        header("Location: admin.php");
        exit;
    } else {
        // Wrong credentials — redirect back with error flag
        header("Location: index.php?admin_error=1#");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>
