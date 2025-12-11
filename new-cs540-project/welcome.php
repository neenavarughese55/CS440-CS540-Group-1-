<!-- Code review done by Neena Varughese -->

<!DOCTYPE html>
<html lang="en">  <!-- Set language of the websote to English -->
    <head>
        <!-- Basic HTML metadata -->
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>Appointment Booking - Welcome</title>

        <!-- Bootstrap CSS (CDN) for layout and components -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Bootstrap JS bundle (includes Popper) -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Custom styles for the welcome page -->
        <link rel="stylesheet" href="css/welcome.css">
    </head>

    <!-- Background styling is controlled via #background in welcome.css -->
    <body id="background">

        <!-- ⭐ Navigation Bar -->
        <header>
            <nav class="navbar navbar-expand-lg bg-body-tertiary">
                <!-- Bootstrap 'container' centers the navbar content -->
                <div class="container">

                    <!-- Brand / Logo link to the welcome page -->
                    <a class="navbar-brand" href="welcome.php">Let's Book</a>

                    <!-- Mobile hamburger menu toggle -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarContent"> <!-- ID of the collapsible menu -->
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <!-- Collapsible navigation content -->
                    <div class="collapse navbar-collapse" id="navbarContent">
                        <div class="navbar-nav ms-auto">
                            <!-- Home link (commented out for now) -->
                            <!-- <a class="nav-link active" href="welcome.php">Home</a> -->

                            <!-- ⭐⭐ Link to login/register page (index.php) ⭐⭐ -->
                            <a class="nav-link" href="index.php">Log In / Register</a>
                        </div>
                    </div>
                </div>
            </nav>
        </header>

        <!-- ⭐ Hero Section: main intro area with image and text -->
        <section class="hero">
            <div class="hero-container">

                <!-- Hero image card (illustrative graphic for the 3 services) -->
                <div class="hero-image-card">
                    <img src="images/fitbeauheal.webp" alt="logo" />
                </div>

                <!-- Hero text block -->
                <div class="hero-text">
                    <h1>Welcome</h1> <!-- Large heading -->
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

        <!-- ⭐ Services Section: three main service categories -->
        <section class="services">

            <!-- Beauty category card -->
            <div class="service-card beauty">
                <img src="images/beauty.jpg" alt="Beauty services">
                <div class="service-text">
                    <h2>Beauty</h2>
                    <p>Nails, hair, makeup, and wellness treatments from top providers.</p>

                    <!-- ⭐ Link to login/register page (index.php) -->
                    <a href="index.php">Login/Register →</a>
                </div>
            </div>

            <!-- Health category card -->
            <div class="service-card health">
                <img src="images/health.webp" alt="Health services">
                <div class="service-text">
                    <h2>Health</h2>
                    <p>Doctors, therapy, and wellness appointments made easy.</p>

                    <!-- Link to login/register page -->
                    <a href="index.php">Login/Register →</a>
                </div>
            </div>

            <!-- Fitness category card -->
            <div class="service-card fitness">
                <img src="images/fitness.jpg" alt="Fitness services">
                <div class="service-text">
                    <h2>Fitness</h2>
                    <p>Personal training, gym memberships, and group classes.</p>

                    <!-- Link to login/register page -->
                    <a href="index.php">Login/Register →</a>
                </div>
            </div>

        </section> <!-- End of services section -->
    </body>

</html>
