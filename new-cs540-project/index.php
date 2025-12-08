<?php
    // 如果你需要 session 检查，可保留，不需要可删掉
    // require 'include/session_check.php';

    error_reporting(E_ALL);
    ini_set('display_errors', '1');
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Appointment Booking - Welcome</title>

        <!-- Bootstrap -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <link rel="stylesheet" href="css/welcome.css">
    </head>

    <body id="background">

        <!-- ⭐ Navigation -->
        <header>
            <nav class="navbar navbar-expand-lg bg-body-tertiary">
                <div class="container">
                    <a class="navbar-brand" href="welcome.php">BeautyHealthFit</a>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarContent">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="navbarContent">
                        <div class="navbar-nav ms-auto">
                            <a class="nav-link active" href="welcome.php">Home</a>

                            <!-- ⭐⭐ 这里跳转到登录注册页面 ⭐⭐ -->
                            <a class="nav-link" href="index_demo.php">Log In / Register</a>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <!-- ⭐ Hero Section -->
        <section class="hero">
            <div class="hero-container">

                <div class="hero-image-card">
                    <img src="images/fitbeauheal.webp" alt="logo" />
                </div>

                <div class="hero-text">
                    <h1>Welcome</h1>
                    <p>
                        Do not know where to find a trustworthy service from getting your nails done
                        to examining your health or exercise your productivity?
                        You have come to the right place.
                        We offer beauty, health, and fitness services with just one click.
                        Explore options and find the right expert for your needs!
                    </p>
                </div>

            </div>
        </section>

        <!-- ⭐ Services -->
        <section class="services">

            <div class="service-card beauty">
                <img src="images/beauty.jpg" alt="Beauty services">
                <div class="service-text">
                    <h2>Beauty</h2>
                    <p>Nails, hair, makeup, and wellness treatments from top providers.</p>

                    <!-- ⭐ 跳转到登录注册页面 -->
                    <a href="index_demo.php">Login/Register →</a>
                </div>
            </div>

            <div class="service-card health">
                <img src="images/health.webp" alt="Health services">
                <div class="service-text">
                    <h2>Health</h2>
                    <p>Doctors, therapy, and wellness appointments made easy.</p>

                    <a href="index_demo.php">Login/Register →</a>
                </div>
            </div>

            <div class="service-card fitness">
                <img src="images/fitness.jpg" alt="Fitness services">
                <div class="service-text">
                    <h2>Fitness</h2>
                    <p>Personal training, gym memberships, and group classes.</p>

                    <a href="index_demo.php">Login/Register →</a>
                </div>
            </div>

        </section>
    </body>

</html>
