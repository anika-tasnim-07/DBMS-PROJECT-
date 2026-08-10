<?php
session_start();
include "config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT student_id, name, password, is_admin FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['name']       = $user['name'];
                $_SESSION['is_admin']   = ($user['is_admin'] == 1);

                // Route admin to admin panel, regular students to dashboard
                if ($_SESSION['is_admin']) {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "No account found with this email address.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - SkillSwap</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="form-card">
        <h2>Login to SkillSwap</h2>
        <p style="color: var(--text-muted); margin-bottom: 20px;">Welcome back! Please enter your details.</p>

        <?php if (!empty($error)): ?>
            <p style="color: red; font-weight: bold; margin-bottom: 15px;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="input-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="example@gmail.com">
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter password">
            </div>

            <button type="submit" class="btn" style="width: 100%;">Login</button>
        </form>

        <p style="margin-top: 15px; text-align: center; font-size: 14px;">
            Don't have an account? <a href="register.php" style="color: var(--primary);">Register here</a>
        </p>
    </div>

</body>
</html>