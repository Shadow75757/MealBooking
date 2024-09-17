<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Access denied. You must be an admin to view this page.");
}

include('connect_db.php');

$sql = "SELECT users.username, meals.name AS meal_name, meals.price, bookings.observation 
        FROM bookings 
        JOIN users ON bookings.user_id = users.id
        JOIN meals ON bookings.meal_id = meals.id";

$stmt = $conn->prepare($sql);
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Booked Meals</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="container">
        <h1>Booked Meals Overview</h1>
        
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Meal</th>
                    <th>Price</th>
                    <th>Observation</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (count($bookings) > 0) {
                    foreach ($bookings as $booking) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($booking['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($booking['meal_name']) . "</td>";
                        echo "<td>$" . htmlspecialchars($booking['price']) . "</td>";
                        echo "<td>" . htmlspecialchars($booking['observation']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No bookings found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
