<?php
session_start();

// If student is already logged in, redirect straight to dashboard
if (isset($_SESSION['student_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to SkillSwap</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar Styling */
        .navbar {
            background-color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 50px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .navbar .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            text-decoration: none;
        }

        .nav-buttons .btn {
            padding: 9px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin-left: 10px;
            transition: background 0.2s ease-in-out;
        }

        .btn-outline {
            border: 2px solid #007bff;
            color: #007bff;
            background: transparent;
        }

        .btn-outline:hover {
            background: #007bff;
            color: #ffffff;
        }

        .btn-primary {
            background: #007bff;
            color: #ffffff;
            border: 2px solid #007bff;
        }

        .btn-primary:hover {
            background: #0056b3;
            border-color: #0056b3;
        }

        /* Hero Section */
        .hero {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 60px 20px;
        }

        .hero h1 {
            font-size: 48px;
            color: #1e293b;
            margin-bottom: 15px;
        }

        .hero p {
            font-size: 20px;
            color: #64748b;
            max-width: 600px;
            margin-bottom: 35px;
            line-height: 1.5;
        }

        .hero-cta {
            display: flex;
            gap: 15px;
        }

        .hero-cta .btn {
            padding: 12px 30px;
            font-size: 16px;
        }

        /* Feature Cards */
        .features {
            display: flex;
            justify-content: center;
            gap: 25px;
            padding: 0 20px 60px 20px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .feature-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 8px;
            flex: 1;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            text-align: center;
        }

        .feature-card h3 {
            margin-bottom: 10px;
            color: #007bff;
        }

        .feature-card p {
            color: #6c757d;
            font-size: 14px;
        }

        footer {
            text-align: center;
            padding: 20px;
            background: #ffffff;
            color: #94a3b8;
            font-size: 14px;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <nav class="navbar">
        <a href="index.php" class="logo">SkillSwap</a>
        <div class="nav-buttons">
            <a href="login.php" class="btn btn-outline">Log In</a>
            <a href="register.php" class="btn btn-primary">Sign Up</a>
        </div>
    </nav>

    <!-- Main Hero Section -->
    <section class="hero">
        <h1>Welcome to SkillSwap</h1>
        <p>Exchange Skills. Grow Together. Connect with peers to share your expertise, learn new technologies, and complete verified skill assessments.</p>
        
        <div class="hero-cta">
            <a href="login.php" class="btn btn-primary">Get Started</a>
            <a href="register.php" class="btn btn-outline">Create Account</a>
        </div>
    </section>

    <!-- Feature Overview -->
    <section class="features">
        <div class="feature-card">
            <h3>🤝 Swap Skills</h3>
            <p>Offer your strengths to other students and request help in areas you want to master.</p>
        </div>
        <div class="feature-card">
            <h3>📝 Skill Verifications</h3>
            <p>Take interactive tests to earn verified badges ranging from Beginner to Expert.</p>
        </div>
        <div class="feature-card">
            <h3>📈 Admin Portal</h3>
            <p>Comprehensive dashboard monitoring scores, active swaps, and user management.</p>
        </div>
    </section>

    <footer>
        &copy; <?php echo date('Y'); ?> SkillSwap Platform. All rights reserved.
    </footer>

</body>
</html>