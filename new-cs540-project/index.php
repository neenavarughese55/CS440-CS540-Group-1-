<!-- Code Review done by Neena Varughese -->
<?php
    // Enable all PHP error reporting (useful during development)

    error_reporting(E_ALL);

    // Show the errors directly on the webpage for debugging
    // NOTE: Turn this off in production to avoid security risks
    ini_set('display_errors', '1');
?>

<!DOCTYPE html>
<html lang="en"> <!-- Set language of the websote to English -->
    <head>
        <meta charset="UTF-8"> <!-- Sets character encoding to UTF-8 for special characters -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Makes the page responsive on mobile devices -->
        
        <title>Let's Book - Welcome</title> <!-- Tab name -->

        <!-- Bootstrap 5 CSS from CDN for layout & styling-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Bootstrap JS bundle (enables navbar toggle menu, modals, etc.) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <link rel="stylesheet" href="css/welcome.css">
    </head>

    <body id="background">  <!-- 'background' used in CSS to style the body -->

        <!-- ⭐ Navigation -->
        <header>
            <nav class="navbar navbar-expand-lg bg-body-tertiary">
                <!-- Bootstrap 'container' centers the navbar content -->
                <div class="container">
                     <!-- Website name / logo, links back to welcome page -->
                    <a class="navbar-brand" href="welcome.php">Let's Book!</a>

                    <!-- Button that appears on mobile (hamburger icon) -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarContent"> <!-- ID of the collapsible menu -->
                        <span class="navbar-toggler-icon"></span>
                    </button>

                     <!-- Collapsible section for mobile view -->
                    <div class="collapse navbar-collapse" id="navbarContent">
                         <!-- 'ms-auto' pushes the links to the right side -->
                        <div class="navbar-nav ms-auto">
                            <!-- Highlighted navigation item for Home -->
                            <a class="nav-link active" href="welcome.php">Home</a>

                            <!-- Login/Register link -->
                            <a class="nav-link" href="index_demo.php">Log In / Register</a>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <!-- ⭐ Hero Section -->
        <section class="hero"> <!-- Section: main introduction banner -->
            <div class="hero-container"> <!-- Likely styled with flexbox in CSS -->

                <!-- Left side: image card -->
                <div class="hero-image-card">
                    <!-- Website’s combined category illustration -->
                    <img src="images/fitbeauheal.webp" alt="logo" />
                </div>

                <!-- Right side: welcome message -->
                <div class="hero-text">
                    <h1>Welcome</h1> <!-- Large heading -->
                    <p>
                         <!-- Intro paragraph explaining the website purpose -->
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
        <section class="services"> <!-- Section containing all 3 service boxes -->

            <div class="service-card beauty"> <!-- Card styled with 'beauty' theme color -->
                <!-- Category image -->
                <img src="images/beauty.jpg" alt="Beauty services">

                <!-- Text inside the card -->
                <div class="service-text">
                    <h2>Beauty</h2> <!-- Category title -->
                    <p>Nails, hair, makeup, and wellness treatments from top providers.</p>

                    <!-- Link requires user to login/register -->
                    <a href="index_demo.php">Login/Register →</a>
                </div>
            </div>

            <div class="service-card health"> <!-- 'health' class sets card color -->
                <img src="images/health.webp" alt="Medical services">
                <div class="service-text">
                    <h2>Medical</h2>
                    <p>Doctors, therapy, and wellness appointments made easy.</p>

                    <a href="index_demo.php">Login/Register →</a>
                </div>
            </div>

            <div class="service-card fitness"> <!-- 'fitness' class sets card color -->
                <img src="images/fitness.jpg" alt="Fitness services">
                <div class="service-text">
                    <h2>Fitness</h2>
                    <p>Personal training, gym memberships, and group classes.</p>

                    <a href="index_demo.php">Login/Register →</a>
                </div>
            </div>

        </section> <!-- End of services section -->
    </body>

</html>
