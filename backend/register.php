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
    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Ensure both username and password fields are filled
        if (!empty($username) && !empty($password)) {
            // Check if the username already exists
            $check_sql = "SELECT * FROM users WHERE username = :username";
            $stmt = $conn->prepare($check_sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                // Hash the password for security
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert the new user into the database
                $insert_sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $hashed_password);

                if ($stmt->execute()) {
                    // Set the session with the registered user's username
                    $_SESSION['username'] = $username;

                    // Redirect to index.html after successful registration
                    echo "<script>alert('Registration successful!'); window.location.href = 'index.html';</script>";
                } else {
                    echo "<script>alert('Error during registration. Please try again.');</script>";
                }
            } else {
                echo "<script>alert('Username already exists. Please choose another.');</script>";
            }
        } else {
            echo "<script>alert('Please fill in both fields.');</script>";
        }
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <section>
        <div class="login">
            <h2>Sign Up</h2>
            <form method="POST">
                <div class="inputBox">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="inputBox">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="inputBox">
                    <input type="submit" value="Register">
                </div>
            </form>
        </div>
    </section>
</body>
</html>