<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Include the database connection
include('connect_db.php');

// Check if the required POST data is present
if (!isset($_POST['observation']) || !isset($_POST['meal_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit();
}

$user_id = $_SESSION['user_id'];
$meal_id = $_POST['meal_id'];
$observation = $_POST['observation'];

// Validate and sanitize input
$meal_id = filter_var($meal_id, FILTER_SANITIZE_NUMBER_INT);
$observation = filter_var($observation, FILTER_SANITIZE_STRING);

// Check if the meal is already booked
try {
    $stmt = $conn->prepare('SELECT * FROM bookings WHERE user_id = :user_id AND meal_id = :meal_id');
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':meal_id', $meal_id);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        // Update existing booking
        $stmt = $conn->prepare('UPDATE bookings SET observation = :observation, status = 1 WHERE user_id = :user_id AND meal_id = :meal_id');
        $stmt->bindParam(':observation', $observation);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':meal_id', $meal_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update booking']);
        }
    } else {
        // Insert new booking
        $stmt = $conn->prepare('INSERT INTO bookings (user_id, meal_id, observation, status) VALUES (:user_id, :meal_id, :observation, 1)');
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':meal_id', $meal_id);
        $stmt->bindParam(':observation', $observation);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Meal booked successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to book meal']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
