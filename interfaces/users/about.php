<?php
session_start();

// 1. Security Check
if (!isset($_SESSION["user"])) {
    header("Location: ../../login.php");
    exit;
}
$user = $_SESSION["user"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>About Us - CampusFind</title>
    <link rel="icon" type="image/x-icon" href="../../assets/search.png">    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CSS FILES -->
    <link rel="stylesheet" href="../../reusable/header.css">
    <link rel="stylesheet" href="../../reusable/about_us.css">
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="m-0 border-0">

    <!-- NAVIGATION -->
    <nav class="navbar p-3 sticky-top">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions"
                    aria-expanded="false" aria-label="Toggle navigation">
                <img src="/assets/hamburger.png" alt="hamburger icon" width="20px" height="20px">
            </button>
            <div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1"
                 id="offcanvasWithBothOptions" aria-labelledby="offcanvasWithBothOptionsLabel">
                <div class="offcanvas-body">
                    <!-- 1. DASHBOARD -->
                    <a href="dashboard_user.php">Dashboard</a>
                    
                    <!-- 2. MY POSTS -->
                    <a href="myposts_user.php">My Posts</a>
                    
                    <!-- 3. ABOUT (Active/Highlighted) -->
                    <a href="about.php" style="color: #2289e6; font-weight: 700;">About</a>
                    
                    <!-- 4. LOG OUT -->
                    <a class="logout" href="../../logout.php" onclick="return confirm('Are you sure you want to LOG OUT?');">Log out</a>
                </div>
            </div>
            <strong><a class="navbar-brand me-auto" href="dashboard_user.php">Campus<span class="find">Find</span></a></strong>
            <a class="navbar-brand ms-auto text-white" href="#">Hello, <?php echo htmlspecialchars($user["username"]); ?></a>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="container">
        
        <!-- HERO BANNER -->
        <div class="hero-section">
            <h1>Our Mission</h1>
            <p>To reconnect lost items with their owners through a simple, secure, and community-driven platform.</p>
        </div>

        <!-- FEATURES -->
        <div class="features-container">
            <div class="feature-box">
                <div class="feature-icon">📢</div>
                <h4>Post Items</h4>
                <p class="text-secondary">Easily report lost or found items with photos and location details.</p>
            </div>
            <div class="feature-box">
                <div class="feature-icon">🔍</div>
                <h4>Smart Search</h4>
                <p class="text-secondary">Filter by category, date, or status to find what you are looking for.</p>
            </div>
            <div class="feature-box">
                <div class="feature-icon">🤝</div>
                <h4>Secure Claim</h4>
                <p class="text-secondary">A verified process to ensure items return to their rightful owners.</p>
            </div>
        </div>

        <!-- TEAM SECTION -->
        <div class="team-section">
            <h2 class="mb-2">Meet the Team</h2>
            <p class="text-secondary mb-4">The developers behind the project</p>
            
            <div class="team-grid">
                <div class="team-member">
                    <div class="avatar-circle">M</div>
                    <div class="member-name">Mel</div>
                    <div class="member-role">Developer</div>
                </div>
                <div class="team-member">
                    <div class="avatar-circle">J</div>
                    <div class="member-name">Jay R Santos</div>
                    <div class="member-role">Backend / DB</div>
                </div>
                <div class="team-member">
                    <div class="avatar-circle">J</div>
                    <div class="member-name">Jess Carbonel</div>
                    <div class="member-role">Logic / Backend</div>
                </div>
                <div class="team-member">
                    <div class="avatar-circle">L</div>
                    <div class="member-name">Leader Name</div>
                    <div class="member-role">Project Manager</div>
                </div>
                <div class="team-member">
                    <div class="avatar-circle">M</div>
                    <div class="member-name">Member 5</div>
                    <div class="member-role">Designer / QA</div>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="about-footer">
            &copy; 2025 CampusFind. WAD 1 & RWD Final Project.
        </div>

    </div>

</body>
</html>