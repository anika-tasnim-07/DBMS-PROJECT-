<?php
session_start();
include "config/database.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $phone      = trim($_POST['phone']);
    $department = trim($_POST['department']);
    $semester   = trim($_POST['semester']);

    // Strict domain validation for @gmail.com
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\.com$/', $email)) {
        $error = "Invalid email format! Email must end with @gmail.com (e.g., user@gmail.com).";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT student_id FROM students WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $error = "An account with this email already exists!";
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO students (name, email, password, phone, department, semester) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssssss", $name, $email, $hashed_password, $phone, $department, $semester);

            if ($insert_stmt->execute()) {
                $message = "Registration successful! You can now <a href='login.php'>Login here</a>.";
            } else {
                $error = "Something went wrong during registration. Please try again.";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - SkillSwap</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .msg-success { color: #10b981; font-weight: bold; margin-bottom: 15px; }
        .msg-error { color: #ef4444; font-weight: bold; margin-bottom: 15px; }
    </style>
</head>
<body>

    <div class="form-card">
        <h2>Create an Account</h2>
        <p style="color: var(--text-muted); margin-bottom: 20px;">Join SkillSwap to trade knowledge with peers.</p>

        <?php if (!empty($message)): ?>
            <div class="msg-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="msg-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="input-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required placeholder="John Doe">
            </div>

            <div class="input-group">
                <label for="email">Email Address (@gmail.com only)</label>
                <input type="email" id="email" name="email" required placeholder="example@gmail.com">
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Minimum 6 characters">
            </div>

            <div class="input-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" placeholder="+1234567890">
            </div>

            <div class="input-group">
                <label for="department">Department</label>
                <input type="text" id="department" name="department" placeholder="Computer Science">
            </div>

            <div class="input-group">
                <label for="semester">Semester</label>
                <input type="text" id="semester" name="semester" placeholder="5th">
            </div>

            <button type="submit" class="btn" style="width: 100%;">Register</button>
        </form>

        <p style="margin-top: 15px; text-align: center; font-size: 14px;">
            Already have an account? <a href="login.php" style="color: var(--primary);">Login here</a>
        </p>
    </div>

</body>
</html>