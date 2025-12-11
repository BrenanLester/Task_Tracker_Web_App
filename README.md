# DebugMyDay: Task Tracker 

<p align="center">
  <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5 Badge">
  <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3 Badge">
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript Badge">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Badge">
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL Badge">
</p>

## 🌟 Project Name and Description

*DebugMyDay* is a powerful, all-in-one digital workspace meticulously engineered for peak efficiency and focus. It provides a clean, intuitive, and responsive platform to seamlessly organize, manage, and track your daily tasks, professional projects, and long-term goals.

*Key Value Proposition:*
* *Intelligent Task Prioritization:* Utilizing the proven *Eisenhower Matrix* to help you distinguish between urgent/important tasks and focus on what truly matters.
* *Deep Focus Integration:* The built-in *POMODORO Timer* helps you establish focused work intervals and prevent burnout.
* *Personalized Experience:* Features like Dark Mode and customizable time formats ensure the workspace adapts to your preference, maximizing concentration and productivity.

## 🧑‍💻 Group Members and Contributions

This project was built by a dedicated team of developers, designers, and quality assurance specialists.

| Member | Role | Key Contributions |
| :--- | :--- | :--- |
| *Rocelyn Lava* | Front-End Developer, Project Manager | Led project coordination and management. Developed key user interface components and ensured front-end integration. |
| *Lorein Manluctao* | Front-End Developer, QA Tester, Documentation | Assisted with front-end development. Responsible for comprehensive testing and drafting initial documentation. |
| *Brenan Lester Espeleta* | Back-End Developer, Database Lead | Developed the core server-side logic using *PHP*. Led the design, management, and connectivity of the project database. |
| *Winston Gultiano* | Front-End Developer (JavaScript Specialist) | Implemented complex front-end functionality, focusing on interactive elements and general JavaScript modifications throughout the application. |
| *Louise Buen* | Database Designer, UI Specialist | Designed the robust database schema. Implemented the user-friendly Logout confirmation modal and associated logic. |

## 🛠️ Features and Technologies Used

### Core Features

| Category | Feature | Description |
| :--- | :--- | :--- |
| *Productivity & Prioritization* | *Tasks & Eisenhower Matrix* | Core functionality to manage/organize tasks. Tasks are added to one of four quadrants (Urgent/Important, etc.) for smart prioritization. Includes full CRUD (Create, Read, Update, Delete) support with user-friendly notification feedback. |
| | *POMODORO Timer* | Dedicated timer feature based on the Pomodoro Technique to encourage focused work. Includes an optional music player to help maintain concentration. |
| *Dashboard* | *Real-time Overview* | Features a calendar and clock for time/date reference. Displays key metrics: total tasks, pending tasks, and completed tasks. Also includes a *Recent Activity* log. |
| *User Management* | *Authentication & Profile* | Secure *Hero Page* that redirects to Login/Registration forms. Users can manage their account via the *Profile* section: edit username, update password, and permanently delete their account. |
| *User Experience (UX)* | *Dark Mode & Responsiveness* | Toggleable *Dark Mode* setting for reduced eye strain. The application features a fully *Responsive* design (optimized for various screen sizes). |
| | *Settings* | Allows users to change the dashboard's time format (e.g., 12-hour or 24-hour) for personal preference. |
| | *General UI/UX* | *User Friendly* interface, and a *Logout* function that prompts a confirmation modal before redirecting to the login/register page. |

### Technologies

| Type | Technology | Purpose |
| :--- | :--- | :--- |
| *Frontend* | HTML5, CSS3, JavaScript | Structure, styling, and client-side interactivity. The UI is built to be responsive and dynamic. |
| *Styling* | Custom CSS, *Boxicons* (for icons) | Modern, clean aesthetic utilizing a custom pastel purple palette. |
| *Backend* | *PHP* | Server-side scripting language for handling user authentication, task logic, and database operations. |
| *Database* | *MySQL / MariaDB* | Used for data persistence, including user accounts, profile information, and all task/project data. |

## 🚀 Instructions to Run the Project Locally

To set up and run *DebugMyDay* on your local machine, follow these steps:

### Prerequisites

You will need a local server environment that can run PHP and MySQL. We recommend using a package like:
* *XAMPP* (Windows, Linux, macOS)
* *WAMP* (Windows)
* *MAMP* (macOS, Windows)

### 1. Database Setup

1.  Start your Apache and MySQL/MariaDB services using your XAMPP/WAMP/MAMP control panel.
2.  Open your database management tool (e.g., PHPMyAdmin via http://localhost/phpmyadmin).
3.  Create a new database (e.g., debugmyday_db).
4.  *Database Schema:* You must import the database schema (.sql file) to create the necessary tables for users, tasks, etc. (Note: Please ensure you have your SQL schema file ready for this step).

### 2. Project Files Setup

1.  Navigate to your local server's root directory (e.g., C:\xampp\htdocs\ or /Applications/MAMP/htdocs/).
2.  Create a new folder for the project (e.g., DebugMyDay).
3.  Place all project files (including index.html, style.css, script.js, and the src/ folder with PHP files) into this new folder.

### 3. Backend Configuration

1.  Locate your database connection file (e.g., a config.php or similar file).
2.  Update the database credentials in this file to match your local setup:
    * DB_SERVER (e.g., localhost)
    * DB_USERNAME (e.g., root)
    * DB_PASSWORD (e.g., leave blank for default XAMPP/WAMP)
    * DB_NAME (e.g., debugmyday_db)

### 4. Launch the Application

1.  Open your web browser and navigate to the project URL:
    
    http://localhost/DebugMyDay/
    
2.  You should be directed to the *Hero Page* (Login/Registration), where you can start using the application.
