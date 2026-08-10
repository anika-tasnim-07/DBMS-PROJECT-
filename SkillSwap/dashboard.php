<?php
session_start();
include "config/database.php";

// Redirect if not logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch current user details
$user_stmt = $conn->prepare("SELECT name, email, department FROM students WHERE student_id = ?");
$user_stmt->bind_param("i", $student_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// Metrics aggregation
$skills_offered  = $conn->query("SELECT COUNT(*) AS total FROM skills WHERE student_id = $student_id AND skill_type = 'offered'")->fetch_assoc()['total'];
$skills_sought   = $conn->query("SELECT COUNT(*) AS total FROM skills WHERE student_id = $student_id AND skill_type = 'sought'")->fetch_assoc()['total'];
$pending_reqs    = $conn->query("SELECT COUNT(*) AS total FROM swap_requests WHERE receiver_id = $student_id AND status = 'Pending'")->fetch_assoc()['total'];
$completed_swaps = $conn->query("SELECT COUNT(*) AS total FROM swap_requests WHERE (sender_id = $student_id OR receiver_id = $student_id) AND status = 'Completed'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - SkillSwap</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="brand">SkillSwap</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="skills.php">Manage Skills</a></li>
            <li><a href="search.php">Search & Auto Match</a></li>
            <li><a href="requests.php">Swap Requests</a></li>
            <li><a href="profile.php">My Profile & Security</a></li>
            
            <!-- ADMIN LINK (Only visible if account is Admin) -->
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                <li>
                    <a href="admin/dashboard.php" style="background: #dc2626; color: white; font-weight: bold; margin-top: 15px;">
                        ⚙️ Admin Panel
                    </a>
                </li>
            <?php endif; ?>

            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="navbar">
            <div>
                <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
                <p style="color: var(--text-muted); font-size: 14px;">
                    Department: <?php echo htmlspecialchars($user['department'] ?? 'Not set'); ?>
                </p>
            </div>
            <div>
                <a href="profile.php" class="btn">View Profile</a>
            </div>
        </div>

        <!-- Dashboard Statistics -->
        <div class="grid">
            <div class="card">
                <h3>Skills Shared</h3>
                <div class="number"><?php echo $skills_offered; ?></div>
            </div>
            <div class="card">
                <h3>Skills Wanted</h3>
                <div class="number"><?php echo $skills_sought; ?></div>
            </div>
            <div class="card">
                <h3>Pending Requests</h3>
                <div class="number"><?php echo $pending_reqs; ?></div>
            </div>
            <div class="card">
                <h3>Completed Swaps</h3>
                <div class="number"><?php echo $completed_swaps; ?></div>
            </div>
        </div>

        <!-- Quick Navigation Cards -->
        <h3 style="margin-top: 35px; margin-bottom: 15px;">Quick Actions</h3>
        <div class="grid">
            <div class="card">
                <h4>Share / Request Skills</h4>
                <p style="color: var(--text-muted); margin: 10px 0;">Add new skills you can teach or skills you want to learn.</p>
                <a href="skills.php" class="btn">Go to Skills</a>
            </div>
            <div class="card">
                <h4>Find Swap Partners</h4>
                <p style="color: var(--text-muted); margin: 10px 0;">Find students who offer skills you want or seek skills you have.</p>
                <a href="search.php" class="btn">Find Matches</a>
            </div>
            <div class="card">
                <h4>Manage Requests</h4>
                <p style="color: var(--text-muted); margin: 10px 0;">Accept, reject, or check incoming skill swap proposals.</p>
                <a href="requests.php" class="btn">View Requests</a>
            </div>
        </div>
    </div>

</body>
</html>