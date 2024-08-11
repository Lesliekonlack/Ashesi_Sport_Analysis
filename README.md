
# Ashesi Sports Management System

## Overview

*Ashesi Sports Insight* is a web-based application designed to manage and display information related to sports teams at Ashesi University. The platform allows users to view team statistics, player details, upcoming matches, awards, and related stories. Coaches can manage their team profiles, including editing player details, adding or removing players, and updating images.

## Features

### General Features
- *Team Overview*: Displays detailed information about each sports team, including the team name, coach, and a photo of the team.
- *Player Management*: Coaches can add, edit, or delete players from their teams. Each player profile includes fields for name, position, age, height, nationality, and image.
- *Awards and Trophies*: Showcases awards and trophies earned by players in each team.
- *Team Stories*: Lists stories related to each team, which are managed through the application.
- *Upcoming Matches*: Displays a schedule of upcoming matches for the team.
- *Responsive Design*: The website is designed to be responsive and accessible across various devices.

### Coach-Specific Features
- *Login and Authentication*: Coaches can log in to the system, ensuring that only authorized personnel can modify team data.
- *Edit Team Profile*: Coaches can upload or remove the team photo and update the coach's photo.
- *Edit Player Details*: Coaches can update player information directly from the interface.
- *Add/Remove Players*: Easily manage the team roster by adding new players or removing existing ones.
- *Change Player Picture*: Coaches can change player pictures via a simple form.

### Technical Features
- *Database Management*: The system is built on a robust relational database to manage teams, players, matches, stories, and trophies.
- *File Uploads*: Coaches can upload images for players, coaches, and teams, with proper handling of file storage.
- *Security*: Basic security measures are in place, such as user session management to prevent unauthorized access.

## Technologies Used

- *Frontend*:
  - HTML5 & CSS3: Structure and design of the web pages.
  - JavaScript: Client-side scripting and interactive functionalities.
  - AJAX: For making asynchronous requests to handle form submissions without page reloads.
  
- *Backend*:
  - PHP: Server-side scripting to handle data processing, form submissions, and interaction with the database.
  - MySQL: Relational database management system for storing and retrieving data.

- *Additional Tools*:
  - Apache: Web server to host the application.
  - Git: Version control to manage changes to the project.

## Installation & Setup

### Prerequisites
- *XAMPP/MAMP/WAMP*: To set up a local server environment with PHP and MySQL.
- *Composer*: (Optional) For managing PHP dependencies.

### Steps to Install Locally
1. *Clone the Repository*:
   
   git clone https://github.com/your-repository/Ashesi-Sports-Insight.git
   
2. *Set Up Database*:
   - Create a new database in MySQL (e.g., ashesi_sports_insight).
   - Import the provided SQL dump file (database/ashesi_sports_insight.sql) to set up the necessary tables and initial data.
   
3. *Configure Database Connection*:
   - Update the connection.php file located in the settings directory with your database credentials.
     php
     $host = 'localhost';
     $db = 'ashesi_sports_insight';
     $user = 'root';
     $pass = '';
     $charset = 'utf8mb4';
     
   
4. *Start the Server*:
   - Launch your XAMPP/MAMP/WAMP server.
   - Place the project folder inside the htdocs directory.
   - Navigate to http://localhost/Ashesi-Sports-Insight/ in your web browser.

## Usage

- *Coach Login*: Navigate to the login page, enter your credentials to access team management features.
- *View Teams*: On the homepage, select a team to view its details, stories, players, and more.
- *Edit Team Info*: If logged in as a coach, you can edit team and player details directly from the team overview page.
- *Manage Players*: Add new players, edit existing players, or remove players as necessary.


## Contributing

Contributions are welcome! Please fork the repository and create a pull request with your changes. Ensure your code follows the project's coding standards and includes appropriate comments.

## License

This project is licensed under the MIT License. See the LICENSE file for details.
