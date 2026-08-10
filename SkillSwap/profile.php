<?php
session_start();
include "config/database.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$message = "";
$error = "";

// 1. Fetch Student Profile Details
$stmt = $conn->prepare("SELECT name, email, phone, department, semester, password FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. Count Offered (Shared) Skills vs Sought Skills
$offered_q = $conn->prepare("SELECT COUNT(*) AS total FROM skills WHERE student_id = ? AND skill_type = 'offered'");
$offered_q->bind_param("i", $student_id);
$offered_q->execute();
$offered_count = $offered_q->get_result()->fetch_assoc()['total'];

$sought_q = $conn->prepare("SELECT COUNT(*) AS total FROM skills WHERE student_id = ? AND skill_type = 'sought'");
$sought_q->bind_param("i", $student_id);
$sought_q->execute();
$sought_count = $sought_q->get_result()->fetch_assoc()['total'];

// 3. Handle Password Change Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect!";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match!";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        // Hash and update new password
        $hashed_new = password_hash($new_password, PASSWORD_DEFAULT);
        $up_stmt = $conn->prepare("UPDATE students SET password = ? WHERE student_id = ?");
        $up_stmt->bind_param("si", $hashed_new, $student_id);
        
        if ($up_stmt->execute()) {
            $message = "Password updated successfully!";
        } else {
            $error = "Failed to update password. Try again.";
        }
        $up_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - SkillSwap</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-container { max-width: 600px; margin: 20px auto; }
        .stats-box { display: flex; gap: 15px; margin: 20px 0; }
        .stat-card { flex: 1; background: #e0e7ff; padding: 15px; border-radius: 8px; text-align: center; }
        .stat-card h4 { margin: 0; color: #3730a3; }
        .stat-card .val { font-size: 24px; font-weight: bold; color: #4f46e5; margin-top: 5px; }
        .success { color: green; font-weight: bold; margin-bottom: 15px; }
        .error { color: red; font-weight: bold; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="brand">SkillSwap</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="skills.php">Manage Skills</a></li>
            <li><a href="search.php">Search & Match</a></li>
            <li><a href="requests.php">Swap Requests</a></li>
            <li><a href="profile.php" class="active">My Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
<div style="margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
    <h4 style="color: #dc2626;">Danger Zone</h4>
    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 10px;">
        Need to close your account? Send a request to the admin.
    </p>
    <a href="request_deletion.php" class="btn" style="background: #dc2626; color: white;">
        Request Account Deletion
    </a>
</div>
    <div class="main-content">
        <div class="profile-container">
            <h2>My Profile</h2>

            <?php if ($message): ?><p class="success"><?php echo $message; ?></p><?php endif; ?>
            <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

            <!-- Skills Overview -->
            <div class="stats-box">
                <div class="stat-card">
                    <h4>Skills Offered (Shared)</h4>
                    <div class="val"><?php echo $offered_count; ?></div>
                </div>
                <div class="stat-card">
                    <h4>Skills Sought (Wanting to Learn)</h4>
                    <div class="val"><?php echo $sought_count; ?></div>
                </div>
            </div>

            <!-- Profile Info Card -->
            <div class="card" style="margin-bottom: 25px;">
                <h3>Account Information</h3>
                <p style="margin-top: 10px;"><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></p>
                <p><strong>Department:</strong> <?php echo htmlspecialchars($user['department'] ?? 'N/A'); ?></p>
                <p><strong>Semester:</strong> <?php echo htmlspecialchars($user['semester'] ?? 'N/A'); ?></p>
            </div>

            <!-- Password Change Form -->
            <div class="card">
                <h3>Change Password</h3>
                <form action="profile.php" method="POST" style="margin-top: 15px;">
                    <input type="hidden" name="change_password" value="1">
                    <div class="input-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="input-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="input-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>