<?php
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$db = 'ambulance_tracker';
$user = 'your_db_user';
$pass = 'your_db_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Example: Adding a new ambulance location
        $data = json_decode(file_get_contents("php://input"), true);
        $lat = $data['lat'];
        $lng = $data['lng'];
        $details = $data['details'];

        $stmt = $pdo->prepare("INSERT INTO ambulance_locations (lat, lng, details) VALUES (?, ?, ?)");
        $stmt->execute([$lat, $lng, $details]);

        echo json_encode(['status' => 'success']);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Example: Fetching ambulance locations
        $stmt = $pdo->query("SELECT * FROM ambulance_locations");
        $ambulances = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($ambulances);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
