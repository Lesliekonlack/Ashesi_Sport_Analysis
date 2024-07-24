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

            profileIcon.addEventListener('click', () => {
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
        });

        function openRegisterModal() {
            document.getElementById('loginModal').style.display = 'none';
            document.getElementById('registerModal').style.display = 'block';
        }

        function openLoginModal() {
            document.getElementById('registerModal').style.display = 'none';
            document.getElementById('loginModal').style.display = 'block';
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
    background-color: black;
    color: white;
    border: none;
    font-size: 2rem;
    cursor: pointer;
    z-index: 3;
    padding: 10px;
    border-radius: 50%;
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

.olympics-container {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}

.match {
    background: white;
    padding: 10px;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    flex: 1;
    text-align: center;
    min-width: 200px;
}

.match p {
    margin: 10px 0;
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
    background-color: #f9f9f9;
    margin-bottom: 100px;
}

.rankings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.rankings-header h2 {
    margin: 0;
    color: #4B0000;
}

.rankings-container {
    display: flex;
    justify-content: space-between;
    gap: 20px;
}

.rankings-container .rankings {
    flex: 1;
    background-color: white;
    border-radius: 5px;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.rankings h3 {
    color: #4B0000;
    margin-bottom: 10px;
}

.rankings ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.rankings ul li {
    display: flex;
    justify-content: space-between;
    align-items: center; /* Center align the content */
    padding: 10px 0;
    border-bottom: 1px solid #ddd;
}

.rankings ul li span {
    color: #666;
}

.rankings ul li img {
    width: 30px; /* Adjust size as needed */
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
                             <li><a href="footballsport.php">Football</a></li>
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
            <h2>UPCOMING MATCHES</h2>
            <div class="olympics-container">
                <div class="match">
                    <p>Men's Olympic Football Tournament Paris 2024<br>First Stage - Group C<br>24 Jul 2024</p>
                    <h3>UZBEKISTAN vs SPAIN</h3>
                    <p>13:00</p>
                </div>
                <div class="match">
                    <p>Men's Olympic Football Tournament Paris 2024<br>First Stage - Group B<br>24 Jul 2024</p>
                    <h3>ARGENTINA vs MOROCCO</h3>
                    <p>13:00</p>
                </div>
                <div class="match">
                    <p>Men's Olympic Football Tournament Paris 2024<br>First Stage - Group A<br>24 Jul 2024</p>
                    <h3>GUINEA vs NEW ZEALAND</h3>
                    <p>15:00</p>
                </div>
            </div>
        </section>
        
        <section class="top-stories">
            <h2>TOP STORIES</h2>
            <a href="#" class="see-more">See More</a>
            <div class="stories-container">
                <div class="story main-story">
                    <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/80ce81b9f1d08f25148101e1b5579d0e00c86019/ashesifootimage.jpeg" alt="Story Image 1">
                    <h3>Norchoyev: Uzbekistan have a huge potential</h3>
                </div>
                <div class="side-stories">
                    <div class="story side-story">
                        <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/8fb8611d9708745bc19df059ebc2b5b92c6755f3/BESTPLAYER.jpeg" alt="Story Image 2">
                        <h3>Mexico entrust 2026 project to Aguirre and Marquez</h3>
                    </div>
                    <div class="story side-story">
                        <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/073676d2c33b343e190969e7481dd97017ccf03e/BASK.jpeg" alt="Story Image 3">
                        <h3>Hosts France headline Paris 2024 opening day</h3>
                    </div>
                    <div class="story side-story">
                        <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/80ce81b9f1d08f25148101e1b5579d0e00c86019/ashesifootimage.jpeg" alt="Story Image 4">
                        <h3>Spain stamp ticket to U-20 World Cup</h3>
                    </div>
                    <div class="story side-story">
                        <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/073676d2c33b343e190969e7481dd97017ccf03e/BASK.jpeg" alt="Story Image 5">
                        <h3>Bindon: New Zealand are set up for something special</h3>
                    </div>
                </div>
            </div>
        </section>

        <section class="upcoming-awards">
            <h2>UPCOMING AWARDS EVENTS</h2>
            <div class="awards-container">
                <div class="award-card">
                    <div class="award-image" style="background-image: url('https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/fde3eff8d625b4b66d47ecc04b3aaec1840d3413/Ustunnight.jpeg');"></div>
                    <div class="award-info">
                        <h3>Sport Event of the Year</h3>
                        <p>2 Aug 2024</p>
                        <p>Ashesi Hive</p>
                    </div>
                </div>
                <!-- Add more award cards here if needed -->
            </div>
            <!-- Petals for animation -->
            <div class="petal"></div>
            <div class="petal"></div>
            <div class="petal"></div>
            <div class="petal"></div>
            <div class="petal"></div>
        </section>

        <section class="upcoming-tournaments">
            <div class="tournaments-info">
                <h2>UPCOMING TOURNAMENTS</h2>
                <div class="tournaments-container">
                    <div class="tournament-card">
                        <div class="tournament-image" style="background-image: url('https://example.com/tournament1.jpg');"></div>
                        <div class="tournament-info">
                            <h3>Football Championship</h3>
                            <p>5 Aug 2024 - 15 Aug 2024</p>
                            <p>Ashesi Stadium</p>
                        </div>
                    </div>
                    <div class="tournament-card">
                        <div class="tournament-image" style="background-image: url('https://example.com/tournament2.jpg');"></div>
                        <div class="tournament-info">
                            <h3>Basketball Tournament</h3>
                            <p>20 Aug 2024 - 30 Aug 2024</p>
                            <p>Ashesi Sports Hall</p>
                        </div>
                    </div>
                    <!-- Add more tournament cards here if needed -->
                </div>
            </div>
            <div class="tournament-image-container">
                <img src="https://rawcdn.githack.com/naomikonlack/WEBTECHGITDEMO/90a505395a43cc861a9679f065dfd1398349a1bb/Image.heic" alt="Tournament Image">
            </div>
        </section>

        <section class="rankings-section">
            <div class="rankings-header">
                <h2>Current Clubs Rankings</h2>
            </div>
            <div class="rankings-container">
                <div class="rankings football-rankings">
                    <h3>Football</h3>
                    <ul>
                        <li>
                            <span>1</span> <img src="https://via.placeholder.com/30" alt="Eagles Logo"> Ashesi Eagles <span>1000 pts</span>
                        </li>
                        <li>
                            <span>2</span> <img src="https://via.placeholder.com/30" alt="Falcons Logo"> Ashesi Falcons <span>950 pts</span>
                        </li>
                        <li>
                            <span>3</span> <img src="https://via.placeholder.com/30" alt="Hawks Logo"> Ashesi Hawks <span>900 pts</span>
                        </li>
                        <!-- Add more football clubs here -->
                    </ul>
                </div>
                <div class="rankings basketball-rankings">
                    <h3>Basketball</h3>
                    <ul>
                        <li>
                            <span>1</span> <img src="https://via.placeholder.com/30" alt="Panthers Logo"> Ashesi Panthers <span>1000 pts</span>
                        </li>
                        <li>
                            <span>2</span> <img src="https://via.placeholder.com/30" alt="Tigers Logo"> Ashesi Tigers <span>950 pts</span>
                        </li>
                        <li>
                            <span>3</span> <img src="https://via.placeholder.com/30" alt="Lions Logo"> Ashesi Lions <span>900 pts</span>
                        </li>
                        <!-- Add more basketball clubs here -->
                    </ul>
                </div>
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
                <form action="#" method="POST">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit">LOGIN</button>
                    <label>
                        <input type="checkbox" checked="checked"> Remember me on this device
                    </label>
                </form>
                <div class="register-link">
                    <p>Don't have an account? <a href="javascript:void(0)" onclick="openRegisterModal()">Register here</a></p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#">Forgot your password or username?</a>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2>Ashesi Sports Insight Register</h2>
            </div>
            <div class="modal-body">
                <form action="#" method="POST">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    <button type="submit">REGISTER</button>
                    <label>
                        <input type="checkbox" required> I agree to the <a style="margin-left:5px;" href="#">Terms and Conditions</a>
                    </label>
                </form>
                <div class="login-link">
                    <p>Already have an account? <a href="javascript:void(0)" onclick="openLoginModal()">Login here</a></p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#">Need help?</a>
            </div>
        </div>
    </div>

  <!-- Search Modal -->
<div id="searchModal" class="search-modal">
    <div class="search-container">
    <span class="close-search">&times;</span>
    <p style= "margin-left:-850px;" >What are you looking for?</p>
    <img style= "margin-left:330px; margin-top:15px;" src="https://cdn-icons-png.flaticon.com/512/54/54481.png" alt="Search Icon" class="search-icon">
        <input type="text" placeholder="News, Players, Matches, etc">
    </div>
</div>

</body>
</html>
