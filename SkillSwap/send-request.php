<?php
session_start();
include "config/database.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$sender_id = $_SESSION['student_id'];
$receiver_id = isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : 0;
$requested_skill_id = isset($_GET['requested_id']) ? intval($_GET['requested_id']) : null;
$offered_skill_id   = isset($_GET['offered_id']) ? intval($_GET['offered_id']) : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $receiver_id        = intval($_POST['receiver_id']);
    $requested_skill_id = intval($_POST['requested_skill_id']);
    $offered_skill_id   = !empty($_POST['offered_skill_id']) ? intval($_POST['offered_skill_id']) : null;
    $message            = trim($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO swap_requests (sender_id, receiver_id, offered_skill_id, requested_skill_id, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiis", $sender_id, $receiver_id, $offered_skill_id, $requested_skill_id, $message);
    $stmt->execute();
    $stmt->close();

    header("Location: requests.php");
    exit();
}

// Fetch sender's offered skills to select from
$my_skills = $conn->query("SELECT * FROM skills WHERE student_id = $sender_id AND skill_type = 'offered'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Swap Request - SkillSwap</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 8px; width: 350px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .input-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        select, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Send Swap Request</h2>
        <form action="send-request.php" method="POST">
            <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
            <input type="hidden" name="requested_skill_id" value="<?php echo $requested_skill_id; ?>">

            <div class="input-group">
                <label>Offer a Skill in Return</label>
                <select name="offered_skill_id">
                    <option value="">-- Choose One of Your Skills --</option>
                    <?php while ($sk = $my_skills->fetch_assoc()): ?>
                        <option value="<?php echo $sk['skill_id']; ?>" <?php if ($sk['skill_id'] == $offered_skill_id) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($sk['skill_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Message</label>
                <textarea name="message" rows="4" placeholder="Hi, I would like to exchange skills with you!"></textarea>
            </div>
            <button type="submit">Send Swap Request</button>
        </form>
    </div>
</body>
</html>