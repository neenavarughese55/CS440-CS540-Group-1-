# CS440-CS540-Group-1-
Let’s Book - User Manual 
1. Introduction
   
1.1 Website Overview 

Let’s Book is an appointment-booking website that allows users to schedule 
appointments with service providers across three industries: Beauty, Fitness, and 
Medical. 

• In the Beauty category, users can book services such as hair styling, nail care, 
and other personal grooming services. 
• In Fitness, users may schedule one-on-one personal training sessions or join 
guided sessions such as yoga. 
• In Medical, users can book consultations to discuss general health concerns 
with qualified medical professionals. 
Let’s Book offers a streamlined, user-friendly experience intended to make booking and 
managing appointments simple, accessible, and efficient. 

1.2 Key Features

Let’s Book provides essential tools for Administrators, Service Providers, and 
Customers: 

• User registration and login for all role types 
• Browsing and booking services across three categories 
• One-on-one appointment scheduling (ensuring privacy and avoiding conflicts) 
• Automatic conflict detection to prevent overlapping appointments 
• Email and in-website notifications for booking confirmations, cancellations, and 
reminders 
• Service providers can create, modify, and manage their own appointment slots 
• Customers can book, cancel, and reschedule appointments within the allowed 
time window 
• Admins can deactivate inactive customer or service provider accounts (admins 
cannot deactivate other admins)

Notifications are sent whenever:

• An appointment is booked 
• An appointment is changed or cancelled 
• A scheduled appointment is approaching 
• A service provider cancels or modifies an appointment

1.3 System Requirements

Supported Devices 
• Desktop or laptop computer 
• Tablet 
• Smartphone (iOS or Android)

Supported Browsers 
• Google Chrome (latest version recommended) 
• Mozilla Firefox 
• Safari (macOS/iOS) 
• Microsoft Edge 

Using outdated browsers may result in unexpected behaviour.

Additional Requirements 
• Stable internet connection 
• Access to the email address used during registration (for confirmations and 
reminders) 

Note for Development or Local Testing 
If the system is being run locally (e.g., for academic or testing purposes), users will need 
XAMPP to run: 
• Apache (for the website) 
• MySQL (for the database) 
End-users of a deployed public website do not require XAMPP. 
3. Getting Started 
2.1 Creating an Account / Logging In 
1. On the Welcome Page, click Log In / Register at the top of the screen. 
2. 
3. 
4. 
If you are a new user, select the Register tab. 
Enter your personal details. 
Choose your account type under Register As: 
• Service Provider 
• Customer 
For Service Providers: 
For Customers: 
5. 
After completing registration, proceed to the Login tab and enter your email and 
password. 
2.2 User Account Types 
Administrator 
• Oversees overall system operations 
• Views all appointment activity via the database 
• Can deactivate customer and service provider accounts that have been inactive 
for extended periods 
• Cannot deactivate other administrators 
• Helps maintain integrity and proper functioning of the system 
Service Provider 
• Creates available appointment slots for customers 
• Can view only their own appointments 
• Has access to limited customer information (name and email) 
• Must upload qualification details for customer transparency 
• May cancel appointments up to 24 hours before the appointment date and must 
provide a reason for the cancellation 
Customer 
• Books appointments created by service providers 
• Can view their own information and the qualifications of service providers 
• May cancel or reschedule appointments up to 24 hours before the appointment 
date 
• Service providers receive notifications when customers cancel or modify 
bookings 
2.3 Password Management 
• Passwords are securely stored using one-way hashing. 
• Administrators cannot view user passwords and may only reset them when 
necessary. 
• Users can manage only their own passwords. 
• This ensures compliance with security standards and protects personal 
information. 
3. Booking and Managing Appointments 
3.1 How to Book an Appointment 
1. After logging in, customers are directed to the Home Page. 
2. Available appointment slots are displayed. 
3. Click Book next to the desired date and time. 
4. A confirmation message will appear: 
“Appointment successfully booked! [Date and Time Slot]” 
5. To review your bookings, click View My Appointments (top right). 
A detailed demo is included later in the manual. 
3.2 Managing Appointments 
To cancel or reschedule an appointment: 
1. Click View My Appointments. 
2. Select the appointment you wish to modify. 
3. Choose Cancel or Reschedule (as the system allows). 
Previous appointments can also be viewed here. 
Notifications 
• Website notifications can be accessed through the Notifications tab. 
• Email notifications are sent automatically using the email provided during 
registration. 
A full demo is provided later in the manual. 
4. Service Provider Functions 
4.1 Creating Appointment Slots 
1. After logging in, service providers arrive at the Home Page. 
2. Click Create Appointment Slot at the top right. 
3. Enter details such as date, time, and service type. 
4. Submit the slot to make it available to customers. 
4.2 Viewing and Managing Appointments 
Service providers can: 
• View all upcoming bookings under View Appointments 
• Cancel appointments within the allowed timeframe 
• View past appointments under Passed Appointments 
5. Full Demonstration 
Full Demo: Website demo.mp4 
6. Troubleshooting 
Common Issue: 404 Error 
A 404 error may appear if: 
• The page has been moved or deleted 
• A broken or outdated link is accessed 
• The server is temporarily unavailable 
Possible Solutions 
• Refresh the page 
• Return to the Home Page 
• Clear browser cache 
• Ensure your internet connection is stable 
If the issue persists, contact support. 
7. Contact Support 
If you need assistance, please reach out to our support team: 
• Quang Do: do4185@uwlax.edu 
• Anton Cortes: cortes8141@uwlax.edu 
• Yao Yao: yao9510@uwlax.edu 
• Neena Varughese: varughese7529@uwlax.edu 

