<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Details - Ashesi Sports Insight</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Basic CSS for styling */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: white;
            padding-top: 30px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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

        .sidebar {
            width: 200px;
            background-color: #4B0000;
            padding: 20px;
            height: 100vh;
            position: fixed;
            top: 80px;
            left: 0;
            overflow-y: auto;
        }

        .sidebar h2 {
            color: white;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 15px;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .sidebar ul li a:hover {
            text-decoration: underline;
        }

        .main-content {
            margin-left: 270px;
            padding: 20px;
            flex: 1;
        }

        .section {
            margin-bottom: 40px;
        }

        .section h2 {
            color: #4B0000;
            font-size: 2rem;
            margin-bottom: 20px;
            text-align: center;
        }

        .team-photo {
            background-color: #4B0000;
            background-image: url('https://www.transparenttextures.com/patterns/asfalt-dark.png'), linear-gradient(90deg, white 2px, transparent 2px), linear-gradient(white 2px, transparent 2px);
            background-size: cover, 50px 50px, 50px 50px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            display: inline-block;
            max-width: 58%;
            margin-left: 260px;
        }

        .team-photo img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .animation-container {
            position: relative;
            width: 98%;
            margin-left: -10px;
            height: 145vh;
            overflow: visible; /* Ensure content is not clipped */
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #88C057;
            background-image: url('https://www.transparenttextures.com/patterns/asfalt-dark.png'), linear-gradient(90deg, white 2px, transparent 2px), linear-gradient(white 2px, transparent 2px);
            background-size: cover, 50px 50px, 50px 50px;
        }

        .central-logo {
            position: absolute;
            width: 200px;
            height: 200px;
            z-index: 10;
            text-align: center;
            font-size: 1.5rem;
            color: #4B0000;
        }

        .orbiting-element {
            position: absolute;
            width: 310px;
            height: 280px;
            overflow: visible; /* Ensure content is not clipped */
            transform-origin: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            text-align: center;
            opacity: 0;
            transition: opacity 0.5s;
        }

        .orbiting-element img {
            width: 50%;
            height: auto;
            border-radius: 50%;
            margin-bottom: 10px;
            z-index: 2;
            position: relative;
        }

        .orbiting-element p.bottom {
            margin: 0;
            font-size: 1.5rem;
            color: #4B0000;
            text-align: center;
            background-color: rgba(75, 5, 0, 0.3);
            padding: 5px;
            border-radius: 5px;
            width: 50%;
            position: absolute;
            z-index: 1;
        }

        .orbiting-element p.top {
            margin: 0;
            font-size: 1.5rem;
            color: #4B0000;
            text-align: center;
            background-color: rgba(75, 5, 0, 0.3);
            padding: 5px;
            border-radius: 5px;
            width: 50%;
            position: absolute;
            z-index: 1;
        }

        .orbiting-element p.bottom {
            top: 68%;
        }

        .orbiting-element p.top {
            top: -100px; /* Adjust this value to move the text further up */
        }

        .orbiting-element img:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(255, 255, 0, 0.6), 0 10px 20px rgba(255, 255, 255, 0.5);
            filter: brightness(1.2);
        }

        .orbiting-element.player4 p.top,
        .orbiting-element.player7 p.top,
        .orbiting-element.player15 p.top,
        .orbiting-element.player18 p.top {
            top: -60px; /* Ensure the text is visible above the images */
        }

        .outer {
            transform-origin: 500px center;
        }

        .arrow {
            position: absolute;
            top: 50%;
            z-index: 100;
            cursor: pointer;
            font-size: 2rem;
            color: white;
            padding: 10px;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
        }

        .arrow.left {
            left: 10px;
        }

        .arrow.right {
            right: 10px;
        }

        .card-container1 {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            padding: 20px;
            margin-bottom: 20px;
            width: 200px;
            text-align: center;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .card h3 {
            margin: 0;
            color: #4B0000;
            font-size: 1.2rem;
        }

        .card p {
            color: #666;
            font-size: 0.9rem;
        }

        footer {
            background-color: #4B0000;
            color: white;
            text-align: center;
            padding: 10px 0;
            border-radius: 5px;
            position: static;
            bottom: 0;
            width: 100%;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 10px;
        }

        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5); /* Black background with opacity */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
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

        .player-info {
            text-align: center;
        }

        .player-info h2 {
            color: #4B0000;
            font-size: 2rem;
        }

        .player-info p {
            font-size: 1.2rem;
        }

        .see-more {
            color: #4B0000;
            font-weight: bold;
            cursor: pointer;
            text-decoration: underline;
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
                            <li><a href="#">Football</a></li>
                            <li><a href="#">Basketball</a></li>
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
                <img src="https://cdn-icons-png.flaticon.com/512/1077/1077114.png" alt="Profile Icon" class="profile-icon">
            </div>
        </div>
    </header>
    <div class="sidebar">
        <h2>Football</h2>
        <ul>
            <li><a href="#stats">Stats</a></li>
            <li><a href="#teams">Teams</a></li>
            <li><a href="#coaches">Coaches</a></li>
            <li><a href="#clubs">Clubs</a></li>
            <li><a href="#players">Players</a></li>
            <li><a href="#competitions">Competitions</a></li>
        </ul>
    </div>
    <div class="main-content">
        <section id="club-info" class="section">
            <h2>Club Name</h2>
            <div class="team-photo">
                <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/7ff1d189d740524e0a78d2ea604330e44b7ce4c5/1*0WhCt1wGPEBhjHi4_uZxHw.jpg" alt="Team Photo">
            </div>
            <h2>Players</h2>
            <div class="animation-container">
                <div class="arrow left" onclick="showPreviousSet()">&#8592;</div>
                <div class="central-logo">
                    <p>Team Name</p>
                </div>

                <!-- Inner Circle (Set 1) -->
                <div class="orbiting-element set1" data-player-id="player1" data-player-name="Player 1" data-player-position="Forward" data-player-description="Player 1's description" style="--i: 0;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 1">
                    <p class="bottom">Player 1<br>Forward</p>
                </div>
                <div class="orbiting-element set1" data-player-id="player2" data-player-name="Player 2" data-player-position="Midfielder" data-player-description="Player 2's description" style="--i: 1;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 2">
                    <p class="bottom">Player 2<br>Midfielder</p>
                </div>
                <div class="orbiting-element set1" data-player-id="player3" data-player-name="Player 3" data-player-position="Defender" data-player-description="Player 3's description" style="--i: 2;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 3">
                    <p class="bottom">Player 3<br>Defender</p>
                </div>
                <div class="orbiting-element set1 player4" data-player-id="player4" data-player-name="Player 4" data-player-position="Goalkeeper" data-player-description="Player 4's description" style="--i: 3;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 4">
                    <p class="top">Player 4<br>Goalkeeper</p>
                </div>

                <!-- Outer Circle -->
                <div class="orbiting-element set1 outer" data-player-id="player5" data-player-name="Player 5" data-player-position="Forward" data-player-description="Player 5's description" style="--i: 4;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 5">
                    <p class="bottom">Player 5<br>Forward</p>
                </div>
                <div class="orbiting-element set1 outer" data-player-id="player6" data-player-name="Player 6" data-player-position="Forward" data-player-description="Player 6's description" style="--i: 0;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 6">
                    <p class="bottom">Player 6<br>Forward</p>
                </div>
                <div class="orbiting-element set1 outer player7" data-player-id="player7" data-player-name="Player 7" data-player-position="Midfielder" data-player-description="Player 7's description" style="--i: 1;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 7">
                    <p class="top">Player 7<br>Midfielder</p>
                </div>
                <div class="orbiting-element set1 outer" data-player-id="player8" data-player-name="Player 8" data-player-position="Defender" data-player-description="Player 8's description" style="--i: 2;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 8">
                    <p class="bottom">Player 8<br>Defender</p>
                </div>
                <div class="orbiting-element set1 outer" data-player-id="player9" data-player-name="Player 9" data-player-position="Goalkeeper" data-player-description="Player 9's description" style="--i: 3;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 9">
                    <p class="bottom">Player 9<br>Goalkeeper</p>
                </div>
                <div class="orbiting-element set1 outer" data-player-id="player10" data-player-name="Player 10" data-player-position="Forward" data-player-description="Player 10's description" style="--i: 4;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 10">
                    <p class="bottom">Player 10<br>Forward</p>
                </div>
                <div class="orbiting-element set1 outer" data-player-id="player11" data-player-name="Player 11" data-player-position="Midfielder" data-player-description="Player 11's description" style="--i: 5;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 11">
                    <p class="bottom">Player 11<br>Midfielder</p>
                </div>

                <!-- Second set of orbiting elements (hidden initially) -->
                <!-- Inner Circle -->
                <div class="orbiting-element set2" data-player-id="player12" data-player-name="Player 12" data-player-position="Forward" data-player-description="Player 12's description" style="--i: 0;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 12">
                    <p class="bottom">Player 12<br>Forward</p>
                </div>
                <div class="orbiting-element set2" data-player-id="player13" data-player-name="Player 13" data-player-position="Midfielder" data-player-description="Player 13's description" style="--i: 1;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 13">
                    <p class="bottom">Player 13<br>Midfielder</p>
                </div>
                <div class="orbiting-element set2" data-player-id="player14" data-player-name="Player 14" data-player-position="Defender" data-player-description="Player 14's description" style="--i: 2;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 14">
                    <p class="bottom">Player 14<br>Defender</p>
                </div>
                <div class="orbiting-element set2 player15" data-player-id="player15" data-player-name="Player 15" data-player-position="Goalkeeper" data-player-description="Player 15's description" style="--i: 3;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 15">
                    <p class="top">Player 15<br>Goalkeeper</p>
                </div>

                <!-- Outer Circle -->
                <div class="orbiting-element set2 outer" data-player-id="player16" data-player-name="Player 16" data-player-position="Forward" data-player-description="Player 16's description" style="--i: 4;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 16">
                    <p class="bottom">Player 16<br>Forward</p>
                </div>
                <div class="orbiting-element set2 outer" data-player-id="player17" data-player-name="Player 17" data-player-position="Forward" data-player-description="Player 17's description" style="--i: 0;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 17">
                    <p class="bottom">Player 17<br>Forward</p>
                </div>
                <div class="orbiting-element set2 outer player18" data-player-id="player18" data-player-name="Player 18" data-player-position="Midfielder" data-player-description="Player 18's description" style="--i: 1;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 18">
                    <p class="top">Player 18<br>Midfielder</p>
                </div>
                <div class="orbiting-element set2 outer" data-player-id="player19" data-player-name="Player 19" data-player-position="Defender" data-player-description="Player 19's description" style="--i: 2;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 19">
                    <p class="bottom">Player 19<br>Defender</p>
                </div>
                <div class="orbiting-element set2 outer" data-player-id="player20" data-player-name="Player 20" data-player-position="Goalkeeper" data-player-description="Player 20's description" style="--i: 3;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 20">
                    <p class="bottom">Player 20<br>Goalkeeper</p>
                </div>
                <div class="orbiting-element set2 outer" data-player-id="player21" data-player-name="Player 21" data-player-position="Forward" data-player-description="Player 21's description" style="--i: 4;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 21">
                    <p class="bottom">Player 21<br>Forward</p>
                </div>
                <div class="orbiting-element set2 outer" data-player-id="player22" data-player-name="Player 22" data-player-position="Midfielder" data-player-description="Player 22's description" style="--i: 5;">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/f3860df6b8b5fb350f106739b94052947dc3ff85/WhatsApp%20Image%202024-07-25%20at%2021.51.24.jpeg" alt="Player 22">
                    <p class="bottom">Player 22<br>Midfielder</p>
                </div>

                <div class="arrow right" onclick="showNextSet()">&#8594;</div>
            </div>

            <h2>Coaches</h2>
            <div class="card-container1">
                <div class="card">
                    <img src="placeholder.png" alt="Coach Photo">
                    <h3>Coach Name</h3>
                    <p>Coach</p>
                </div>
                <!-- Add more coach cards here if needed -->
            </div>
        </section>
    </div>

    <!-- Modal for player details -->
    <div id="playerModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="player-info">
                <img id="modalPlayerImage" src="" alt="Player Image" style="width: 100%; max-width: 200px; margin-bottom: 15px; border-radius: 50%;">
                <h2 id="modalPlayerName">Player Name</h2>
                <p id="modalPlayerPosition">Position</p>
                <p id="modalPlayerDescription">Detailed description of the player, stats, history, etc.</p>
                <a id="modalSeeMore" class="see-more" href="#" target="_blank">See More</a>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <p>&copy; 2024 Ashesi Sports Insight. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const set1 = document.querySelectorAll('.orbiting-element.set1');
        const set2 = document.querySelectorAll('.orbiting-element.set2');
        let showingSet1 = true;

        function showNextSet() {
            if (showingSet1) {
                set1.forEach(el => {
                    el.style.opacity = 0;
                    el.style.pointerEvents = 'none'; // Disable interactions for hidden set
                });
                set2.forEach(el => {
                    el.style.opacity = 1;
                    el.style.pointerEvents = 'auto'; // Enable interactions for visible set
                });
                showingSet1 = false;
            }
        }

        function showPreviousSet() {
            if (!showingSet1) {
                set1.forEach(el => {
                    el.style.opacity = 1;
                    el.style.pointerEvents = 'auto';
                });
                set2.forEach(el => {
                    el.style.opacity = 0;
                    el.style.pointerEvents = 'none';
                });
                showingSet1 = true;
            }
        }

        window.addEventListener('scroll', function () {
            const scrollPosition = window.scrollY;
            const windowHeight = window.innerHeight;
            const animationContainer = document.querySelector('.animation-container');
            const containerTop = animationContainer.offsetTop;
            const containerHeight = animationContainer.offsetHeight;

            if (scrollPosition + windowHeight > containerTop && scrollPosition < containerTop + containerHeight) {
                const scrollFraction = (scrollPosition + windowHeight - containerTop) / (containerHeight + windowHeight);
                const innerElements1 = document.querySelectorAll('.orbiting-element.set1:not(.outer)');
                const outerElements1 = document.querySelectorAll('.orbiting-element.set1.outer');
                const innerElements2 = document.querySelectorAll('.orbiting-element.set2:not(.outer)');
                const outerElements2 = document.querySelectorAll('.orbiting-element.set2.outer');

                const numInnerElements1 = innerElements1.length;
                const numOuterElements1 = outerElements1.length;
                const numInnerElements2 = innerElements2.length;
                const numOuterElements2 = outerElements2.length;

                const angleStepInner1 = 360 / numInnerElements1;
                const angleStepOuter1 = 360 / numOuterElements1;
                const angleStepInner2 = 360 / numInnerElements2;
                const angleStepOuter2 = 360 / numOuterElements2;

                innerElements1.forEach((element, index) => {
                    const orbitRotation = scrollFraction * 360 + angleStepInner1 * index;
                    element.style.transform = `rotate(${orbitRotation}deg) translate(300px) rotate(-${orbitRotation}deg)`;
                });

                outerElements1.forEach((element, index) => {
                    const orbitRotation = scrollFraction * 360 + angleStepOuter1 * index;
                    element.style.transform = `rotate(${orbitRotation}deg) translate(500px) rotate(-${orbitRotation}deg)`;
                });

                innerElements2.forEach((element, index) => {
                    const orbitRotation = scrollFraction * 360 + angleStepInner2 * index;
                    element.style.transform = `rotate(${orbitRotation}deg) translate(300px) rotate(-${orbitRotation}deg)`;
                });

                outerElements2.forEach((element, index) => {
                    const orbitRotation = scrollFraction * 360 + angleStepOuter2 * index;
                    element.style.transform = `rotate(${orbitRotation}deg) translate(500px) rotate(-${orbitRotation}deg)`;
                });
            }
        });

        // Initialize the visibility of the elements
        set1.forEach(el => {
            el.style.opacity = 1;
            el.style.pointerEvents = 'auto';
        });
        set2.forEach(el => {
            el.style.opacity = 0;
            el.style.pointerEvents = 'none';
        });

        // Modal functionality
        const modal = document.getElementById("playerModal");
        const closeModal = document.querySelector(".modal .close");

        // Function to open modal with player info
        function openModal(playerName, playerPosition, playerDescription, playerImage, playerMoreLink) {
            document.getElementById("modalPlayerName").textContent = playerName;
            document.getElementById("modalPlayerPosition").textContent = playerPosition;
            document.getElementById("modalPlayerDescription").textContent = playerDescription;
            document.getElementById("modalPlayerImage").src = playerImage;
            document.getElementById("modalSeeMore").href = playerMoreLink;
            modal.style.display = "block";
        }

        // Close the modal
        closeModal.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Attach click event listeners to each player element
        document.querySelectorAll('.orbiting-element').forEach(player => {
            player.addEventListener('click', () => {
                const playerName = player.getAttribute('data-player-name');
                const playerPosition = player.getAttribute('data-player-position');
                const playerDescription = player.getAttribute('data-player-description');
                const playerImage = player.querySelector('img').src; // Assuming the image source is the same
                const playerId = player.getAttribute('data-player-id'); // Unique identifier for the player

                // Set the correct URL path for the "See More" link
                const playerMoreLink = `http://localhost/Ashesi_Sport_Analysis/view/pages/seemore.php?player=${playerId}`;

                if ((showingSet1 && player.classList.contains('set1')) || (!showingSet1 && player.classList.contains('set2'))) {
                    openModal(playerName, playerPosition, playerDescription, playerImage, playerMoreLink);
                }
            });
        });
    </script>
</body>
</html>
