<?php
session_start();
if(!isset($_SESSION['loggedin'])){
    header("Location: login.php"); exit;
}
// Simple admin check - you can expand this with a role column in DB
$ADMIN_USERS = ['admin']; // Add admin usernames here
if(!in_array($_SESSION['username'], $ADMIN_USERS)){
    header("Location: index.php"); exit;
}

$con = mysqli_connect("localhost", "root", "", "trip");

// Delete participant
if(isset($_POST['delete_id'])){
    $del = $con->prepare("DELETE FROM participants WHERE id = ?");
    $del->bind_param("i", $_POST['delete_id']);
    $del->execute();
    header("Location: admin.php"); exit;
}

// Stats
$total = $con->query("SELECT COUNT(*) as c FROM participants")->fetch_assoc()['c'];
$male = $con->query("SELECT COUNT(*) as c FROM participants WHERE gender='Male'")->fetch_assoc()['c'];
$female = $con->query("SELECT COUNT(*) as c FROM participants WHERE gender='Female'")->fetch_assoc()['c'];
$today = $con->query("SELECT COUNT(*) as c FROM participants WHERE DATE(date)=CURDATE()")->fetch_assoc()['c'];

// Search & Filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$gender_filter = isset($_GET['gender']) ? $_GET['gender'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date';
$order = in_array($sort, ['name','age','date']) ? $sort : 'date';

$sql = "SELECT * FROM participants WHERE 1=1";
$params = [];
$types = "";
if($search){
    $sql .= " AND (name LIKE ? OR email LIKE ? OR phone_no LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like];
    $types = "sss";
}
if($gender_filter){
    $sql .= " AND gender = ?";
    $params[] = $gender_filter;
    $types .= "s";
}
$sql .= " ORDER BY $order DESC";

$stmt = $con->prepare($sql);
if($params){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Doon University Trip</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: var(--bg-dark); }
        .admin-wrap { max-width: 1100px; margin: 0 auto; padding: 100px 20px 60px; }
        .admin-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:40px; flex-wrap:wrap; gap:16px; }
        .admin-header h1 { font-family:var(--font-head); font-size:36px; font-weight:800; }
        .kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:36px; }
        .kpi { background:var(--glass); border:1px solid var(--glass-border); border-radius:16px; padding:22px 24px; }
        .kpi-num { font-family:var(--font-head); font-size:36px; font-weight:800; line-height:1; margin-bottom:6px; }
        .kpi-label { font-size:13px; color:var(--text-dim); }
        .kpi.blue .kpi-num { color:#3b82f6; }
        .kpi.cyan .kpi-num { color:#06b6d4; }
        .kpi.pink .kpi-num { color:#ec4899; }
        .kpi.gold .kpi-num { color:#f59e0b; }

        .controls { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:24px; align-items:center; }
        .controls input[type=text] { flex:1; min-width:200px; padding:10px 16px; font-size:14px; }
        .controls select { padding:10px 14px; font-size:14px; border-radius:10px; }
        .controls .btn { width:auto; padding:10px 24px; font-size:14px; }

        .data-table { width:100%; border-collapse:collapse; }
        .data-table th { font-family:var(--font-head); font-size:12px; text-transform:uppercase; letter-spacing:1px; color:var(--text-dim); padding:12px 16px; text-align:left; border-bottom:1px solid var(--glass-border); }
        .data-table td { padding:14px 16px; font-size:14px; border-bottom:1px solid rgba(255,255,255,0.04); }
        .data-table tr:hover td { background:var(--glass); }
        .gender-badge { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .gender-badge.Male { background:rgba(59,130,246,0.15); color:#3b82f6; }
        .gender-badge.Female { background:rgba(236,72,153,0.15); color:#ec4899; }
        .gender-badge.Other { background:rgba(245,158,11,0.15); color:#f59e0b; }
        .del-btn { background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); color:#f87171; padding:5px 12px; border-radius:6px; cursor:pointer; font-size:12px; font-family:var(--font-body); transition:0.2s; }
        .del-btn:hover { background:rgba(239,68,68,0.3); }
        .table-wrap { background:var(--glass); border:1px solid var(--glass-border); border-radius:20px; overflow:hidden; }
        .no-data { text-align:center; padding:60px; color:var(--text-dim); }
        .export-btn { background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.3); color:#10b981; padding:10px 20px; border-radius:10px; cursor:pointer; font-size:14px; font-family:var(--font-body); text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:0.2s; }
        .export-btn:hover { background:rgba(16,185,129,0.25); }
    </style>
</head>
<body>
<nav class="navbar">
    <div class="logo"><span class="logo-icon">⚙</span> Admin Dashboard</div>
    <div class="nav-user">
        <span class="user-badge">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <a href="index.php" class="btn-logout" style="text-decoration:none; padding:8px 16px;">← Portal</a>
    </div>
</nav>

<div class="admin-wrap">
    <div class="admin-header">
        <div>
            <h1>Participants <span class="gradient-text">Overview</span></h1>
            <p style="color:var(--text-dim); font-size:14px; margin-top:4px;">Manage all trip registrations</p>
        </div>
        <a href="export.php" class="export-btn">📥 Export CSV</a>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi blue">
            <div class="kpi-num"><?php echo $total; ?></div>
            <div class="kpi-label">Total Registered</div>
        </div>
        <div class="kpi cyan">
            <div class="kpi-num"><?php echo $male; ?></div>
            <div class="kpi-label">Male Participants</div>
        </div>
        <div class="kpi pink">
            <div class="kpi-num"><?php echo $female; ?></div>
            <div class="kpi-label">Female Participants</div>
        </div>
        <div class="kpi gold">
            <div class="kpi-num"><?php echo $today; ?></div>
            <div class="kpi-label">Registered Today</div>
        </div>
    </div>

    <!-- Search & Filter -->
    <form method="get" class="controls">
        <input type="text" name="search" placeholder="🔍 Search by name, email, phone…" value="<?php echo htmlspecialchars($search); ?>">
        <select name="gender">
            <option value="">All Genders</option>
            <option value="Male" <?php echo $gender_filter=='Male'?'selected':''; ?>>Male</option>
            <option value="Female" <?php echo $gender_filter=='Female'?'selected':''; ?>>Female</option>
            <option value="Other" <?php echo $gender_filter=='Other'?'selected':''; ?>>Other</option>
        </select>
        <select name="sort">
            <option value="date" <?php echo $sort=='date'?'selected':''; ?>>Sort: Latest</option>
            <option value="name" <?php echo $sort=='name'?'selected':''; ?>>Sort: Name</option>
            <option value="age" <?php echo $sort=='age'?'selected':''; ?>>Sort: Age</option>
        </select>
        <button type="submit" class="btn" style="width:auto; padding:10px 24px;">Filter</button>
        <?php if($search || $gender_filter): ?>
        <a href="admin.php" style="color:var(--text-dim); font-size:13px; text-decoration:none; align-self:center;">✕ Clear</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <div class="table-wrap">
        <?php if($result->num_rows > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Registered</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php $i = 1; while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td style="color:var(--text-dim);"><?php echo $i++; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                    <td><?php echo $row['age']; ?></td>
                    <td><span class="gender-badge <?php echo $row['gender']; ?>"><?php echo $row['gender']; ?></span></td>
                    <td style="color:var(--text-dim);"><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone_no']); ?></td>
                    <td style="color:var(--text-dim);"><?php echo date('d M Y, h:i A', strtotime($row['date'])); ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Delete this registration?')">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="del-btn">🗑 Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-data">No participants found matching your criteria.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>