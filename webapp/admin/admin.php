<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../');
    exit();
}

// Check if the user is an admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../');
    exit();
}

include('../connect_db.php');

// Handle sorting (Default sorting by 'username' and 'ASC' order)
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'username';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Fetch bookings for admin with sorting
$sql = "SELECT users.username, meals.name AS meal_name, meals.price, bookings.observation 
        FROM bookings 
        JOIN users ON bookings.user_id = users.id
        JOIN meals ON bookings.meal_id = meals.id
        ORDER BY $sortColumn $sortOrder";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->errorInfo()[2]);
}
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        th {
            cursor: pointer;
        }
        .sorted-asc .fa-circle-arrow-up,
        .sorted-desc .fa-circle-arrow-down {
            display: inline;
        }
        .fa-circle-arrow-up,
        .fa-circle-arrow-down {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Header with Navigation Links -->
    <header>
        <nav>
            <ul>
                <li><a href="user_managment/add_user.php">Add User</a></li>
                <li><a href="user_managment/view_users.php">View/Edit Users</a></li>
                <li><a href="user_managment/add_meal.php">Add Meal</a></li>
                <li><a href="user_managment/manage_bookings.php">Manage Bookings</a></li>
            </ul>
        </nav>
    </header>

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
                    <th class="<?= $sortColumn === 'username' ? ($sortOrder === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>" 
                        onclick="window.location.href='?sort=username&order=<?= $sortOrder === 'ASC' ? 'DESC' : 'ASC' ?>'">
                        Username 
                        <i class="fa-solid fa-circle-arrow-up"></i>
                        <i class="fa-solid fa-circle-arrow-down"></i>
                    </th>
                    <th class="<?= $sortColumn === 'meal_name' ? ($sortOrder === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>" 
                        onclick="window.location.href='?sort=meal_name&order=<?= $sortOrder === 'ASC' ? 'DESC' : 'ASC' ?>'">
                        Meal 
                        <i class="fa-solid fa-circle-arrow-up"></i>
                        <i class="fa-solid fa-circle-arrow-down"></i>
                    </th>
                    <th class="<?= $sortColumn === 'price' ? ($sortOrder === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>" 
                        onclick="window.location.href='?sort=price&order=<?= $sortOrder === 'ASC' ? 'DESC' : 'ASC' ?>'">
                        Price 
                        <i class="fa-solid fa-circle-arrow-up"></i>
                        <i class="fa-solid fa-circle-arrow-down"></i>
                    </th>
                    <th class="<?= $sortColumn === 'observation' ? ($sortOrder === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>" 
                        onclick="window.location.href='?sort=observation&order=<?= $sortOrder === 'ASC' ? 'DESC' : 'ASC' ?>'">
                        Observation 
                        <i class="fa-solid fa-circle-arrow-up"></i>
                        <i class="fa-solid fa-circle-arrow-down"></i>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($bookings) > 0): ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['username']) ?></td>
                            <td><?= htmlspecialchars($booking['meal_name']) ?></td>
                            <td><?= htmlspecialchars($booking['price']) ?>€</td>
                            <td><?= htmlspecialchars($booking['observation']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
