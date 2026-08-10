<?php
session_start();
include "config/database.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// Fetch categories for foreign key selection
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id  = $_SESSION['student_id'];
    $category_id = intval($_POST['category_id']);
    $skill_name  = trim($_POST['skill_name']);
    $skill_level = $_POST['skill_level'];
    $skill_type  = $_POST['skill_type'];
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("INSERT INTO skills (student_id, category_id, skill_name, skill_level, skill_type, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $student_id, $category_id, $skill_name, $skill_level, $skill_type, $description);

    if ($stmt->execute()) {
        header("Location: skills.php");
        exit();
    } else {
        $message = "Failed to add skill.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Skill - SkillSwap</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 8px; width: 350px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .input-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Add a New Skill</h2>
        <?php if ($message): ?><p style="color:red;"><?php echo $message; ?></p><?php endif; ?>
        <form action="add-skill.php" method="POST">
            <div class="input-group">
                <label>Skill Name</label>
                <input type="text" name="skill_name" required placeholder="e.g. Python, Photoshop">
            </div>
            <div class="input-group">
                <label>Category</label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Skill Level</label>
                <select name="skill_level" required>
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Expert">Expert</option>
                </select>
            </div>
            <div class="input-group">
                <label>Type</label>
                <select name="skill_type" required>
                    <option value="offered">Skill I Offer (Can Teach)</option>
                    <option value="sought">Skill I Seek (Want to Learn)</option>
                </select>
            </div>
            <div class="input-group">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Brief details about what you know/want..."></textarea>
            </div>
            <button type="submit">Save Skill</button>
        </form>
    </div>
</body>
</html>