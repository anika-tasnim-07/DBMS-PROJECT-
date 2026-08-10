<?php
session_start();
include "config/database.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$message = "";

// Handle Skill Deletion (D in CRUD)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $del_stmt = $conn->prepare("DELETE FROM skills WHERE skill_id = ? AND student_id = ?");
    $del_stmt->bind_param("ii", $delete_id, $student_id);
    if ($del_stmt->execute()) {
        $message = "Skill deleted successfully.";
    }
    $del_stmt->close();
}

// Fetch Skills (R in CRUD) with Category JOIN
$query = "SELECT s.*, c.category_name 
          FROM skills s 
          JOIN categories c ON s.category_id = c.category_id 
          WHERE s.student_id = ? 
          ORDER BY s.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$skills = $stmt->get_result();

// Fetch Test Results History for logged-in student
$history_query = "SELECT * FROM skill_test_results WHERE student_id = ? ORDER BY taken_at DESC";
$hist_stmt = $conn->prepare($history_query);
$hist_stmt->bind_param("i", $student_id);
$hist_stmt->execute();
$test_history = $hist_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Skills - SkillSwap</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; }
        .btn { padding: 8px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block; }
        .btn-danger { background: #dc3545; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-success { background: #28a745; color: white; }
        .grid { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; width: 280px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .badge { background: #e9ecef; padding: 3px 8px; border-radius: 4px; font-size: 12px; }
        .msg { color: green; font-weight: bold; }
        
        /* Styles for the Test History Table */
        .history-section { margin-top: 40px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="header">
        <h2>My Skills</h2>
        <div>
            <a href="add-skill.php" class="btn">+ Add New Skill</a>
            <a href="dashboard.php" class="btn" style="background: #6c757d;">Dashboard</a>
        </div>
    </div>

    <?php if ($message): ?><p class="msg"><?php echo $message; ?></p><?php endif; ?>

    <div class="grid">
        <?php if ($skills->num_rows > 0): ?>
            <?php while ($row = $skills->fetch_assoc()): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($row['skill_name']); ?></h3>
                    <p><span class="badge"><?php echo htmlspecialchars($row['category_name']); ?></span></p>
                    <p><strong>Level:</strong> <?php echo htmlspecialchars($row['skill_level'] ?? $row['proficiency_level'] ?? 'Beginner'); ?></p>
                    <p><strong>Type:</strong> <?php echo ucfirst(htmlspecialchars($row['skill_type'])); ?></p>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    
                    <div style="margin-top: 15px; display: flex; flex-direction: column; gap: 8px;">
                        <!-- Verification Test Button -->
                        <a href="take_test.php?skill_id=<?php echo $row['skill_id']; ?>" class="btn btn-success" style="text-align: center;">
                            📝 Take Verification Test
                        </a>
                        
                        <div style="display: flex; gap: 10px;">
                            <a href="edit-skill.php?id=<?php echo $row['skill_id']; ?>" class="btn btn-warning" style="flex: 1; text-align: center;">Edit</a>
                            <a href="skills.php?delete_id=<?php echo $row['skill_id']; ?>" class="btn btn-danger" style="flex: 1; text-align: center;" onclick="return confirm('Delete this skill?')">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No skills added yet.</p>
        <?php endif; ?>
    </div>

    <!-- Verification Test Score History Section -->
    <div class="history-section">
        <h3>My Skill Verification Test Scores</h3>
        <table>
            <thead>
                <tr>
                    <th>Skill Name</th>
                    <th>Score</th>
                    <th>Assigned Level</th>
                    <th>Date Taken</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($test_history && $test_history->num_rows > 0): ?>
                    <?php while ($test = $test_history->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($test['skill_name']); ?></td>
                            <td><strong><?php echo $test['score']; ?> / <?php echo $test['total_questions']; ?></strong></td>
                            <td>
                                <span class="badge" style="background: #d4edda; color: #155724; font-weight: bold;">
                                    <?php echo htmlspecialchars($test['assigned_level']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y h:i A', strtotime($test['taken_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #6c757d;">No verification tests taken yet. Click "Take Verification Test" on any skill card above to start!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>