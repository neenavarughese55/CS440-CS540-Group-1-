<?php
  // Ensure the user is logged in before viewing the User Manual.
  // session_check.php handles authentication and redirects if unauthorized.
  require 'include/session_check.php';
?>

<html lang="en">
<head>
  <meta charset="UTF-8">

  <!-- Forces modern browser rendering mode -->
  <meta http-equiv="x-ua-compatible" content="ie=edge">

  <title>User Manual</title>

  <!-- Makes the layout mobile-friendly and responsive -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Styles for the site header and the user manual page -->
  <link rel="stylesheet" href="./css/header.css">
  <link rel="stylesheet" href="./css/user_manual.css">

</head>

<body>

<!-- Insert the shared navigation/header -->
<?php require 'include/header.php'; ?>

<!-- ======================== USER MANUAL CONTENT ======================== -->
<div class="page">

  <h1>Let’s Book - User Manual</h1>

  <!-- ==== SECTION 1: INTRODUCTION ==== -->
  <h2>1. Introduction</h2>

  <h3>1.1 Website Overview</h3>

  <p>
    Let’s Book is an appointment-booking website that allows users to schedule
    appointments with service providers across three industries: Beauty, Fitness, and Medical.
  </p>

  <!-- Bullet list of supported industries -->
  <ul class="hyphen-list">
    <li>
      In the Beauty category, users can book hair styling, nail care, and other grooming services.
    </li>
    <li>
      In Fitness, users may schedule personal training or guided classes such as yoga.
    </li>
    <li>
      In Medical, users may book consultations with qualified medical professionals.
    </li>
  </ul>

  <p>
    The platform focuses on simplicity, accessibility, and efficiency for booking and managing appointments.
  </p>

  <h3>1.2 Key Features</h3>

  <p>Let’s Book provides essential tools for Admins, Service Providers, and Customers:</p>

  <ul class="hyphen-list">
    <li>User registration and login</li>
    <li>Browse and book services in 3 categories</li>
    <li>One-on-one private appointment scheduling</li>
    <li>Conflict detection (no overlapping appointments)</li>
    <li>Email + in-website notifications</li>
    <li>Service providers can manage their own slots</li>
    <li>Customers can book, cancel, and reschedule</li>
    <li>Admins may deactivate inactive user accounts (except other admins)</li>
  </ul>

  <div class="bold-label">Notifications are sent whenever:</div>
  <ul class="hyphen-list">
    <li>An appointment is booked</li>
    <li>An appointment is modified or canceled</li>
    <li>An appointment is approaching</li>
    <li>A provider cancels or changes an appointment</li>
  </ul>

  <h3>1.3 System Requirements</h3>

  <div class="bold-label">Supported Devices</div>
  <ul class="hyphen-list">
    <li>Desktop or laptop</li>
    <li>Tablet</li>
    <li>Smartphone (iOS/Android)</li>
  </ul>

  <div class="bold-label">Supported Browsers</div>
  <ul class="hyphen-list">
    <li>Google Chrome (recommended)</li>
    <li>Mozilla Firefox</li>
    <li>Safari</li>
    <li>Microsoft Edge</li>
  </ul>

  <p>Old browsers may cause unexpected issues.</p>

  <div class="bold-label">Additional Requirements</div>
  <ul class="hyphen-list">
    <li>Stable internet connection</li>
    <li>Access to the email used during registration</li>
  </ul>

  <div class="bold-label">Note for Local Testing</div>
  <p>If running the site locally, users need XAMPP to run:</p>

  <ul class="hyphen-list">
    <li>Apache (web server)</li>
    <li>MySQL (database)</li>
  </ul>

  <p>Public end-users do not need XAMPP.</p>

  <!-- ==== SECTION 2: GETTING STARTED ==== -->
  <h2>2. Getting Started</h2>

  <h3>2.1 Creating an Account / Logging In</h3>
  <ul class="hyphen-list">
    <li>Click Log In / Register on the Welcome Page.</li>
    <li>Choose the Register tab if you are new.</li>
    <li>Fill in the required fields.</li>
    <li>Select your account type (Customer or Service Provider).</li>
    <li>After registering, switch to Login and sign in.</li>
  </ul>

  <h3>2.2 User Account Types</h3>

  <div class="bold-label">Administrator</div>
  <ul class="hyphen-list">
    <li>Oversees system operations</li>
    <li>View all appointment data</li>
    <li>Deactivate inactive users (but not admins)</li>
    <li>Maintains system integrity</li>
  </ul>

  <div class="bold-label">Service Provider</div>
  <ul class="hyphen-list">
    <li>Create appointment slots</li>
    <li>View only their own appointments</li>
    <li>Access limited customer info</li>
    <li>Upload qualifications</li>
    <li>Cancel appointments ≥24 hours in advance with reason</li>
  </ul>

  <div class="bold-label">Customer</div>
  <ul class="hyphen-list">
    <li>Book existing appointment slots</li>
    <li>View provider qualifications</li>
    <li>Cancel or reschedule ≥24 hours before appointment</li>
    <li>Providers get notified of customer changes</li>
  </ul>

  <h3>2.3 Password Management</h3>
  <ul class="hyphen-list">
    <li>Passwords stored securely (hashed)</li>
    <li>Admins cannot view raw passwords</li>
    <li>Users manage only their own passwords</li>
    <li>Ensures security and privacy compliance</li>
  </ul>

  <!-- ==== SECTION 3: BOOKING ==== -->
  <h2>3. Booking and Managing Appointments</h2>

  <h3>3.1 How to Book an Appointment</h3>
  <ul class="hyphen-list">
    <li>Customers go to Home Page after login</li>
    <li>Available slots are displayed</li>
    <li>Click Book on the desired slot</li>
    <li>A success message appears</li>
    <li>Click “View My Appointments” to review bookings</li>
  </ul>

  <h3>A detailed demo is included later.</h3>

  <h3>3.2 Managing Appointments</h3>
  <div class="bold-label">To cancel or reschedule:</div>
  <ul class="hyphen-list">
    <li>Click View My Appointments</li>
    <li>Select an appointment</li>
    <li>Choose Cancel or Reschedule</li>
  </ul>

  <h3>Previous appointments can be viewed there too.</h3>

  <div class="bold-label">Notifications</div>
  <ul class="hyphen-list">
    <li>View in-website notifications under "Notifications"</li>
    <li>Email notifications are sent automatically</li>
  </ul>

  <h3>A full demo is included later.</h3>

  <!-- ==== SECTION 4: SERVICE PROVIDERS ==== -->
  <h2>4. Service Provider Functions</h2>

  <h3>4.1 Creating Appointment Slots</h3>
  <ul class="hyphen-list">
    <li>Go to Home Page after login</li>
    <li>Click Create Appointment Slot</li>
    <li>Enter date, time, and service details</li>
    <li>Submit to publish the slot</li>
  </ul>

  <h3>4.2 Viewing and Managing Appointments</h3>
  <div class="bold-label">Providers can:</div>
  <ul class="hyphen-list">
    <li>View upcoming appointments</li>
    <li>Cancel with proper notice</li>
    <li>View past appointments</li>
  </ul>

  <!-- ==== SECTION 5: DEMO ==== -->
  <h3>5. Full Demonstration</h3>
  <p>Full Demo:</p>
  <a href="https://drive.google.com/file/d/1G2zdrqsIyKThPy8HbWMU5kwal5PQ-mrz/view">Website Demo</a>

  <!-- ==== SECTION 6: TROUBLESHOOTING ==== -->
  <h3>6. Troubleshooting</h3>

  <div class="bold-label">Common Issue: 404 Error</div>
  <p>A 404 error occurs when:</p>
  <ul class="hyphen-list">
    <li>The page is missing</li>
    <li>A broken link is clicked</li>
    <li>The server is temporarily unavailable</li>
  </ul>

  <div class="bold-label">Possible Solutions</div>
  <ul class="hyphen-list">
    <li>Refresh the page</li>
    <li>Go back to Home Page</li>
    <li>Clear browser cache</li>
    <li>Check your internet connection</li>
  </ul>

  <p>Contact support if issues persist.</p>

  <!-- ==== SECTION 7: SUPPORT ==== -->
  <h3>7. Contact Support</h3>
  <ul class="hyphen-list">
    <li>Quang Do: do4185@uwlax.edu</li>
    <li>Anton Cortes: cortes8141@uwlax.edu</li>
    <li>Yao Yao: yao9510@uwlax.edu</li>
    <li>Neena Varughese: varughese7529@uwlax.edu</li>
  </ul>

</div> <!-- End .page -->

</body>
</html>
