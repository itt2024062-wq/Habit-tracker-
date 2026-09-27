🎯 Habit Tracker Web Application

A full-stack web application designed to help users track their daily habits, monitor progress, and achieve their goals effectively. Built using PHP, MySQL, HTML5, CSS3, and JavaScript.

🚀 Features

User Authentication: Secure Registration, Login, and Logout functionality.

Interactive Dashboard: Quick summary of active habits and overall daily progress.

Habit Management:

Add new habits with custom schedules/frequencies.

View, edit, and delete existing habits.

Mark daily habits as completed.

Progress Tracking: Visual analytics and reports to track consistency over time.

Responsive Design: Clean UI/UX optimized for both mobile and desktop screens.

Contact & About Pages: User support and project details.

🛠️ Tech Stack

Frontend: HTML5, CSS3 (style.css), JavaScript

Backend: PHP (Procedural/OOP)

Database: MySQL (database.sql)

Server: Apache (XAMPP / WAMP / Lamp Stack)

📂 Project Structure

habit_tracker/
│
├── config.php          # Database configuration and connection settings
├── database.sql        # Database schema and tables
├── index.php           # Landing page / Homepage
├── login.php           # User login logic & UI
├── register.php        # User registration logic & UI
├── logout.php          # Session termination
├── dashboard.php       # User dashboard page
├── add-habit.php       # Form to add a new habit
├── my-habits.php       # View and manage user habits
├── progress.php       # Progress tracking and analytics page
├── about.php          # About the application
├── contact.php        # Contact page
└── style.css          # Global stylesheet


⚡ Setup & Installation Guide

Follow these simple steps to run the project locally on your machine using XAMPP:

1. Prerequisites

Download and install XAMPP.

2. Clone/Download Project

Place the project folder inside your XAMPP server's root directory:

Windows: C:\xampp\htdocs\habit_tracker

macOS: /Applications/XAMPP/htdocs/habit_tracker

3. Database Setup

Open XAMPP Control Panel and start Apache and MySQL.

Go to your browser and open PHPMyAdmin: http://localhost/phpmyadmin

Create a new database named habit_tracker (or check config.php for the exact name used).

Click on the Import tab.

Choose the database.sql file provided in the project folder and click Go / Import.

4. Database Configuration

Open config.php and verify the database connection settings match your local environment:

$host = "localhost";
$username = "root";      // Default XAMPP username
$password = "";          // Default XAMPP password (blank)
$dbname = "habit_tracker";


5. Launch Application

Open your web browser and visit:

http://localhost/habit_tracker/


🛡️ Security Features

Prepared SQL statements / Query sanitization to protect against SQL Injection.

Password hashing (e.g., password_hash()) for secure authentication.

Active PHP session validation across protected routes (Dashboard, My Habits, Progress).

🤝 Contributing

Contributions, issues, and feature requests are welcome! Feel free to fork the repository and submit a pull request.

📝 License

This project is open-source and available under the MIT License.
