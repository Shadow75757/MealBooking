<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Access denied. You must be an admin to view this page.");
}

include('../connect_db.php');

// Fetch bookings for admin - removing the "has_booked" condition for testing
$sql = "SELECT users.username, meals.name AS meal_name, meals.price, bookings.observation 
        FROM bookings 
        JOIN users ON bookings.user_id = users.id
        JOIN meals ON bookings.meal_id = meals.id";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->errorInfo()[2]);
}
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$bookings) {
    echo "Error fetching data: " . $stmt->errorInfo()[2];
    die();  // Prevent rendering of the page if there is a query error
}

// Fetch total number of users
$totalUsersSql = "SELECT COUNT(*) AS total_users FROM users";
$totalUsersStmt = $conn->prepare($totalUsersSql);
$totalUsersStmt->execute();
$totalUsers = $totalUsersStmt->fetch(PDO::FETCH_ASSOC)['total_users'];

// Fetch total number of bookings
$totalBookings = count($bookings);

// Fetch total bookings with observation
$observationsCount = 0;
foreach ($bookings as $booking) {
    if (!empty($booking['observation'])) {
        $observationsCount++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Booked Meals</title>
    <link rel="stylesheet" href="admin.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> <!-- For arrow icons -->
</head>
<body>
    <div class="container">
        <h1>Booked Meals Overview</h1>
        
        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="card">
                <h3>Total Users</h3>
                <p><?= $totalUsers ?></p>
            </div>
            <div class="card">
                <h3>Total Bookings</h3>
                <p><?= $totalBookings ?></p>
            </div>
            <div class="card">
                <h3>Bookings with Observations</h3>
                <p><?= $observationsCount ?></p>
            </div>
        </div>

        <!-- Table of bookings -->
        <table id="bookings-table">
            <thead>
                <tr>
                    <th onclick="sortTable(0)">Username <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(1)">Meal <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(2)">Price <i class="fas fa-sort"></i></th>
                    <th onclick="sortTable(3)">Observation <i class="fas fa-sort"></i></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (count($bookings) > 0) {
                    foreach ($bookings as $booking) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($booking['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($booking['meal_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($booking['price']) . "€</td>";
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
