<?php
session_start();
include "../config/database.php";

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $skill_name     = trim($_POST['skill_name']);
    $question_type  = $_POST['question_type'];
    $question_text  = trim($_POST['question_text']);
    $option_a       = trim($_POST['option_a']);
    $option_b       = trim($_POST['option_b']);
    $option_c       = trim($_POST['option_c']);
    $option_d       = trim($_POST['option_d']);
    $correct_opts   = isset($_POST['correct_options']) ? implode(",", $_POST['correct_options']) : '';

    if (!empty($skill_name) && !empty($question_text) && !empty($correct_opts)) {
        $stmt = $conn->prepare("INSERT INTO quiz_questions (skill_name, question_type, question_text, option_a, option_b, option_c, option_d, correct_options) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $skill_name, $question_type, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_opts);
        $stmt->execute();
        $message = "Question saved successfully!";
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Question - SkillSwap Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>body { display: block !important; padding: 30px; background: #f8fafc; }</style>
</head>
<body>
    <div style="max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px;">
        <h2>➕ Add Quiz Question</h2>
        <?php if ($message) echo "<p style='color:green;'>$message</p>"; ?>
        <form method="POST">
            <label>Skill Name (e.g. C++, Java, OOP, English Literature):</label>
            <input type="text" name="skill_name" required style="width:100%; margin-bottom:10px;">

            <label>Type:</label>
            <select name="question_type" style="width:100%; margin-bottom:10px;">
                <option value="single">Single Choice (Radio)</option>
                <option value="checkbox">Multiple Choice (Checkbox)</option>
            </select>

            <label>Question:</label>
            <textarea name="question_text" required style="width:100%; margin-bottom:10px;"></textarea>

            <label>Option A:</label><input type="text" name="option_a" required style="width:100%; margin-bottom:5px;">
            <label>Option B:</label><input type="text" name="option_b" required style="width:100%; margin-bottom:5px;">
            <label>Option C:</label><input type="text" name="option_c" required style="width:100%; margin-bottom:5px;">
            <label>Option D:</label><input type="text" name="option_d" required style="width:100%; margin-bottom:10px;">

            <label>Correct Answer(s):</label><br>
            <input type="checkbox" name="correct_options[]" value="A"> A
            <input type="checkbox" name="correct_options[]" value="B"> B
            <input type="checkbox" name="correct_options[]" value="C"> C
            <input type="checkbox" name="correct_options[]" value="D"> D<br><br>

            <button type="submit" class="btn">Save Question</button>
            <a href="dashboard.php" class="btn" style="background:#64748b;">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>