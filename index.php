<?php
session_start();
if(!isset($_SESSION['loggedin'])){
    header("Location: login.php");
    exit;
}

$con = mysqli_connect("localhost", "root", "", "trip");
$insert = false;
$error = "";

if(isset($_POST['name'])){
    // Server-side validation
    $name = trim($_POST['name']);
    $age = intval($_POST['age']);
    $gender = $_POST['gender'];
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $phone = $_POST['phone'];
    $desc = trim($_POST['desc']);

    if(!$email){
        $error = "Invalid email address.";
    } elseif(!preg_match('/^[0-9]{10}$/', $phone)){
        $error = "Phone must be 10 digits.";
    } else {
        // Check duplicate email
        $chk = $con->prepare("SELECT id FROM participants WHERE email = ?");
        $chk->bind_param("s", $email);
        $chk->execute();
        if($chk->get_result()->num_rows > 0){
            $error = "This email is already registered!";
        } else {
            $stmt = $con->prepare("INSERT INTO participants (name, age, gender, email, phone_no, other, date) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP())");
            $stmt->bind_param("sissss", $name, $age, $gender, $email, $phone, $desc);
            if($stmt->execute()){
                $insert = true;
            }
            $stmt->close();
        }
        $chk->close();
    }
}

// Fetch stats
$total_result = $con->query("SELECT COUNT(*) as total FROM participants");
$total = $total_result ? $total_result->fetch_assoc()['total'] : 0;
$seats_total = 60;
$seats_left = max(0, $seats_total - $total);

// Fetch recent registrations (for logged-in user view)
$recent = $con->query("SELECT name, gender, date FROM participants ORDER BY date DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doon University Trip Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="logo">
        <span class="logo-icon">✈</span> Doon University Trip Planner
    </div>
    <div class="nav-links">
        <a href="#register" class="nav-link">Register</a>
        <a href="#itinerary" class="nav-link">Itinerary</a>
        <a href="#participants" class="nav-link">Participants</a>
        <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
        <a href="admin.php" class="nav-link admin-link">⚙ Admin</a>
        <?php endif; ?>
    </div>
    <div class="nav-user">
        <span class="user-badge">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <form action="login.php" method="post" style="display:inline;">
            <button name="logout" class="btn-logout">Logout</button>
        </form>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-overlay"></div>
    <img src="IIT_Kharagpur.webp" alt="Doon University" class="hero-bg">
    <div class="hero-content">
        <span class="hero-badge">🎓 Doon University Official</span>
        <h1 class="hero-title">Annual Trip <span class="gradient-text">2025</span></h1>
        <p class="hero-subtitle">Join fellow Doon University students for an unforgettable adventure. Register now before seats run out!</p>
        <div class="hero-stats">
            <div class="stat-pill">
                <span class="stat-num"><?php echo $total; ?></span>
                <span class="stat-label">Registered</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-pill <?php echo $seats_left < 10 ? 'urgent' : ''; ?>">
                <span class="stat-num"><?php echo $seats_left; ?></span>
                <span class="stat-label">Seats Left</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-pill">
                <span class="stat-num"><?php echo $seats_total; ?></span>
                <span class="stat-label">Total Seats</span>
            </div>
        </div>
        <!-- Progress bar -->
        <div class="seats-bar-wrap">
            <div class="seats-bar">
                <div class="seats-filled" style="width: <?php echo ($total/$seats_total)*100; ?>%"></div>
            </div>
            <span class="seats-pct"><?php echo round(($total/$seats_total)*100); ?>% filled</span>
        </div>
        <a href="#register" class="btn-hero">Register Now →</a>
    </div>
</section>

<!-- Itinerary Section -->
<section class="section" id="itinerary">
    <div class="section-container">
        <h2 class="section-title">Trip <span class="gradient-text">Itinerary</span></h2>
        <p class="section-sub">A 3-day adventure packed with exploration and fun</p>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-dot">1</div>
                <div class="timeline-card">
                    <span class="tl-date">Day 1 — Dec 20</span>
                    <h3>Departure & Arrival</h3>
                    <p>Board the train from Dehradun station at 6:00 AM. Reach destination by evening. Check-in to hotel and group dinner.</p>
                    <div class="tl-tags">
                        <span class="tag">🚂 Train</span>
                        <span class="tag">🏨 Hotel</span>
                        <span class="tag">🍽 Dinner</span>
                    </div>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-dot">2</div>
                <div class="timeline-card">
                    <span class="tl-date">Day 2 — Dec 21</span>
                    <h3>Sightseeing & Adventure</h3>
                    <p>Full day tour of major attractions. Adventure activities in the afternoon. Cultural program in the evening.</p>
                    <div class="tl-tags">
                        <span class="tag">🗺 Sightseeing</span>
                        <span class="tag">⛰ Adventure</span>
                        <span class="tag">🎭 Culture</span>
                    </div>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-dot">3</div>
                <div class="timeline-card">
                    <span class="tl-date">Day 3 — Dec 22</span>
                    <h3>Leisure & Return</h3>
                    <p>Free morning for local shopping and exploration. Depart by afternoon train. Return to Dehradun by late night.</p>
                    <div class="tl-tags">
                        <span class="tag">🛍 Shopping</span>
                        <span class="tag">🚂 Return</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Registration Form -->
<section class="section" id="register">
    <div class="section-container">
        <h2 class="section-title">Trip <span class="gradient-text">Registration</span></h2>
        <p class="section-sub">Fill in your details to secure your spot</p>

        <?php if($insert): ?>
        <div class="alert-success">
            <span class="alert-icon">🎉</span>
            <div>
                <strong>Registration Confirmed!</strong>
                <p>You're all set! Check your email for confirmation details.</p>
            </div>
        </div>
        <?php endif; ?>

        <?php if($error): ?>
        <div class="alert-error">
            <span class="alert-icon">⚠️</span>
            <div><strong>Error:</strong> <?php echo htmlspecialchars($error); ?></div>
        </div>
        <?php endif; ?>

        <?php if($seats_left == 0): ?>
        <div class="alert-error"><span class="alert-icon">🚫</span> <div><strong>Registrations Closed.</strong> All seats are filled.</div></div>
        <?php else: ?>
        <div class="form-card">
            <form action="index.php#register" method="post" id="tripForm" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" placeholder="e.g. Rajesh Kumar" required minlength="3">
                        <span class="field-hint">As per institute ID</span>
                    </div>
                    <div class="form-group">
                        <label>Age *</label>
                        <input type="number" name="age" placeholder="18" min="15" max="60" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender" required>
                            <option value="" disabled selected>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="tel" name="phone" placeholder="10-digit mobile" pattern="[0-9]{10}" required>
                    </div>
                </div>
                <div class="form-group full">
                    <label>Email Address *</label>
                    <input type="email" name="email" placeholder="yourname@doonuniversity.ac.in" required>
                </div>
                <div class="form-group full">
                    <label>Preferences / Medical History</label>
                    <textarea name="desc" id="desc" rows="3" maxlength="200" placeholder="Any dietary preferences, allergies, or medical conditions we should know about?"></textarea>
                    <div class="char-count"><span id="current-char">0</span>/200</div>
                </div>
                <div class="form-group full checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="agree" required>
                        <span>I agree to the trip terms & conditions and confirm the above details are accurate.</span>
                    </label>
                </div>
                <button type="submit" class="btn" id="submitBtn">
                    <span class="btn-text">Submit Application</span>
                    <span class="btn-icon">→</span>
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Recent Participants -->
<section class="section" id="participants">
    <div class="section-container">
        <h2 class="section-title">Recent <span class="gradient-text">Registrations</span></h2>
        <p class="section-sub">See who's already joined the adventure</p>
        <div class="participants-grid">
            <?php if($recent && $recent->num_rows > 0): ?>
                <?php while($row = $recent->fetch_assoc()): ?>
                <div class="participant-card">
                    <div class="p-avatar"><?php echo strtoupper(substr($row['name'], 0, 1)); ?></div>
                    <div class="p-info">
                        <strong><?php echo htmlspecialchars(substr($row['name'], 0, 15)) . (strlen($row['name']) > 15 ? '…' : ''); ?></strong>
                        <span><?php echo htmlspecialchars($row['gender']); ?></span>
                    </div>
                    <div class="p-date"><?php echo date('d M', strtotime($row['date'])); ?></div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: rgba(255,255,255,0.5); text-align:center;">No registrations yet. Be the first!</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-logo">✈ Doon University Trip Planner</div>
        <p>Organized by Students' Activity Centre, Doon University</p>
        <p style="margin-top:5px; font-size:12px; opacity:0.4;">© 2025 Doon University. All rights reserved.</p>
    </div>
</footer>

<script>
// Character counter
const textarea = document.getElementById('desc');
const currentCount = document.getElementById('current-char');
if(textarea) {
    textarea.addEventListener('input', () => {
        currentCount.textContent = textarea.value.length;
        currentCount.style.color = textarea.value.length > 180 ? '#ff7675' : '';
    });
}

// Smooth scroll for nav links
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        document.querySelector(a.getAttribute('href'))?.scrollIntoView({ behavior: 'smooth' });
    });
});

// Form submit loading state
const form = document.getElementById('tripForm');
const btn = document.getElementById('submitBtn');
if(form && btn) {
    form.addEventListener('submit', () => {
        btn.disabled = true;
        btn.querySelector('.btn-text').textContent = 'Submitting…';
    });
}

// Animate stats on scroll
const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if(e.isIntersecting) e.target.classList.add('visible');
    });
}, { threshold: 0.1 });
document.querySelectorAll('.timeline-item, .participant-card, .form-card').forEach(el => observer.observe(el));
</script>
</body>
</html>