<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Football - Ashesi Sports Insight</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: white;
            padding-top: 80px; /* Space for the fixed header */
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .section img{
            width: 40%;
            margin-top: 200px;
            border-radius: 10px;
            margin-left:350px;
        }

        .sidebar {
            width: 250px;
            background-color: #4B0000;
            padding: 20px;
            height: 100vh;
            position: fixed;
            top: 80px; /* Below the navbar */
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
        }

        .section img {
            width: 40%;
            margin-top: 200px;
            border-radius: 10px;
            margin-left: 350px;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            padding: 20px;
            margin-bottom: 20px;
            width: 200px; /* Make cards smaller */
            display: inline-block; /* Allow cards to be in a grid */
            vertical-align: top; /* Align top */
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card h3 {
            margin: 0;
            color: #4B0000;
            font-size: 1.2rem; /* Smaller font size */
        }

        .card p {
            color: #666;
            font-size: 0.9rem; /* Smaller font size */
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

        footer {
            background-color: #4B0000;
            color: white;
            text-align: center;
            padding: 10px 0;
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 10px;
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
                    <li> <a href="homepage.php">HOME</a></li>
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
        <section id="welcome" class="section">
            <h2>Welcome to the Football Insight of Ashesi</h2>
            <p>Discover all the latest updates, stats, teams, coaches, clubs, players, and competitions in Ashesi football.</p>
        </section>

        <section id="clubs" class="section">
            <h2>Football Clubs at Ashesi</h2>
            <a href="club.php?club_id=1">
                <div class="card">
                    <h3>Ashesi Eagles</h3>
                    <p>Coached by John Doe</p>
                </div>
            </a>
            <a href="club.php?club_id=2">
                <div class="card">
                    <h3>Ashesi Falcons</h3>
                    <p>Coached by Jane Smith</p>
                </div>

                <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/1f0af0e3b89d30a2d9f590b5c3d0291876c4edb4/url.png" alt="Football Image">
            </a>
            <!-- Add more club cards here if needed -->
        </section>
    </div>
    <footer>
        <div class="footer-container">
            <p>&copy; 2024 Ashesi Sports Insight. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
