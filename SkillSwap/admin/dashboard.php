<?php
session_start();
include "../config/database.php";

// Restrict access to Admins only
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Handle Profile Deletion by Admin
if (isset($_GET['delete_student'])) {
    $del_id = intval($_GET['delete_student']);
    $conn->query("DELETE FROM students WHERE student_id = $del_id");
    header("Location: dashboard.php");
    exit();
}

// System Metrics
$total_students = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
$total_shared   = $conn->query("SELECT COUNT(*) AS total FROM skills WHERE skill_type='offered'")->fetch_assoc()['total'];
$total_swaps    = $conn->query("SELECT COUNT(*) AS total FROM swap_requests WHERE status='Accepted'")->fetch_assoc()['total'];

// Account Deletion Requests
$del_requests = $conn->query("SELECT dr.*, s.name, s.email FROM deletion_requests dr JOIN students s ON dr.student_id = s.student_id WHERE dr.status='Pending'");

// All Student Verification Test Results
$all_scores = $conn->query("
    SELECT r.*, s.name, s.email 
    FROM skill_test_results r 
    JOIN students s ON r.student_id = s.student_id 
    ORDER BY r.taken_at DESC
");

// All Registered Students
$students = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - SkillSwap</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Override display flex to ensure full-width vertical layout */
        body {
            display: block !important;
            padding: 30px;
            background-color: var(--bg-main, #f8fafc);
        }
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .section-title {
            margin-top: 35px;
            margin-bottom: 15px;
            font-size: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 6px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 600;
        }
        .badge-level {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            background: #e2e8f0;
            color: #334155;
        }
    </style>
</head>
<body>

    <div class="admin-container">
        <!-- Top Header -->
        <div class="admin-header">
            <h2>⚙️ Admin Monitoring Dashboard</h2>
            <a href="../dashboard.php" class="btn" style="background: #64748b;">Back to Student Dashboard</a>
        </div>

        <!-- Metric Cards Grid -->
        <div class="grid">
            <div class="card">
                <h3>Total Registered</h3>
                <div class="number"><?php echo $total_students; ?></div>
            </div>
            <div class="card">
                <h3>Skills Shared</h3>
                <div class="number"><?php echo $total_shared; ?></div>
            </div>
            <div class="card">
                <h3>Successful Swaps</h3>
                <div class="number"><?php echo $total_swaps; ?></div>
            </div>
        </div>

        <!-- Pending Deletion Requests Table -->
        <h3 class="section-title">Pending Account Deletion Requests</h3>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Reason</th>
                    <th>Requested At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($del_requests && $del_requests->num_rows > 0): ?>
                    <?php while($dr = $del_requests->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($dr['name']); ?></td>
                            <td><?php echo htmlspecialchars($dr['email']); ?></td>
                            <td><?php echo htmlspecialchars($dr['reason']); ?></td>
                            <td><?php echo $dr['requested_at']; ?></td>
                            <td>
                                <a href="dashboard.php?delete_student=<?php echo $dr['student_id']; ?>" class="btn btn-danger" onclick="return confirm('Approve request and permanently delete account?')">Approve & Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted, #64748b); padding: 20px;">No pending deletion requests.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Student Verification Test Scores Section -->
        <h3 class="section-title">Student Skill Verification Test Scores</h3>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Skill Tested</th>
                    <th>Score</th>
                    <th>Assigned Level</th>
                    <th>Date Taken</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($all_scores && $all_scores->num_rows > 0): ?>
                    <?php while($sc = $all_scores->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sc['name']); ?></td>
                            <td><?php echo htmlspecialchars($sc['email']); ?></td>
                            <td><?php echo htmlspecialchars($sc['skill_name']); ?></td>
                            <td><strong><?php echo $sc['score']; ?> / <?php echo $sc['total_questions']; ?></strong></td>
                            <td>
                                <span class="badge-level">
                                    <?php echo htmlspecialchars($sc['assigned_level']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y h:i A', strtotime($sc['taken_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted, #64748b); padding: 20px;">No student test results available yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- All Profiles Management Table -->
        <h3 class="section-title">Manage All Profiles</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($st = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $st['student_id']; ?></td>
                        <td><?php echo htmlspecialchars($st['name']); ?></td>
                        <td><?php echo htmlspecialchars($st['email']); ?></td>
                        <td><?php echo htmlspecialchars($st['department'] ?? 'N/A'); ?></td>
                        <td>
                            <a href="dashboard.php?delete_student=<?php echo $st['student_id']; ?>" class="btn btn-danger" onclick="return confirm('Directly delete this profile?')">Delete Profile</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>