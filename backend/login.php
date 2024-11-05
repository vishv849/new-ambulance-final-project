<?php
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

// Handle Login
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            header('Location: ambulance_tracker.html');
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
            $signup_sql = "INSERT INTO users (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($signup_sql);
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
            header('Location: ambulance_tracker.html');
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
                    <input type="text" placeholder="Username" name="username">
                </div>
                <div class="inputBox">
                    <input type="password" placeholder="Password" name="password">
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
    </section>
</body>

</html>
