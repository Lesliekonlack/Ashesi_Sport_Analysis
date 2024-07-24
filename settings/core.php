<?php
// Start the session
session_start();

// Include the database connection
require_once 'connection.php';

// Define some global constants
define('BASE_URL', 'http://localhost/Ashesi_Sport_Analysis/');

// Function to sanitize user input
function sanitize($data) {
    return htmlspecialchars(strip_tags($data));
}

// Example function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Example: Error reporting settings
error_reporting(E_ALL);
ini_set('display_errors', 1);

// // Add more global settings or functions as needed

// // Example core functionality
// function getSportEvents($conn) {
//     $stmt = $conn->prepare("SELECT * FROM sport_events");
//     $stmt->execute();
//     return $stmt->fetchAll();
// }

// // Fetch and display sport events
// if (isLoggedIn()) {
//     $sportEvents = getSportEvents($conn);
//     foreach ($sportEvents as $event) {
//         echo "Event: " . sanitize($event['name']) . " on " . sanitize($event['date']) . "<br>";
//     }
// } else {
//     echo "Please log in to see the sport events.";
// }
?>
