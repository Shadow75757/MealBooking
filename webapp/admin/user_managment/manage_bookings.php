<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Access denied. You must be an admin to view this page.");
}

include('../../connect_db.php');

// Get the current date or default to today
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Handle sorting
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'username'; // Default sort by username
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'ASC'; // Default sort order

// Fetch bookings for the selected date
$sql = "SELECT bookings.id as booking_id, users.username, meals.name AS meal_name, bookings.observation 
        FROM bookings 
        JOIN users ON bookings.user_id = users.id 
        JOIN meals ON bookings.meal_id = meals.id
        WHERE DATE(bookings.booking_date) = ?
        ORDER BY $sortColumn $sortOrder";
$stmt = $conn->prepare($sql);
$stmt->execute([$date]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all meals for the "Add Booking" form
$mealsSql = "SELECT id, name FROM meals";
$mealsStmt = $conn->prepare($mealsSql);
$mealsStmt->execute();
$meals = $mealsStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle booking removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_booking_id'])) {
    $deleteBookingId = $_POST['delete_booking_id'];
    $deleteStmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $deleteStmt->execute([$deleteBookingId]);
    header("Location: manage_bookings.php?date=$date");
    exit();
}

// Handle adding or updating bookings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_booking'])) {
    $userId = $_POST['user_id'];
    $mealId = $_POST['meal_id'];
    $observation = $_POST['observation'];

    // Check if the user already has a booking for the same meal today
    $checkBookingSql = "SELECT id, observation, meal_id FROM bookings WHERE user_id = ? AND DATE(booking_date) = ?";
    $checkBookingStmt = $conn->prepare($checkBookingSql);
    $checkBookingStmt->execute([$userId, $date]);
    $existingBooking = $checkBookingStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingBooking) {
        if ($existingBooking['meal_id'] == $mealId && $existingBooking['observation'] == $observation) {
            echo "No changes detected. Booking not updated.";
        } else {
            // Update existing booking
            $updateBookingSql = "UPDATE bookings SET meal_id = ?, observation = ? WHERE id = ?";
            $updateBookingStmt = $conn->prepare($updateBookingSql);
            $updateBookingStmt->execute([$mealId, $observation, $existingBooking['id']]);
            echo "Booking updated successfully!";
        }
    } else {
        // Insert new booking
        $addBookingSql = "INSERT INTO bookings (user_id, meal_id, observation, booking_date) VALUES (?, ?, ?, ?)";
        $addBookingStmt = $conn->prepare($addBookingSql);
        $addBookingStmt->execute([$userId, $mealId, $observation, $date . ' 00:00:00']); // Set time to start of the day
        echo "Booking added successfully!";
    }

    header("Location: manage_bookings.php?date=$date");
    exit();
}

// Fetch all users for the "Add Booking" form
$usersSql = "SELECT id, username FROM users";
$usersStmt = $conn->prepare($usersSql);
$usersStmt->execute();
$usersList = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../admin.css">
    <script src="https://kit.fontawesome.com/076868c758.js" crossorigin="anonymous"></script>
    <title>Manage Bookings</title>
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
    <h1>Manage Bookings</h1>

    <!-- Navigation for dates -->
    <div class="date-navigation">
        <a href="manage_bookings.php?date=<?= date('Y-m-d', strtotime($date . ' -1 day')) ?>"><</a>
        <span><?= htmlspecialchars($date) ?></span>
        <a href="manage_bookings.php?date=<?= date('Y-m-d', strtotime($date . ' +1 day')) ?>">></a>
    </div>

    <!-- Bookings Table -->
    <table>
        <thead>
            <tr>
                <th class="<?= $sortColumn === 'username' ? ($sortOrder === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>" 
                    onclick="window.location.href='manage_bookings.php?date=<?= $date ?>&sort=username&order=<?= $sortOrder === 'ASC' ? 'DESC' : 'ASC' ?>'">
                    Username 
                    <i class="fa-solid fa-circle-arrow-up"></i>
                    <i class="fa-solid fa-circle-arrow-down"></i>
                </th>
                <th class="<?= $sortColumn === 'meal_name' ? ($sortOrder === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>" 
                    onclick="window.location.href='manage_bookings.php?date=<?= $date ?>&sort=meal_name&order=<?= $sortOrder === 'ASC' ? 'DESC' : 'ASC' ?>'">
                    Meal 
                    <i class="fa-solid fa-circle-arrow-up"></i>
                    <i class="fa-solid fa-circle-arrow-down"></i>
                </th>
                <th class="<?= $sortColumn === 'observation' ? ($sortOrder === 'ASC' ? 'sorted-asc' : 'sorted-desc') : '' ?>" 
                    onclick="window.location.href='manage_bookings.php?date=<?= $date ?>&sort=observation&order=<?= $sortOrder === 'ASC' ? 'DESC' : 'ASC' ?>'">
                    Observation 
                    <i class="fa-solid fa-circle-arrow-up"></i>
                    <i class="fa-solid fa-circle-arrow-down"></i>
                </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $booking): ?>
            <tr>
                <td><?= htmlspecialchars($booking['username']) ?></td>
                <td><?= htmlspecialchars($booking['meal_name']) ?></td>
                <td><?= htmlspecialchars($booking['observation']) ?></td>
                <td>
                    <form action="manage_bookings.php" method="post">
                        <input type="hidden" name="delete_booking_id" value="<?= $booking['booking_id'] ?>">
                        <button type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Add New Booking</h2>
    <form action="manage_bookings.php" method="post">
        <label for="user_id">User:</label>
        <select id="user_id" name="user_id" required>
            <?php foreach ($usersList as $user): ?>
                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="meal_id">Meal:</label>
        <select id="meal_id" name="meal_id" required>
            <?php foreach ($meals as $meal): ?>
                <option value="<?= $meal['id'] ?>"><?= htmlspecialchars($meal['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="observation">Observation:</label>
        <input type="text" id="observation" name="observation">

        <button type="submit" name="add_booking">Add Booking</button>
    </form>
</body>
</html>
