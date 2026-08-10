<?php
session_start();
include "config/database.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get search inputs
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$category_filter = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$level_filter = isset($_GET['skill_level']) ? $_GET['skill_level'] : '';

// Build Search Query with Dynamic SQL Conditions
$sql = "SELECT s.*, st.name as student_name, c.category_name 
        FROM skills s 
        JOIN students st ON s.student_id = st.student_id 
        JOIN categories c ON s.category_id = c.category_id 
        WHERE s.student_id != ? AND s.skill_type = 'offered'";

$params = [$student_id];
$types = "i";

if ($search_query != '') {
    $sql .= " AND s.skill_name LIKE ?";
    $params[] = "%" . $search_query . "%";
    $types .= "s";
}
if ($category_filter > 0) {
    $sql .= " AND s.category_id = ?";
    $params[] = $category_filter;
    $types .= "i";
}
if ($level_filter != '') {
    $sql .= " AND s.skill_level = ?";
    $params[] = $level_filter;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$search_results = $stmt->get_result();

// --- AUTOMATIC MATCHING ALGORITHM ---
// Find students where:
// My Offered Skill = Their Sought Skill AND My Sought Skill = Their Offered Skill
$match_sql = "
    SELECT DISTINCT 
        st.student_id as matched_student_id,
        st.name as matched_student_name,
        my_offer.skill_name as my_offered,
        their_offer.skill_name as their_offered,
        their_offer.skill_id as requested_skill_id,
        my_offer.skill_id as offered_skill_id
    FROM skills my_offer
    JOIN skills my_seek ON my_offer.student_id = ? AND my_offer.skill_type = 'offered' AND my_seek.student_id = ? AND my_seek.skill_type = 'sought'
    JOIN skills their_seek ON LOWER(their_seek.skill_name) = LOWER(my_offer.skill_name) AND their_seek.skill_type = 'sought'
    JOIN skills their_offer ON LOWER(their_offer.skill_name) = LOWER(my_seek.skill_name) AND their_offer.skill_type = 'offered' AND their_offer.student_id = their_seek.student_id
    JOIN students st ON st.student_id = their_offer.student_id
    WHERE st.student_id != ?
";

$match_stmt = $conn->prepare($match_sql);
$match_stmt->bind_param("iii", $student_id, $student_id, $student_id);
$match_stmt->execute();
$matches = $match_stmt->get_result();

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search & Matches - SkillSwap</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 20px; }
        .match-box { background: #e3f2fd; border: 1px solid #90caf9; padding: 15px; border-radius: 8px; margin-bottom: 30px; }
        .filter-form { background: white; padding: 15px; border-radius: 8px; display: flex; gap: 10px; align-items: center; margin-bottom: 20px; }
        .grid { display: flex; flex-wrap: wrap; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; width: 280px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn { padding: 8px 12px; background: #28a745; color: white; border: none; text-decoration: none; border-radius: 4px; cursor: pointer; display: inline-block; }
    </style>
</head>
<body>
    <a href="dashboard.php" style="float: right;">Back to Dashboard</a>
    <h2>Search Skills & Partners</h2>

    <!-- Automatic Match Recommendations -->
    <?php if ($matches->num_rows > 0): ?>
        <div class="match-box">
            <h3>🎯 Perfect Skill Matches Found!</h3>
            <?php while ($m = $matches->fetch_assoc()): ?>
                <p>
                    <strong><?php echo htmlspecialchars($m['matched_student_name']); ?></strong> offers 
                    <strong><?php echo htmlspecialchars($m['their_offered']); ?></strong> and wants your 
                    <strong><?php echo htmlspecialchars($m['my_offered']); ?></strong>! 
                    <a href="send-request.php?receiver_id=<?php echo $m['matched_student_id']; ?>&offered_id=<?php echo $m['offered_skill_id']; ?>&requested_id=<?php echo $m['requested_skill_id']; ?>" class="btn">Request Swap</a>
                </p>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <!-- Search Form -->
    <form class="filter-form" method="GET" action="search.php">
        <input type="text" name="q" placeholder="Search skill..." value="<?php echo htmlspecialchars($search_query); ?>" style="padding: 8px; width: 200px;">
        <select name="category_id" style="padding: 8px;">
            <option value="0">All Categories</option>
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?php echo $cat['category_id']; ?>" <?php if ($category_filter == $cat['category_id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($cat['category_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <select name="skill_level" style="padding: 8px;">
            <option value="">All Levels</option>
            <option value="Beginner" <?php if ($level_filter == 'Beginner') echo 'selected'; ?>>Beginner</option>
            <option value="Intermediate" <?php if ($level_filter == 'Intermediate') echo 'selected'; ?>>Intermediate</option>
            <option value="Expert" <?php if ($level_filter == 'Expert') echo 'selected'; ?>>Expert</option>
        </select>
        <button type="submit" class="btn" style="background:#007bff;">Search</button>
    </form>

    <!-- Results Grid -->
    <div class="grid">
        <?php if ($search_results->num_rows > 0): ?>
            <?php while ($row = $search_results->fetch_assoc()): ?>
                <div class="card">
                    <h3><?php echo htmlspecialchars($row['skill_name']); ?></h3>
                    <p><strong>Offered By:</strong> <?php echo htmlspecialchars($row['student_name']); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($row['category_name']); ?></p>
                    <p><strong>Level:</strong> <?php echo htmlspecialchars($row['skill_level']); ?></p>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <a href="send-request.php?receiver_id=<?php echo $row['student_id']; ?>&requested_id=<?php echo $row['skill_id']; ?>" class="btn">Request Exchange</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No skills found matching your search.</p>
        <?php endif; ?>
    </div>
</body>
</html>