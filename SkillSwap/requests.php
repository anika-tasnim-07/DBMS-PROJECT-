<?php
session_start();
include "config/database.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Handle Accept/Reject/Complete Actions
if (isset($_GET['action']) && isset($_GET['req_id'])) {
    $req_id = intval($_GET['req_id']);
    $action = $_GET['action'];

    if (in_array($action, ['Accepted', 'Rejected', 'Completed'])) {
        $stmt = $conn->prepare("UPDATE swap_requests SET status = ? WHERE request_id = ? AND (receiver_id = ? OR sender_id = ?)");
        $stmt->bind_param("siii", $action, $req_id, $student_id, $student_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: requests.php");
    exit();
}

// Fetch Incoming Requests
$incoming_sql = "
    SELECT r.*, st.name as sender_name, 
           s1.skill_name as offered_skill, 
           s2.skill_name as requested_skill
    FROM swap_requests r
    JOIN students st ON r.sender_id = st.student_id
    LEFT JOIN skills s1 ON r.offered_skill_id = s1.skill_id
    LEFT JOIN skills s2 ON r.requested_skill_id = s2.skill_id
    WHERE r.receiver_id = ?
    ORDER BY r.created_at DESC";
$inc_stmt = $conn->prepare($incoming_sql);
$inc_stmt->bind_param("i", $student_id);
$inc_stmt->execute();
$incoming = $inc_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swap Requests - SkillSwap</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 20px; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #007bff; color: white; }
        .btn { padding: 5px 10px; color: white; text-decoration: none; border-radius: 3px; font-size: 13px; }
        .btn-success { background: #28a745; }
        .btn-danger { background: #dc3545; }
        .btn-info { background: #17a2b8; }
    </style>
</head>
<body>
    <a href="dashboard.php" style="float: right;">Dashboard</a>
    <h2>Swap Requests Received</h2>

    <table>
        <thead>
            <tr>
                <th>From</th>
                <th>They Offer</th>
                <th>They Want</th>
                <th>Message</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($incoming->num_rows > 0): ?>
                <?php while ($row = $incoming->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['sender_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['offered_skill'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['requested_skill'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['message']); ?></td>
                        <td><strong><?php echo $row['status']; ?></strong></td>
                        <td>
                            <?php if ($row['status'] == 'Pending'): ?>
                                <a href="requests.php?action=Accepted&req_id=<?php echo $row['request_id']; ?>" class="btn btn-success">Accept</a>
                                <a href="requests.php?action=Rejected&req_id=<?php echo $row['request_id']; ?>" class="btn btn-danger">Reject</a>
                            <?php elseif ($row['status'] == 'Accepted'): ?>
                                <a href="requests.php?action=Completed&req_id=<?php echo $row['request_id']; ?>" class="btn btn-info">Mark Completed</a>
                            <?php elseif ($row['status'] == 'Completed'): ?>
                                <a href="rating.php?req_id=<?php echo $row['request_id']; ?>&target_id=<?php echo $row['sender_id']; ?>" class="btn btn-success">⭐ Rate User</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No swap requests received yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>