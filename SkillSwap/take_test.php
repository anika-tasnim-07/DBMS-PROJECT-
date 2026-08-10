<?php
session_start();
include "config/database.php";

if (!isset($_SESSION['student_id']) || !isset($_GET['skill_id'])) {
    header("Location: skills.php");
    exit();
}

$skill_id   = intval($_GET['skill_id']);
$student_id = $_SESSION['student_id'];
$message    = "";

// Fetch skill details
$skill_stmt = $conn->prepare("SELECT skill_name FROM skills WHERE skill_id = ? AND student_id = ?");
$skill_stmt->bind_param("ii", $skill_id, $student_id);
$skill_stmt->execute();
$skill_info = $skill_stmt->get_result()->fetch_assoc();
$skill_stmt->close();

if (!$skill_info) {
    die("Skill not found or access denied.");
}

$skill_name = trim($skill_info['skill_name']);

// Pull questions matching this skill (Case-insensitive search)
$q_stmt = $conn->prepare("SELECT * FROM quiz_questions WHERE LOWER(TRIM(skill_name)) = LOWER(?) LIMIT 30");
$q_stmt->bind_param("s", $skill_name);
$q_stmt->execute();
$questions = $q_stmt->get_result();

// If no specific skill questions found, load general/fallback questions so test isn't empty
if ($questions->num_rows === 0) {
    $questions = $conn->query("SELECT * FROM quiz_questions LIMIT 30");
}

// Evaluate test submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_test'])) {
    $score = 0;
    
    if (isset($_POST['answers']) && is_array($_POST['answers'])) {
        foreach ($_POST['answers'] as $q_id => $user_ans) {
            $eval_stmt = $conn->prepare("SELECT correct_options FROM quiz_questions WHERE question_id = ?");
            $eval_stmt->bind_param("i", $q_id);
            $eval_stmt->execute();
            $res = $eval_stmt->get_result()->fetch_assoc();
            $eval_stmt->close();

            if ($res) {
                $correct_db = $res['correct_options'];
                $user_string = is_array($user_ans) ? implode(",", $user_ans) : $user_ans;

                if ($user_string === $correct_db) {
                    $score++;
                }
            }
        }
    }

    // Determine proficiency level
    if ($score >= 25) {
        $level = 'Expert';
    } elseif ($score >= 15) {
        $level = 'Intermediate';
    } else {
        $level = 'Beginner';
    }

    // 1. Update skill level in skills table
    $up_stmt = $conn->prepare("UPDATE skills SET skill_level = ? WHERE skill_id = ? AND student_id = ?");
    $up_stmt->bind_param("sii", $level, $skill_id, $student_id);
    $up_stmt->execute();
    $up_stmt->close();

    // 2. Log score in test results table
    $log_stmt = $conn->prepare("INSERT INTO skill_test_results (student_id, skill_id, skill_name, score, total_questions, assigned_level) VALUES (?, ?, ?, ?, 30, ?)");
    $log_stmt->bind_param("iisis", $student_id, $skill_id, $skill_name, $score, $level);
    $log_stmt->execute();
    $log_stmt->close();

    $message = "Test Submitted Successfully! Score: $score / 30. Your verified tier is now: <strong>$level</strong>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take Verification Test - SkillSwap</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn { padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Skill Verification Test: <?php echo htmlspecialchars($skill_name); ?></h2>
        <p style="color: #6c757d; margin-bottom: 20px;">Thresholds: 25–30 = Expert | 15–24 = Intermediate | Below 15 = Beginner</p>

        <?php if ($message): ?>
            <div class="card">
                <h3>Result Summary</h3>
                <p style="margin: 15px 0; font-size: 18px;"><?php echo $message; ?></p>
                <a href="skills.php" class="btn">Return to Manage Skills</a>
            </div>
        <?php elseif ($questions->num_rows == 0): ?>
            <div class="card">
                <p style="color: #dc3545; font-weight: bold;">No questions available in the question bank for this skill yet.</p>
                <a href="skills.php" class="btn" style="background: #6c757d;">Back to Skills</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <?php $i = 1; while($q = $questions->fetch_assoc()): ?>
                    <div class="card">
                        <p><strong>Q<?php echo $i++; ?>: <?php echo htmlspecialchars($q['question_text']); ?></strong></p>
                        
                        <?php if ($q['question_type'] == 'checkbox'): ?>
                            <label style="display:block; margin: 5px 0;"><input type="checkbox" name="answers[<?php echo $q['question_id']; ?>][]" value="A"> <?php echo htmlspecialchars($q['option_a']); ?></label>
                            <label style="display:block; margin: 5px 0;"><input type="checkbox" name="answers[<?php echo $q['question_id']; ?>][]" value="B"> <?php echo htmlspecialchars($q['option_b']); ?></label>
                            <label style="display:block; margin: 5px 0;"><input type="checkbox" name="answers[<?php echo $q['question_id']; ?>][]" value="C"> <?php echo htmlspecialchars($q['option_c']); ?></label>
                            <label style="display:block; margin: 5px 0;"><input type="checkbox" name="answers[<?php echo $q['question_id']; ?>][]" value="D"> <?php echo htmlspecialchars($q['option_d']); ?></label>
                        <?php else: ?>
                            <label style="display:block; margin: 5px 0;"><input type="radio" name="answers[<?php echo $q['question_id']; ?>]" value="A" required> <?php echo htmlspecialchars($q['option_a']); ?></label>
                            <label style="display:block; margin: 5px 0;"><input type="radio" name="answers[<?php echo $q['question_id']; ?>]" value="B"> <?php echo htmlspecialchars($q['option_b']); ?></label>
                            <label style="display:block; margin: 5px 0;"><input type="radio" name="answers[<?php echo $q['question_id']; ?>]" value="C"> <?php echo htmlspecialchars($q['option_c']); ?></label>
                            <label style="display:block; margin: 5px 0;"><input type="radio" name="answers[<?php echo $q['question_id']; ?>]" value="D"> <?php echo htmlspecialchars($q['option_d']); ?></label>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
                
                <button type="submit" name="submit_test" class="btn" style="width:100%; font-size: 16px;">Submit Answers</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>