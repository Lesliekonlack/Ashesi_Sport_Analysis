<?php
session_start();
include 'settings/connection.php';

// Fetch tournaments
$tournaments_sql = "SELECT * FROM tournaments";
$tournaments_stmt = $conn->prepare($tournaments_sql);
$tournaments_stmt->execute();
$tournaments = $tournaments_stmt->fetchAll(PDO::FETCH_ASSOC);

include 'settings/connection.php';

// Function to fetch scores for each match
function getMatchScores($conn, $matchID) {
    $sql = "SELECT e.TeamID, COUNT(*) as Goals
            FROM match_events e
            WHERE e.MatchID = :matchID AND e.EventType = 'goal'
            GROUP BY e.TeamID";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['matchID' => $matchID]);
    $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = ['team1' => 0, 'team2' => 0];
    foreach ($scores as $score) {
        if ($score['TeamID'] == 1) {
            $result['team1'] = $score['Goals'];
        } else {
            $result['team2'] = $score['Goals'];
        }
    }
    return $result;
}

// Fetch matches from the database
$sql = "SELECT m.MatchID, m.Date, m.Time, t1.TeamName as Team1Name, t2.TeamName as Team2Name, 
               s.SportName, tr.Name as TournamentName, m.HasEnded
        FROM matches m
        JOIN teams t1 ON m.Team1ID = t1.TeamID
        JOIN teams t2 ON m.Team2ID = t2.TeamID
        JOIN sports s ON m.SportID = s.SportID
        LEFT JOIN tournaments tr ON m.TournamentID = tr.TournamentID
        WHERE (m.HasEnded = 0 OR (m.HasEnded = 1 AND m.Date = CURDATE()))
        ORDER BY m.Date ASC, m.Time ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch five most recent stories
$stories_sql = "SELECT * FROM stories ORDER BY DatePosted DESC LIMIT 5";
$stories_stmt = $conn->prepare($stories_sql);
$stories_stmt->execute();
$recent_stories = $stories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to get image path, ensuring it exists
function getImagePath($imagePath) {
    $defaultImagePath = 'uploads/default_image.png'; // Replace with your default image path
    if ($imagePath && file_exists(__DIR__ . '/../../' . $imagePath)) {
        return '../../' . $imagePath;
    }
    return $defaultImagePath;
}

// Fetch upcoming awards
$awards_sql = "SELECT e.*, s.SportName FROM events e
               JOIN sports s ON e.SportID = s.SportID
               WHERE e.EventDate >= CURDATE()
               ORDER BY e.EventDate ASC";
$awards_stmt = $conn->prepare($awards_sql);
$awards_stmt->execute();
$upcoming_awards = $awards_stmt->fetchAll(PDO::FETCH_ASSOC);

$tournaments_sql = "SELECT t.Name, s.SportName, t.StartDate, t.EndDate FROM tournaments t 
                    JOIN sports s ON t.SportID = s.SportID
                    WHERE t.StartDate >= CURDATE()
                    ORDER BY t.StartDate ASC";
$tournaments_stmt = $conn->prepare($tournaments_sql);
$tournaments_stmt->execute();
$upcoming_tournaments = $tournaments_stmt->fetchAll(PDO::FETCH_ASSOC);

function checkMatchStatus($match) {
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');

    if ($match['HasEnded']) {
        return 'Ended';
    } elseif ($match['Date'] < $currentDate || ($match['Date'] == $currentDate && $match['Time'] <= $currentTime)) {
        return 'Ongoing';
    } else {
        return 'Upcoming';
    }
}


if (isset($_GET['matchID']) && isset($_GET['action'])) {
    $matchID = $_GET['matchID'];
    if ($_GET['action'] == 'getMatchEvents') {
        $events = getMatchEvents($conn, $matchID);
        echo json_encode($events);
        exit;
    } elseif ($_GET['action'] == 'getMatchDetails') {
        $details = getMatchDetails($conn, $matchID);
        echo json_encode($details);
        exit;
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ashesi Sports Insight</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const images = [
                'https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/80ce81b9f1d08f25148101e1b5579d0e00c86019/ashesifootimage.jpeg',
                'https://raw.githubusercontent.com/naomikonlack/WEBTECHGITDEMO/main/ashesifootball2.webp',
                'https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/80ce81b9f1d08f25148101e1b5579d0e00c86019/ashesifootball7.webp',
                'https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/8fb8611d9708745bc19df059ebc2b5b92c6755f3/BESTPLAYER.jpeg',
                'https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/073676d2c33b343e190969e7481dd97017ccf03e/BASK.jpeg',
                'https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/073676d2c33b343e190969e7481dd97017ccf03e/BASKETBALL1.jpeg'
            ];

            let currentIndex = 0;
            const heroImage = document.querySelector('.hero-image');
            const leftArrow = document.querySelector('.left-arrow');
            const rightArrow = document.querySelector('.right-arrow');

            const updateImage = () => {
                heroImage.src = images[currentIndex];
            };

            setInterval(() => {
                currentIndex = (currentIndex + 1) % images.length;
                updateImage();
            }, 10000); // Change image every 10 seconds

            leftArrow.addEventListener('click', () => {
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                updateImage();
            });

            rightArrow.addEventListener('click', () => {
                currentIndex = (currentIndex + 1) % images.length;
                updateImage();
            });

            const profileIcon = document.querySelector('.profile-icon');
            const searchIcon = document.querySelector('.search-icon');
            const loginModal = document.querySelector('#loginModal');
            const registerModal = document.querySelector('#registerModal');
            const searchModal = document.querySelector('#searchModal');
            const closeModalButtons = document.querySelectorAll('.close');
            const closeSearchButton = document.querySelector('.close-search');

            profileIcon?.addEventListener('click', () => {
                loginModal.style.display = 'block';
            });

            searchIcon.addEventListener('click', () => {
                searchModal.style.display = 'block';
                document.body.classList.add('blurred');
            });

            closeModalButtons.forEach(button => {
                button.addEventListener('click', () => {
                    loginModal.style.display = 'none';
                    registerModal.style.display = 'none';
                });
            });

            closeSearchButton.addEventListener('click', () => {
                searchModal.style.display = 'none';
                document.body.classList.remove('blurred');
            });

            window.addEventListener('click', (event) => {
                if (event.target == loginModal) {
                    loginModal.style.display = 'none';
                } else if (event.target == registerModal) {
                    registerModal.style.display = 'none';
                } else if (event.target == searchModal) {
                    searchModal.style.display = 'none';
                    document.body.classList.remove('blurred');
                }
            });

            const upcomingAwards = document.querySelector('.upcoming-awards');
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        document.querySelectorAll('.petal').forEach(petal => {
                            petal.classList.add('animate');
                        });
                    }
                });
            });

            observer.observe(upcomingAwards);

            // Carousel functionality
            const carousel = document.querySelector('.carousel-track');
            const carouselItems = document.querySelectorAll('.match');
            const prevButton = document.querySelector('.carousel-prev');
            const nextButton = document.querySelector('.carousel-next');
            let scrollPosition = 0;
            const itemWidth = carouselItems[0].offsetWidth + 20; // Width of a match item + gap

            prevButton.addEventListener('click', () => {
                scrollPosition = Math.max(scrollPosition - itemWidth, 0);
                carousel.scrollTo({ left: scrollPosition, behavior: 'smooth' });
            });

            nextButton.addEventListener('click', () => {
                scrollPosition = Math.min(scrollPosition + itemWidth, (carouselItems.length - 1) * itemWidth);
                carousel.scrollTo({ left: scrollPosition, behavior: 'smooth' });
            });

            // Update scores dynamically
            const updateScores = () => {
                carouselItems.forEach(item => {
                    const matchID = item.getAttribute('data-match-id');
                    fetch(?matchID=${matchID}&action=getMatchEvents)
                        .then(response => response.json())
                        .then(data => {
                            const scoreElement = item.querySelector('.score');
                            const team1Goals = data[0]?.Goals || 0;
                            const team2Goals = data[1]?.Goals || 0;
                            scoreElement.textContent = ${team1Goals} : ${team2Goals};

                            const statusElement = item.querySelector('.status');
                            const matchDate = item.getAttribute('data-match-date');
                            const matchTime = item.getAttribute('data-match-time');
                            const matchDateTime = new Date(${matchDate}T${matchTime});
                            const now = new Date();
                            const timeDifference = Math.floor((now - matchDateTime) / 1000 / 60); // in minutes

                            if (item.getAttribute('data-has-ended') == '1') {
                                statusElement.textContent = 'Ended';
                            } else if (timeDifference >= 140) {
                                statusElement.textContent = 'Ended';
                            } else if (timeDifference >= 0 && timeDifference < 140) {
                                statusElement.textContent = 'On-going';
                            } else {
                                statusElement.textContent = 'Upcoming';
                            }
                        });
                });
            };

            updateScores();

            // Show match details in a modal
            carouselItems.forEach(item => {
                item.addEventListener('click', () => {
                    const matchID = item.getAttribute('data-match-id');
                    fetch(?matchID=${matchID}&action=getMatchDetails)
                        .then(response => response.json())
                        .then(data => {
                            const modalContent = document.querySelector('#matchDetailContent');
                            modalContent.innerHTML = data.map(event => 
                                <p>${event.EventTime} - ${event.PlayerName} (${event.EventType})</p>
                            ).join('');
                            document.querySelector('#matchDetailModal').style.display = 'block';
                        });
                });
            });

            const closeMatchDetail = document.querySelector('.close-match-detail');
            closeMatchDetail.addEventListener('click', () => {
                document.querySelector('#matchDetailModal').style.display = 'none';
            });

            window.addEventListener('click', (event) => {
                if (event.target == document.querySelector('#matchDetailModal')) {
                    document.querySelector('#matchDetailModal').style.display = 'none';
                }
            });
        });

        function openRegisterModal() {
            document.getElementById('loginModal').style.display = 'none';
            document.getElementById('registerModal').style.display = 'block';
        }

        function openLoginModal() {
            document.getElementById('registerModal').style.display = 'none';
            document.getElementById('loginModal').style.display = 'block';
        }

        function toggleDropdown() {
            document.getElementById("profileDropdown").classList.toggle("show");
        }

        window.onclick = function(event) {
            if (!event.target.matches('.profile-name')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                var i;
                for (i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: white;
            padding-top: 80px; /* Space for the fixed header */
        }

        .header-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: white;
            padding: 5px 10px;
            height: 80px;
            border-radius: 1px;
            z-index: 1000;
            width: 100%;
            box-sizing: border-box;
        }

        .logo-container {
            height: 100%;
            display: flex;
            align-items: center;
            margin-left: 20px;
        }

        .site-title {
            font-size: 24px;
            color: #4B0000;
            margin-right: 20px;
        }

        .logo {
            height: 100%;
            margin-right: 10px;
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 10px;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            position: relative;
            display: inline-block;
        }

        nav ul li a {
            color: #4B0000;
            text-decoration: none;
            font-weight: bold;
            line-height: 20px;
            padding: 0 15px;
        }

        nav ul li:hover > ul {
            display: block;
        }

        nav ul ul {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 1000;
        }

        nav ul ul li {
            display: block;
            text-align: left;
        }

        nav ul ul li a {
            color: #4B0000;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        nav ul ul li a:hover {
            background-color: #ddd;
        }

        .nav-icons img {
            height: 28px;
            cursor: pointer;
        }

        .nav-icons {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-icon {
            height: 24px;
            cursor: pointer;
        }

        .profile-name {
            font-size: 1rem;
            color: #4B0000;
            font-weight: bold;
            cursor: pointer;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }

        .dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {background-color: #ddd;}

        .show {display: block;}

        .hero {
            display: flex;
            height: 60vh;
            overflow: hidden;
            position: relative;
            margin-bottom: 20px;
            width: 100vw;
        }

        .hero-text-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: #4B0000;
            padding: 20px;
            color: white;
        }

        .hero-text-container h1 {
            font-size: 2rem;
        }

        .hero-text-container h2 {
            font-size: 1.2rem;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .cta-button {
            display: inline-block;
            padding: 8px 15px;
            background-color: #1e90ff;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            margin-top: 10px;
        }

        .hero-image-container {
            flex: 1;
            position: relative;
        }

        .hero-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(255, 255, 255, 0.5); /* Transparent white */
            color: white;
            border: none;
            font-size: 1.5rem; /* Adjust font size for thinner appearance */
            line-height: 1; /* Adjust line height for better alignment */
            cursor: pointer;
            z-index: 3;
            padding: 10px; /* Adjust padding as needed */
            border-radius: 50%; /* Make it circular */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .left-arrow {
            left: 10px;
        }

        .right-arrow {
            right: 10px;
        }

        .follow-olympics {
            padding: 60px;
            border-radius: 5px;
            margin-bottom: 30px;
            margin-top: -50px; 
        }

        .follow-olympics h2 {
            color: #800000;
            text-align: center;
        }

        .carousel-container {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .carousel-track {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            scroll-behavior: smooth;
        }

        .match {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            min-width: 200px;
            transition: transform 0.3s;
            flex: 0 0 auto; /* Ensure items don't shrink */
        }

        .match p {
            margin: 10px 0;
            color: #333;
        }

        .score {
            font-weight: bold;
            font-size: 1.2rem;
        }

        .status {
            font-size: 1rem;
            color: #800000;
        }

        .carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: #800000;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .carousel-prev {
            left: -50px; /* Adjust as needed to avoid overlap */
        }

        .carousel-next {
            right: -50px; /* Adjust as needed to avoid overlap */
        }

        .top-stories {
            padding: 90px;
            background-color: #fff;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .top-stories-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            margin-bottom: 20px;
        }

        .top-stories h2 {
            margin: 0;
            color: #800000;
            text-align: left;
            margin-top:-100px;
        }

        .see-more {
            text-decoration: none;
            font-weight: bold;
            color: #1e90ff;
            transition: color 0.3s ease;
            margin-left:1450px;
        }

        .see-more:hover {
            color: #0056b3;
        }

        .stories-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: space-between;
        }

        .story {
            background: white;
            padding: 10px;
            border-radius: 5px;
        }

        .story img {
            width: 100%;
            height: 200px;
            border-radius: 5px;
            object-fit: cover;
        }

        .main-story {
            flex: 1 1 60%;
        }

        .main-story img {
            height: 450px; 
            width: 90%;
        }

        .side-stories {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            flex: 1 1 35%;
            margin-left:-100px;
        }

        .side-story {
            flex: 1 1 45%;
        }

        /* Upcoming Awards Events Section */
        .upcoming-awards {
            padding: 90px;
            background-color: black;
            border-radius: 5px;
            margin-bottom: 20px;
            position: relative;
        }

        .upcoming-awards h2 {
            margin: 0;
            color: white;
            text-align: left;
            margin-bottom: 20px;
        }

        .awards-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .award-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 400px;
            display: flex;
            flex-direction: column;
        }

        .award-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .award-image {
            background-size: cover;
            background-position: center;
            height: 500px;
        }

        .award-info {
            padding: 15px;
            text-align: center;
        }

        .award-info h3 {
            margin: 0;
            color:  #666;
        }

        .award-info p {
            margin: 5px 0;
            color: #666;
        }

        .rankings-section {
            padding: 60px;
            border-radius: 5px;
            background-color: #f0f0f0; /* Lighter background for better contrast */
            margin-bottom: 50px; /* Reduce margin for a tighter look */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Add a subtle shadow for depth */
            margin-bottom:150px;
            margin-top:100px;
        }

        .rankings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .rankings-header h2 {
            margin: 0;
            color: #4B0000; /* Ashesi Sports Insight primary color */
            font-size: 2rem; /* Increase font size for better visibility */
        }

        .rankings-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap; /* Allow items to wrap for better responsiveness */
        }

        .rankings-container .rankings {
            flex: 1;
            background: linear-gradient(135deg, #4B0000, #388E3C); /* Original gradient with Ashesi maroon */
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Consistent shadow with other elements */
            color: white; /* White text for good contrast */
        }

        .rankings h3 {
            color: white;
            margin-bottom: 10px;
            font-size: 1.5rem; /* Increase font size */
        }

        .rankings ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .rankings ul li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3); /* Lighter line for better visibility */
        }

        .rankings ul li span {
            color: #f0f0f0; /* Lighten text color for contrast */
        }

        .rankings ul li img {
            width: 40px; /* Slightly increase image size */
            height: auto;
            margin-right: 10px;
        }

        footer {
            background-color: #4B0000;
            color: white;
            text-align: center;
            padding: 10px 0;
            border-radius: 5px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 10px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            border-radius: 5px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-header,
        .modal-footer {
            padding: 10px;
            color: white;
            background-color: #4B0000;
            border-bottom: 1px solid #ddd;
        }

        .modal-header h2,
        .modal-footer h3 {
            margin: 0;
        }

        .modal-body {
            padding: 10px 20px;
        }

        .modal-body input[type="text"],
        .modal-body input[type="password"],
        .modal-body input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0 10px 0;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .modal-body button {
            background-color: #1e90ff;
            color: white;
            padding: 14px 20px;
            margin: 8px 0;
            border: none;
            cursor: pointer;
            width: 100%;
            border-radius: 5px;
        }

        .modal-body button:hover {
            background-color: #0056b3;
        }

        .modal-body .social-login {
            display: flex;
            justify-content: space-between;
        }

        .modal-body .social-login button {
            width: 48%;
            background-color: #3b5998; /* Facebook color */
            color: white;
        }

        .modal-body .social-login button.google {
            background-color: #db4a39; /* Google color */
        }

        .modal-body .social-login button:hover {
            opacity: 0.8;
        }

        .modal-body label {
            display: flex;
            align-items: center;
        }

        .modal-body input[type="checkbox"] {
            margin-right: 10px;
        }

        .modal-footer a {
            color: #5e95df;
            text-decoration: none;
        }

        .modal-footer a:hover {
            text-decoration: underline;
        }

        /* Updated Search Modal Styles */
        .search-modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            backdrop-filter: blur(5px); /* This will create the blur effect */
            justify-content: center;
            align-items: center;
        }

        .search-container {
            background-color: white;
            padding: 27px;
            border-radius: 1px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .search-container p {
            margin: 0;
            padding-bottom: 10px;
            font-size: 16px;
            color: #333;
        }

        .search-container .search-input-wrapper {
            position: relative;
            width: 10%;
        }

        .search-container input[type="text"] {
            width: 60%;
            padding: 10px;
            border: none;
            border-bottom: 3px solid #4B0000;
            outline: none;
            font-size: 35px;
            box-sizing: border-box;
        }

        .search-container input[type="text"]::placeholder {
            color: #aaa;
        }

        .search-container .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            height: 24px;
        }

        .close-search {
            position: absolute;
            top: 10px;
            right: 80px;
            font-size: 28px;
            color: #aaa;
            cursor: pointer;
        }

        .close-search:hover,
        .close-search:focus {
            color: black;
        }

        /* Upcoming Tournaments Section */
        .upcoming-tournaments {
            display: flex;
            padding: 90px;
            background-color: #f9f9f9;
            border-radius: 5px;
            margin-bottom: 20px;
            justify-content: space-between;
        }

        .upcoming-tournaments .tournaments-info {
            flex: 1;
        }

        .upcoming-tournaments .tournaments-info h2 {
            margin: 0;
            color: #800000;
            text-align: left;
            margin-bottom: 20px;
        }

        .tournaments-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;

        }
        

        .tournament-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            width: 300px;
            display: flex;
            flex-direction: column;
        }

        .tournament-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .tournament-image {
            background-size: cover;
            background-position: center;
            height: 200px;
        }

        .tournament-info {
            padding: 15px;
            text-align: center;
        }

        .tournament-info h3 {
            margin: 0;
            color: #4B0000;
        }

        .tournament-info p {
            margin: 5px 0;
            color: #666;
        }

        .tournament-image-container {
            flex: 1;
            position: relative;
        }

        .tournament-image-container img {
            width: 30%;
            height: 100%;
            object-fit: cover;
            margin-left:500px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/246c29d2a7c8bff15a8f6206d9f7084c6018fa5a/Untitled_Artwork%204.png" alt="Ashesi Sports Insight Logo" class="logo">
                <div class="site-title">Ashesi Sports Insight</div>
            </div>
            <nav>
                <ul>
                    <li>
                        <a href="#">SPORTS</a>
                        <ul>
                             <li><a href="view/pages/footballsport.php">Football</a></li>
                            <li><a href="basketballsport.php">Basketball</a></li>
                        </ul>
                    </li>
                    <li><a href="#">NEWS</a></li>
                    <li><a href="#">RANKINGS</a></li>
                    <li>
                        <a href="#">TEAMS & COACHES</a>
                        <ul>
                            <li><a href="#">Teams</a></li>
                            <li><a href="#">Coaches</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#">PLAYER STATS</a>
                        <ul>
                            <li><a href="#">Statistics</a></li>
                            <li><a href="#">Accomplishments</a></li>
                        </ul>
                    </li>
                    <li><a href="#">UPCOMING EVENTS</a></li>
                </ul>
            </nav>
            <div class="nav-icons">
                <img src="https://cdn-icons-png.flaticon.com/512/54/54481.png" alt="Search Icon" class="search-icon">
                <?php if (isset($_SESSION['coach_name'])): ?>
                    <div class="profile-dropdown">
                        <span class="profile-name" onclick="toggleDropdown()"><?php echo htmlspecialchars($_SESSION['coach_name']); ?></span>
                        <div id="profileDropdown" class="dropdown-content">
                            <a href="action/logout.php">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <img src="https://cdn-icons-png.flaticon.com/512/1077/1077114.png" alt="Profile Icon" class="profile-icon">
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main>
        <section class="hero">
            <div class="hero-text-container">
                <h1>Experience Ashesi's sports</h1>
                <h2>Get all the latest news on football & basketball, teams, coaches, players statistics, matches and any upcoming sports events</h2>
                <h1>Are you interested in sport but don't know where to start?</h1>
                <p>Register interest in one of the sports we do at Ashesi</p>
                <a href="#" class="cta-button">FIND YOUR SPORT AND CLUB TODAY</a>
            </div>
            <div class="hero-image-container">
                <div class="hero-overlay"></div>
                <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/80ce81b9f1d08f25148101e1b5579d0e00c86019/ashesifootimage.jpeg" alt="Ashesi Sports Image" class="hero-image">
                <button class="arrow left-arrow">&#9664;</button>
                <button class="arrow right-arrow">&#9654;</button>
            </div>
        </section>
        
        <section class="follow-olympics">
            <h2>MATCHES</h2>
            <div class="carousel-container">
                <button class="carousel-arrow carousel-prev">&#9664;</button>
                <div class="carousel-track">
    <?php foreach ($matches as $match): ?>
    <?php $scores = getMatchScores($conn, $match['MatchID']); ?>
    <div class="match" data-match-id="<?php echo $match['MatchID']; ?>" data-match-date="<?php echo $match['Date']; ?>" data-match-time="<?php echo $match['Time']; ?>" data-has-ended="<?php echo $match['HasEnded']; ?>">
        <p><?php echo htmlspecialchars($match['Date']); ?> - <?php echo htmlspecialchars($match['Time']); ?></p>
        <h3><?php echo htmlspecialchars($match['Team1Name']); ?> vs <?php echo htmlspecialchars($match['Team2Name']); ?></h3>
        <?php if (!empty($match['TournamentName'])): ?>
            <p><?php echo htmlspecialchars($match['TournamentName']); ?></p>
        <?php else: ?>
            <p>Friendly Match</p>
        <?php endif; ?>
        <div class="score"><?php echo htmlspecialchars($scores['team1']); ?> : <?php echo htmlspecialchars($scores['team2']); ?></div>
        <div class="status">
            <?php echo checkMatchStatus($match); ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

                <button class="carousel-arrow carousel-next">&#9654;</button>
            </div>
        </section>
       <!-- Top Stories Section -->
       <section class="top-stories">
            <h2>TOP STORIES</h2>
            <a href="view/pages/all_stories.php" class="see-more">See More</a> <!-- Link to the page showing all stories -->
            <div class="stories-container">
                <?php if (!empty($recent_stories)): ?>
                    <div class="story main-story">
                        <a href="view/pages/story_details.php?story_id=<?php echo htmlspecialchars($recent_stories[0]['StoryID']); ?>">
                            <img src="<?php echo htmlspecialchars(getImagePath($recent_stories[0]['ImagePath'])); ?>" alt="<?php echo htmlspecialchars($recent_stories[0]['Title']); ?>">
                            <h3><?php echo htmlspecialchars($recent_stories[0]['Title']); ?></h3>
                        </a>
                    </div>
                    <div class="side-stories">
                        <?php for ($i = 1; $i < count($recent_stories); $i++): ?>
                            <div class="story side-story">
                                <a href="view/pages/story_details.php?story_id=<?php echo htmlspecialchars($recent_stories[$i]['StoryID']); ?>">
                                    <img src="<?php echo htmlspecialchars(getImagePath($recent_stories[$i]['ImagePath'])); ?>" alt="<?php echo htmlspecialchars($recent_stories[$i]['Title']); ?>">
                                    <h3><?php echo htmlspecialchars($recent_stories[$i]['Title']); ?></h3>
                                </a>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php else: ?>
                    <p>No stories available at the moment.</p>
                <?php endif; ?>
            </div>
        </section>


<section class="upcoming-awards">
            <h2>UPCOMING EVENTS</h2>
            <div class="awards-container">
                <?php if (!empty($upcoming_awards)): ?>
                    <?php foreach ($upcoming_awards as $award): ?>
                        <div class="award-card">
                            <div class="award-image" style="background-image: url('../../<?php echo htmlspecialchars($award['EventFlyerImage']); ?>');"></div>
                            <div class="award-info">
                                <h3><?php echo htmlspecialchars($award['EventName']); ?></h3>
                                <p><?php echo htmlspecialchars($award['EventDate']); ?></p>
                                <p><?php echo htmlspecialchars($award['Location']); ?></p>
                                <p><?php echo htmlspecialchars($award['EventType']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No upcoming awards at the moment.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="upcoming-tournaments">
        <div class="tournaments-info">
            <h2>UPCOMING TOURNAMENTS</h2>
            <div class="tournaments-container">
                <?php if (!empty($upcoming_tournaments)): ?>
                    <?php foreach ($upcoming_tournaments as $tournament): ?>
                        <div class="tournament-card">
                            <div class="tournament-image" style="background-image: url('https://rawcdn.githack.com/Lesliekonlack/IgemImages/b6fa87c123e32e5cde47ff412c59e74d6216615f/pngtree-champion-tournament-logo-design-concept-vector-illustration-png-image_8152179.png.jpeg');"></div>
                            <div class="tournament-info">
                                <h3><?php echo htmlspecialchars($tournament['Name']); ?></h3>
                                <p><?php echo htmlspecialchars($tournament['SportName']); ?></p>
                                <p><?php echo htmlspecialchars($tournament['StartDate']); ?> - <?php echo htmlspecialchars($tournament['EndDate']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No upcoming tournaments at the moment.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="tournament-image-container">
            <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/90a505395a43cc861a9679f065dfd1398349a1bb/Image.heic" alt="Tournament Image">
        </div>
    </section>


        

    </main>
    <footer>
        <div class="footer-container">
            <p>&copy; 2024 Ashesi Sports Insight. All rights reserved.</p>
        </div>
    </footer>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2>Ashesi Sports Insight Login</h2>
            </div>

            <div class="modal-body">
                <form action="action/login_action.php" method="POST">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="PassKey" required>
                    <button type="submit">LOGIN</button>
                </form>
            </div>
            <div class="modal-footer">
                <h5 style= "text-align: center;">Sport admins only </h5>
            </div>
        </div>
    </div>



    <!-- Search Modal -->
    <div id="searchModal" class="search-modal">
        <div class="search-container">
            <span class="close-search">&times;</span>
            <p style= "margin-left:-850px;" >What team are you looking for?</p>
            <img style= "margin-left:330px; margin-top:15px;" src="https://cdn-icons-png.flaticon.com/512/54/54481.png" alt="Search Icon" class="search-icon">
            <input type="text" placeholder="Type the team name here">
        </div>
    </div>

    <!-- Match Detail Modal -->
    <div id="matchDetailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close close-match-detail">&times;</span>
                <h2>Match Details</h2>
            </div>
            <div class="modal-body" id="matchDetailContent">
                <!-- Match details will be loaded here -->
            </div>
        </div>
    </div> 
</body>
</html>
