<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
    switch ($_SESSION['user_type']) {
        case 'admin':
            header('Location: admin/admin_dashboard.php');
            exit;
        case 'doctor':
            header('Location: doctor/doctor_dashboard.php');
            exit;
        case 'patient':
            header('Location: patient/patient_dashboard.php');
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcare Appointment System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="static/home.css">
</head>
<body>
    <main class="hero-shell">
        <section class="hero-card">
            <div class="brand">
                <span class="brand-icon"><i class="fa-solid fa-heart-pulse"></i></span>
                <p>BCI Healthcare</p>
            </div>

            <h1>Healthcare Appointment System</h1>
            <p class="subtitle">Book appointments faster, manage care smoothly, and connect patients with doctors in one reliable platform.</p>

            <div class="actions">
                <a class="btn btn-primary" href="auth/login.php">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Login
                </a>
                <a class="btn btn-secondary" href="auth/register.php">
                    <i class="fa-solid fa-user-plus"></i>
                    Register
                </a>
            </div>

            <div class="stats">
                <article>
                    <h2>24/7</h2>
                    <p>Patient Access</p>
                </article>
                <article>
                    <h2>Secure</h2>
                    <p>Data Handling</p>
                </article>
                <article>
                    <h2>Fast</h2>
                    <p>Appointment Flow</p>
                </article>
            </div>
        </section>
    </main>
</body>
</html>
