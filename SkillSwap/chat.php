<?php
session_start();
include "config/database.php";

if (!isset($_SESSION['student_id']) || !isset($_GET['swap_id'])) {
    header("Location: requests.php");
    exit();
}

$swap_id    = intval($_GET['swap_id']);
$student_id = $_SESSION['student_id'];

// Verify access: current user MUST be sender or receiver of an Accepted swap request
$auth_stmt = $conn->prepare("SELECT * FROM swap_requests WHERE request_id = ? AND (sender_id = ? OR receiver_id = ?) AND status = 'Accepted'");
$auth_stmt->bind_param("iii", $swap_id, $student_id, $student_id);
$auth_stmt->execute();
$swap = $auth_stmt->get_result()->fetch_assoc();

if (!$swap) {
    die("<div style='padding:30px; font-family:sans-serif;'><h2>Access Denied</h2><p>You can only chat with students who have an accepted skill swap with you.</p></div>");
}

$receiver_id = ($swap['sender_id'] == $student_id) ? $swap['receiver_id'] : $swap['sender_id'];

// Handle Sending Messages
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['message'])) {
    $msg = trim($_POST['message']);
    $ins = $conn->prepare("INSERT INTO chat_messages (swap_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
    $ins->bind_param("iiis", $swap_id, $student_id, $receiver_id, $msg);
    $ins->execute();
}

// Fetch Messages History
$chats = $conn->query("SELECT c.*, s.name FROM chat_messages c JOIN students s ON c.sender_id = s.student_id WHERE c.swap_id = $swap_id ORDER BY c.sent_at ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Private Chat Workspace - SkillSwap</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="main-content" style="max-width:650px; margin:20px auto;">
        <h2>Private Skill Swap Chat</h2>
        <p style="color:var(--text-muted); margin-bottom:15px;">Only you and your matched swap partner can view this conversation.</p>

        <div class="card" style="height:380px; overflow-y:auto; margin-bottom:15px; background:#ffffff; border:1px solid #cbd5e1; padding:15px;">
            <?php if ($chats && $chats->num_rows > 0): ?>
                <?php while($m = $chats->fetch_assoc()): ?>
                    <div style="margin-bottom:12px; text-align: <?php echo ($m['sender_id'] == $student_id) ? 'right' : 'left'; ?>;">
                        <small style="color:#64748b; font-size:11px;">
                            <strong><?php echo htmlspecialchars($m['name']); ?></strong> • <?php echo $m['sent_at']; ?>
                        </small><br>
                        <div style="display:inline-block; padding:8px 14px; border-radius:12px; margin-top:3px; max-width:75%; background:<?php echo ($m['sender_id'] == $student_id) ? '#4f46e5; color:white;' : '#e2e8f0; color:#1e293b;'; ?>">
                            <?php echo htmlspecialchars($m['message']); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center; color:#94a3b8; margin-top:150px;">No messages yet. Say hello to start swapping skills!</p>
            <?php endif; ?>
        </div>

        <form method="POST">
            <div style="display:flex; gap:10px;">
                <input type="text" name="message" placeholder="Type a message..." required autocomplete="off" style="flex:1; padding:12px; border:1px solid #cbd5e1; border-radius:6px;">
                <button type="submit" class="btn">Send</button>
            </div>
        </form>
    </div>
</body>
</html>