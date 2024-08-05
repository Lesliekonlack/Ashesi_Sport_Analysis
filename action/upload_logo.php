<?php
session_start();
include '../settings/connection.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['team_logo']) && isset($_SESSION['coach_id'])) {
    $coachId = $_SESSION['coach_id'];
    $teamId = $_POST['team_id'];
    $uploadDir = '/Applications/XAMPP/xamppfiles/htdocs/Ashesi_Sport_Analysis/uploads/';
    $imageFileType = strtolower(pathinfo($_FILES['team_logo']['name'], PATHINFO_EXTENSION));
    $targetFile = $uploadDir . $teamId . '.' . $imageFileType; // Save as team_id.extension

    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES['team_logo']['tmp_name']);
    if ($check === false) {
        die('File is not an image.');
    }

    // Check file size
    if ($_FILES['team_logo']['size'] > 5000000) {
        die('Sorry, your file is too large.');
    }

    // Allow certain file formats
    if ($imageFileType != 'jpg' && $imageFileType != 'jpeg' && $imageFileType != 'png' && $imageFileType != 'gif') {
        die('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');
    }

    // Remove old file if it exists
    if (file_exists($targetFile)) {
        unlink($targetFile);
    }

    // Debugging output to understand the issue
    echo "Temp file: " . $_FILES['team_logo']['tmp_name'] . "<br>";
    echo "Target file: " . $targetFile . "<br>";
    echo "File type: " . $imageFileType . "<br>";
    echo "File size: " . $_FILES['team_logo']['size'] . "<br>";
    echo "File error: " . $_FILES['team_logo']['error'] . "<br>";
    echo "Is writable: " . (is_writable($uploadDir) ? "Yes" : "No") . "<br>";

    // Move uploaded file to target location
    if (move_uploaded_file($_FILES['team_logo']['tmp_name'], $targetFile)) {
        // Update the team's logo path in the database
        $sql = "UPDATE teams SET Logo = ? WHERE TeamID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$targetFile, $teamId]);

        // Redirect to the football page after successful upload
        header('Location: ../view/pages/footballsport.php');
        exit();
    } else {
        $error = $_FILES['team_logo']['error'];
        die("Sorry, there was an error uploading your file. Error code: $error");
    }
} else {
    die('Invalid request.');
}

