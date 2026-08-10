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

// Check if a request is already pending
$check_stmt = $conn->prepare("SELECT status FROM deletion_requests WHERE student_id = ? AND status = 'Pending'");
$check_stmt->bind_param("i", $student_id);
$check_stmt->execute();
$existing_request = $check_stmt->get_result()->fetch_assoc();
$check_stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reason = trim($_POST['reason']);

    if (empty($reason)) {
        $error = "Please provide a reason for account deletion.";
    } elseif ($existing_request) {
        $error = "You already have a pending deletion request.";
    } else {
        $stmt = $conn->prepare("INSERT INTO deletion_requests (student_id, reason) VALUES (?, ?)");
        $stmt->bind_param("is", $student_id, $reason);

        if ($stmt->execute()) {
            $message = "Your request has been submitted to the admin for review.";
            $existing_request = true; // Block duplicate submissions immediately
        } else {
            $error = "Failed to submit request. Try again later.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Account Deletion - SkillSwap</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--bg-main, #f8fafc);
        }
        .deletion-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            max-width: 500px;
            width: 100%;
        }
    </style>
</head>
<body>

    <div class="deletion-card">
        <h2>Request Account Deletion</h2>
        <p style="color: var(--text-muted); margin-bottom: 20px;">
            Submitting this form sends a request to the system administrator to permanently delete your account.
        </p>

        <?php if (!empty($message)): ?>
            <p style="color: green; font-weight: bold; margin-bottom: 15px;"><?php echo $message; ?></p>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <p style="color: red; font-weight: bold; margin-bottom: 15px;"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if ($existing_request): ?>
            <div style="background: #fef3c7; color: #92400e; padding: 15px; border-radius: 8px; font-weight: 500;">
                ⚠️ You have a pending deletion request. The administrator will process it soon.
            </div>
            <a href="dashboard.php" class="btn" style="display: block; text-align: center; margin-top: 20px; background: #64748b;">
                Back to Dashboard
            </a>
        <?php else: ?>
            <form action="request_deletion.php" method="POST">
                <div class="input-group" style="margin-bottom: 20px;">
                    <label for="reason">Reason for leaving:</label>
                    <textarea id="reason" name="reason" rows="4" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;" required placeholder="Tell us why you are deleting your account..."></textarea>
                </div>

                <button type="submit" class="btn btn-danger" style="width: 100%; background: #dc2626; color: white;">
                    Submit Request to Admin
                </button>
                <a href="dashboard.php" class="btn" style="display: block; text-align: center; margin-top: 10px; background: #64748b; color: white;">
                    Cancel
                </a>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>