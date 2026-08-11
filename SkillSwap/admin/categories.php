<?php
session_start();
include "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = trim($_POST['category_name']);
    if (!empty($category_name)) {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        $stmt->close();
    }
}

$categories = $conn->query("SELECT * FROM categories ORDER BY category_id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="brand">SkillSwap Admin</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="students.php">Manage Students</a></li>
            <li><a href="skills.php">Manage Skills</a></li>
            <li><a href="categories.php" class="active">Categories</a></li>
        </ul>
    </div>
    <div class="main-content">
        <h2>Manage Categories</h2>
        <div class="form-card" style="margin: 20px 0;">
            <form action="categories.php" method="POST">
                <div class="input-group">
                    <label>New Category Name</label>
                    <input type="text" name="category_name" required placeholder="e.g. Graphic Design">
                </div>
                <button type="submit" class="btn">Add Category</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $cat['category_id']; ?></td>
                    <td><?php echo htmlspecialchars($cat['category_name']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>