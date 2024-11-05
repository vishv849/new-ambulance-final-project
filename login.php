<?php
session_start(); // Start session management

$servername = "localhost";
$username_db = "root"; // Default XAMPP MySQL username
$password_db = ""; // Default XAMPP MySQL password
$dbname = "user_db";

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
                header('Location: index.php'); // Redirect to the main page
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
            header('Location: index.php'); // Redirect to the main page
            exit();
        } else {
            echo "<script>alert('Username already exists');</script>";
        }
    } else {
        echo "<script>alert('Please fill all fields');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Signup</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <section>
        <div class="leaves"></div>
        <img src="bg.jpg" class="bg">
        <img src="girl.png" class="girl">
        <img src="trees.png" class="trees">

        <div class="login">
            <h2>Sign In</h2>
            <form method="POST">
                <div class="inputBox">
                    <input type="text" placeholder="Username" name="username" required>
                </div>
                <div class="inputBox">
                    <input type="password" placeholder="Password" name="password" required>
                </div>
                <div class="inputBox">
                    <input type="submit" value="Login" name="login">
                </div>
                <div class="group">
                    <a href="#" id="forgot-password">Forgot Password</a>
                    <input type="submit" value="Signup" name="signup">
                </div>
            </form>
        </div>

        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']): ?>
            <div class="welcome">
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                <form method="POST" style="display: inline;">
                    <input type="submit" value="Logout" name="logout">
                </form>
            </div>
        <?php endif; ?>

    </section>

    <?php
    // Handle Logout
    if (isset($_POST['logout'])) {
        session_start();
        session_destroy(); // Destroy the session if logging out
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect to self to refresh
        exit();
    }
    ?>
</body>
</html>
