<?php
session_start();
include "config/database.php";

if (!isset($_SESSION['student_id']) || !isset($_GET['id'])) {
    header("Location: skills.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$skill_id   = intval($_GET['id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_id = intval($_POST['category_id']);
    $skill_name  = trim($_POST['skill_name']);
    $skill_level = $_POST['skill_level'];
    $skill_type  = $_POST['skill_type'];
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("UPDATE skills SET category_id=?, skill_name=?, skill_level=?, skill_type=?, description=? WHERE skill_id=? AND student_id=?");
    $stmt->bind_param("issssii", $category_id, $skill_name, $skill_level, $skill_type, $description, $skill_id, $student_id);
    $stmt->execute();
    $stmt->close();

    header("Location: skills.php");
    exit();
}

// Fetch current skill data
$stmt = $conn->prepare("SELECT * FROM skills WHERE skill_id = ? AND student_id = ?");
$stmt->bind_param("ii", $skill_id, $student_id);
$stmt->execute();
$skill = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$skill) {
    header("Location: skills.php");
    exit();
}

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Skill - SkillSwap</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 8px; width: 350px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .input-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Edit Skill</h2>
        <form action="edit-skill.php?id=<?php echo $skill_id; ?>" method="POST">
            <div class="input-group">
                <label>Skill Name</label>
                <input type="text" name="skill_name" value="<?php echo htmlspecialchars($skill['skill_name']); ?>" required>
            </div>
            <div class="input-group">
                <label>Category</label>
                <select name="category_id" required>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $cat['category_id']; ?>" <?php if ($cat['category_id'] == $skill['category_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Skill Level</label>
                <select name="skill_level" required>
                    <option value="Beginner" <?php if ($skill['skill_level'] == 'Beginner') echo 'selected'; ?>>Beginner</option>
                    <option value="Intermediate" <?php if ($skill['skill_level'] == 'Intermediate') echo 'selected'; ?>>Intermediate</option>
                    <option value="Expert" <?php if ($skill['skill_level'] == 'Expert') echo 'selected'; ?>>Expert</option>
                </select>
            </div>
            <div class="input-group">
                <label>Type</label>
                <select name="skill_type" required>
                    <option value="offered" <?php if ($skill['skill_type'] == 'offered') echo 'selected'; ?>>Skill I Offer</option>
                    <option value="sought" <?php if ($skill['skill_type'] == 'sought') echo 'selected'; ?>>Skill I Seek</option>
                </select>
            </div>
            <div class="input-group">
                <label>Description</label>
                <textarea name="description" rows="3"><?php echo htmlspecialchars($skill['description']); ?></textarea>
            </div>
            <button type="submit">Update Skill</button>
        </form>
    </div>
</body>
</html>