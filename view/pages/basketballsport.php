<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basketball - Ashesi Sports Insight</title>
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

        .sidebar {
            width: 200px;
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

        .toggle-buttons {
            margin-bottom: 20px;
        }

        .toggle-buttons button {
            padding: 10px 20px;
            margin-right: 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: white;
        }

        #male-button.active, #female-button.active {
            background-color: #4B0000;
        }

        #male-button, #female-button {
            background-color: #ccc;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 100px;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            padding: 20px;
            margin-bottom: 20px;
            width: calc(140.333% - 10px); /* Responsive card width */
            box-sizing: border-box;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card img {
            width: 100px;
            height: 100px;
            margin-bottom: 10px;
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

        .upload-logo {
            background-color: #1e90ff;
            color: white;
            border: none;
            padding: 8px 16px;
            margin-top: 10px;
            cursor: pointer;
            border-radius: 5px;
            display: inline-block;
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
            margin-top: auto; /* Push footer to the bottom */
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 10px;
        }
    </style>
    <script>
        function toggleView(view) {
            document.getElementById('male-clubs').style.display = view === 'male' ? 'flex' : 'none';
            document.getElementById('female-clubs').style.display = view === 'female' ? 'flex' : 'none';

            document.getElementById('male-button').classList.toggle('active', view === 'male');
            document.getElementById('female-button').classList.toggle('active', view === 'female');
        }
    </script>
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
                            <li><a href="footballsport.php">Football</a></li>
                            <li><a href="basketballsport.php">Basketball</a></li>
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
        <h2>Basketball</h2>
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
            <h2>Welcome to the Basketball Insight of Ashesi</h2>
            <p>Discover all the latest updates, stats, teams, coaches, clubs, players, and competitions in Ashesi Basketball.</p>
        </section>

        <section id="clubs" class="section">
            <h2>Basketball Clubs at Ashesi</h2>
            <div class="toggle-buttons">
                <button id="male-button" onclick="toggleView('male')" class="active">Male</button>
                <button id="female-button" onclick="toggleView('female')">Female</button>
            </div>

            <div id="male-clubs" class="card-container">
                <a href="club.php?club_id=1">
                    <div class="card">
                        <img src="placeholder.png" alt="Default Logo" style="width:100px; height:100px;">
                        <h3>Ashesi Eagles</h3>
                        <p>Coached by John Doe</p>
                        <button class="upload-logo">Upload Logo</button>
                    </div>
                </a>
                <a href="club.php?club_id=2">
                    <div class="card">
                        <img src="placeholder.png" alt="Default Logo" style="width:100px; height:100px;">
                        <h3>Ashesi Falcons</h3>
                        <p>Coached by Jane Smith</p>
                        <button class="upload-logo">Upload Logo</button>
                    </div>
                </a>
                <!-- Add more male club cards here -->
            </div>

            <div id="female-clubs" class="card-container" style="display:none;">
                <a href="club.php?club_id=3">
                    <div class="card">
                        <img src="placeholder.png" alt="Default Logo" style="width:100px; height:100px;">
                        <h3>Ashesi Eagles (Women)</h3>
                        <p>Coached by Lisa Doe</p>
                        <button class="upload-logo">Upload Logo</button>
                    </div>
                </a>
                <a href="club.php?club_id=4">
                    <div class="card">
                        <img src="placeholder.png" alt="Default Logo" style="width:100px; height:100px;">
                        <h3>Ashesi Falcons (Women)</h3>
                        <p>Coached by Mary Smith</p>
                        <button class="upload-logo">Upload Logo</button>
                    </div>
                </a>
                <!-- Add more female club cards here -->
            </div>
        </section>
    </div>
    <footer>
        <div class="footer-container">
            <p>&copy; 2024 Ashesi Sports Insight. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>