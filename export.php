<?php
session_start();
if(!isset($_SESSION['loggedin'])){ header("Location: login.php"); exit; }
$ADMIN_USERS = ['admin'];
if(!in_array($_SESSION['username'], $ADMIN_USERS)){ header("Location: index.php"); exit; }

$con = mysqli_connect("localhost", "root", "", "trip");
$result = $con->query("SELECT name, age, gender, email, phone_no, other, date FROM participants ORDER BY date DESC");

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="kgp_trip_participants_' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Name', 'Age', 'Gender', 'Email', 'Phone', 'Notes', 'Registered At']);

while($row = $result->fetch_assoc()){
    fputcsv($out, [
        $row['name'], $row['age'], $row['gender'],
        $row['email'], $row['phone_no'], $row['other'],
        date('d M Y H:i', strtotime($row['date']))
    ]);
}
fclose($out);