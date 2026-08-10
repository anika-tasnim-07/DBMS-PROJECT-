<?php
session_start();
include "config/database.php";

if (!isset($_SESSION['student_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$current_user = $_SESSION['student_id'];
$profile_id   = intval($_GET['id']);
$message      = "";

// Handle Review Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    $rating  = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    if ($profile_id == $current_user) {
        $message = "You cannot leave a review on your own profile!";
    } elseif ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO reviews (reviewer_id, reviewee_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $current_user, $profile_id, $rating, $comment);
        $stmt->execute();
        $message = "Feedback submitted successfully!";
    }
}

// Fetch Profile Info
$user_stmt = $conn->prepare("SELECT name, email, department FROM students WHERE student_id = ?");
$user_stmt->bind_param("i", $profile_id);
$user_stmt->execute();
$target_user = $user_stmt->get_result()->fetch_assoc();

// Calculate Average Rating
$avg_res = $conn->query("SELECT AVG(rating) as avg_rating FROM reviews WHERE reviewee_id = $profile_id")->fetch_assoc();
$avg_rating = number_format($avg_res['avg_rating'] ?? 0, 1);

// Fetch Reviews List
$reviews = $conn->query("SELECT r.*, s.name FROM reviews r JOIN students s ON r.reviewer_id = s.student_id WHERE r.reviewee_id = $profile_id ORDER BY r.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($target_user['name']); ?> - Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="main-content" style="max-width:800px; margin:20px auto;">
        <h2><?php echo htmlspecialchars($target_user['name']); ?>'s Profile</h2>
        <p><strong>Department:</strong> <?php echo htmlspecialchars($target_user['department'] ?? 'N/A'); ?></p>
        <p style="font-size:18px; margin-top:10px;"><strong>Average Rating:</strong> ⭐ <?php echo $avg_rating; ?> / 5</p>

        <hr style="margin:20px 0;">

        <h3>Leave Feedback</h3>
        <?php if($message): ?>
            <p style="color:green; font-weight:bold; margin-bottom:10px;"><?php echo $message; ?></p>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="submit_review" value="1">
            <div class="input-group">
                <label>Rating</label>
                <select name="rating" required>
                    <option value="5">5 - Outstanding</option>
                    <option value="4">4 - Very Helpful</option>
                    <option value="3">3 - Good</option>
                    <option value="2">2 - Satisfactory</option>
                    <option value="1">1 - Needs Improvement</option>
                </select>
            </div>
            <div class="input-group">
                <label>Comment / Feedback</label>
                <textarea name="comment" rows="3" required placeholder="Describe how helpful their skills were..."></textarea>
            </div>
            <button type="submit" class="btn">Submit Review</button>
        </form>

        <h3 style="margin-top:30px;">Ratings & Reviews</h3>
        <?php if ($reviews && $reviews->num_rows > 0): ?>
            <?php while($r = $reviews->fetch_assoc()): ?>
                <div class="card" style="margin-top:10px;">
                    <strong><?php echo htmlspecialchars($r['name']); ?></strong> — ⭐ <?php echo $r['rating']; ?>/5
                    <p style="color:var(--text-muted); font-size:12px;"><?php echo $r['created_at']; ?></p>
                    <p style="margin-top:5px;"><?php echo htmlspecialchars($r['comment']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color:var(--text-muted); margin-top:10px;">No reviews yet for this student.</p>
        <?php endif; ?>
    </div>
</body>
</html>