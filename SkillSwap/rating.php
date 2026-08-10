<?php
session_start();
include "config/database.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$rater_id         = $_SESSION['student_id'];
$request_id       = isset($_GET['req_id']) ? intval($_GET['req_id']) : 0;
$rated_student_id = isset($_GET['target_id']) ? intval($_GET['target_id']) : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id       = intval($_POST['request_id']);
    $rated_student_id = intval($_POST['rated_student_id']);
    $rating           = intval($_POST['rating']);
    $feedback         = trim($_POST['feedback']);

    $stmt = $conn->prepare("INSERT INTO ratings (request_id, rater_id, rated_student_id, rating, feedback) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiis", $request_id, $rater_id, $rated_student_id, $rating, $feedback);
    
    if ($stmt->execute()) {
        header("Location: requests.php");
        exit();
    } else {
        $error = "You have already submitted a rating for this exchange.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rate Skill Swap - SkillSwap</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 8px; width: 320px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .input-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        select, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Rate Experience</h2>
        <?php if (isset($error)): ?><p style="color:red;"><?php echo $error; ?></p><?php endif; ?>
        <form action="rating.php" method="POST">
            <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
            <input type="hidden" name="rated_student_id" value="<?php echo $rated_student_id; ?>">

            <div class="input-group">
                <label>Rating (1 to 5 Stars)</label>
                <select name="rating" required>
                    <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                    <option value="4">⭐⭐⭐⭐ (4/5)</option>
                    <option value="3">⭐⭐⭐ (3/5)</option>
                    <option value="2">⭐⭐ (2/5)</option>
                    <option value="1">⭐ (1/5)</option>
                </select>
            </div>
            <div class="input-group">
                <label>Feedback</label>
                <textarea name="feedback" rows="4" placeholder="How was your learning experience?"></textarea>
            </div>
            <button type="submit">Submit Review</button>
        </form>
    </div>
</body>
</html>