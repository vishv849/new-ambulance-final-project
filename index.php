<?php
session_start(); // Start session management

$servername = "localhost";
$username_db = "root"; // Default XAMPP MySQL username
$password_db = ""; // Default XAMPP MySQL password
$dbname = "user_db"; // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle Login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $sql = "SELECT password FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['loggedin'] = true; // Set session variable
                $_SESSION['username'] = $username; // Store username in session
                header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the page
                exit();
            } else {
                echo "<script>alert('Invalid username or password');</script>";
            }
        } else {
            echo "<script>alert('Invalid username or password');</script>";
        }
    } else {
        echo "<script>alert('Please fill all fields');</script>";
    }
}

// Handle Signup
if (isset($_POST['signup'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        // Check if username already exists
        $check_sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows == 0) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hashing the password
            $signup_sql = "INSERT INTO users (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($signup_sql);
            $stmt->bind_param("ss", $username, $hashed_password);
            $stmt->execute();
            $_SESSION['loggedin'] = true; // Set session variable
            $_SESSION['username'] = $username; // Store username in session
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to refresh the page
            exit();
        } else {
            echo "<script>alert('Username already exists');</script>";
        }
    } else {
        echo "<script>alert('Please fill all fields');</script>";
    }
}

// Handle Logout
if (isset($_POST['logout'])) {
    session_destroy(); // Destroy the session if logging out
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to self to refresh
    exit();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambulance Tracker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        /* General Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        header {
            background-color: #0033ff;
            color: #fff;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            margin: 0 15px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
        }

        .auth-container button {
            margin-left: 10px;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            background-color: #0600b3;
            color: white;
            cursor: pointer;
        }

        /* Main Container */
        .container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        #map {
            flex: 2;
            height: 100%;
        }

        .info {
            flex: 1;
            padding: 20px;
            background-color: #ffffff;
            overflow-y: auto;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .controls {
            display: flex;
            justify-content: center;
            padding: 15px;
            background-color: #007bff;
            color: #fff;
        }

        .controls button {
            margin: 0 10px;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #0056b3;
            color: white;
            cursor: pointer;
        }

        /* Footer Styles */
        footer {
            background-color: #003cff;
            color: white;
            text-align: center;
            padding: 20px;
        }

        footer p {
            margin: 5px 0;
        }

        footer .social-icons {
            list-style: none;
            padding: 0;
        }

        footer .social-icons li {
            display: inline;
            margin: 0 10px;
        }

        footer .social-icons a {
            color: white;
            text-decoration: none;
        }

        /* Driver Interface Styles */
        .driver-info,
        .patient-info {
            display: none;
        }

        .driver-info.active,
        .patient-info.active {
            display: block;
        }

        .emergency-details,
        .profile-settings {
            margin-top: 30px;
        }

        /* Input Field Styles */
        .emergency-details input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }

        .emergency-details select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
    </style>
</head>

<body>

    <!-- Header Section -->
    <header>
        <nav>
            <ul>
                <li><a href="#" id="driver-mode">Driver</a></li>
                <li><a href="#" id="patient-mode">Patient</a></li>
            </ul>
        </nav>
        <div class="auth-container">
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']): ?>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="logout">Logout</button>
                </form>
            <?php else: ?>
                <button class="sign-in">Sign In</button>
                <button class="sign-up">Sign Up</button>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Container -->
    <div class="container">
        <div id="map"></div>
        <div class="info">
            <div class="controls">
                <i class="fas fa-home"></i>
            </div>
            <!-- Patient Section -->
            <div class="patient-info active">
                <h2>Patient Address</h2>
                <label for="patient-address"></label>
                <input type="text" id="patient-address" placeholder="Enter address" />
                <h2>Destination Hospital</h2>
                <label for="hospital-location"></label>
                <input type="text" id="hospital-location" placeholder="Enter hospital location" />
                <button id="update-location">Update Location</button>
                <div class="price-section">
                    <h2>DESTINATION</h2>
                    <p id="price"></p>
                </div>
            </div>

            <!-- Driver Section -->
            <div class="driver-info">
                <h2>Driver Details</h2>
                <label for="driver-name">Driver Name:</label>
                <input type="text" id="driver-name" placeholder="Enter driver name" />
                <label for="ambulance-number">Ambulance Number:</label>
                <input type="text" id="ambulance-number" placeholder="Enter ambulance number" />
                <label for="driver-phone">Driver Phone Number:</label>
                <input type="text" id="driver-phone" placeholder="Enter phone number" />
                <h2>Ambulance Details</h2>
                <textarea id="ambulance-details" rows="4" placeholder="Enter ambulance details"></textarea>
            </div>

            <!-- Emergency Details -->
            <div class="emergency-details">
                <h2>Emergency Details</h2>
                <h3>Emergency Type</h3>
                <select id="emergency-type">
                    <option value="heart-attack">Heart Attack</option>
                    <option value="stroke">Stroke</option>
                    <option value="accident">Accident</option>
                    <option value="other">Other</option>
                </select>
                <h3>Patient's Name</h3>
                <input type="text" id="patient-name" value="John Doe" />
            </div>

            <!-- Profile Settings -->
            <div class="profile-settings">
                <h3>Profile Settings</h3>
                <ul>
                    <li><a href="#"><i class="fas fa-user"></i> Edit Profile</a></li>
                    <li><a href="#"><i class="fas fa-cogs"></i> App Settings</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <footer>
        <p>&copy; 2024 Ambulance Tracker. All Rights Reserved.</p>
        <ul class="social-icons">
            <li><a href="#"><i class="fab fa-facebook"></i></a></li>
            <li><a href="#"><i class="fab fa-twitter"></i></a></li>
            <li><a href="#"><i class="fab fa-instagram"></i></a></li>
            <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
        </ul>
    </footer>

    <!-- External Libraries -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Initialize the map and set view to Sion, Mumbai
        var map = L.map('map').setView([19.0434, 72.8654], 13);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Event listeners for Sign In and Sign Up
        document.querySelector('.sign-in').addEventListener('click', function () {
            window.location.href = 'login.php'; // Redirect to login page
        });
        document.querySelector('.sign-up').addEventListener('click', function () {
            window.location.href = 'signup.php'; // Redirect to sign up page (same page for simplicity)
        });

        // Event listeners to switch between Driver and Patient interfaces
        document.getElementById('driver-mode').addEventListener('click', function () {
            document.querySelector('.driver-info').classList.add('active');
            document.querySelector('.patient-info').classList.remove('active');
        });

        document.getElementById('patient-mode').addEventListener('click', function () {
            document.querySelector('.driver-info').classList.remove('active');
            document.querySelector('.patient-info').classList.add('active');
        });
    </script>
</body>

</html>
