<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    echo '<p>You need to be logged in to view this page.</p>';
    exit();
}

// Include the database connection
include('connect_db.php');

// Initialize variables
$meal = null;
$has_booked = false;

// Fetch meal of the day details
$meal_id = 1; // Assuming we're fetching a specific meal, e.g., today's meal
$stmt = $conn->prepare('SELECT * FROM meals WHERE id = :meal_id');
$stmt->bindParam(':meal_id', $meal_id);
$stmt->execute();
$meal = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if meal was fetched successfully
if (!$meal) {
    echo '<p>Error: Meal not found.</p>';
    exit();
}

// Fetch the user's booking details (if they have already booked)
$stmt = $conn->prepare('SELECT * FROM bookings WHERE user_id = :user_id AND meal_id = :meal_id');
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->bindParam(':meal_id', $meal_id);
$stmt->execute();
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the user has booked the meal
if ($booking) {
    $has_booked = true;
}

// Handle form submission for booking, unbooking, or updating observation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $observation = $_POST['observation'] ?? '';

    if ($action === 'book') {
        $stmt = $conn->prepare('INSERT INTO bookings (user_id, meal_id, observation) VALUES (:user_id, :meal_id, :observation)');
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':meal_id', $meal_id);
        $stmt->bindParam(':observation', $observation);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Meal booked successfully!']);
    } elseif ($action === 'cancel') {
        $stmt = $conn->prepare('DELETE FROM bookings WHERE user_id = :user_id AND meal_id = :meal_id');
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':meal_id', $meal_id);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully!']);
    } elseif ($action === 'update') {
        $stmt = $conn->prepare('UPDATE bookings SET observation = :observation WHERE user_id = :user_id AND meal_id = :meal_id');
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':meal_id', $meal_id);
        $stmt->bindParam(':observation', $observation);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Observation updated successfully!']);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Booking</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to your CSS file -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- jQuery -->
    <script>
        $(document).ready(function() {
            function handleFormSubmit(event, action) {
                event.preventDefault(); // Prevent default form submission

                let formId = action === 'book' ? '#book-meal-form' : (action === 'update' ? '#update-observation-form' : '#cancel-booking-form');
                let formData = $(formId).serialize() + '&action=' + action;

                $.ajax({
                    url: '', // Submit to the same page
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        let responseData = JSON.parse(response);
                        if (responseData.success) {
                            Swal.fire({
                                icon: 'success',
                                title: action === 'update' ? 'Updated!' : 'Success!',
                                text: responseData.message,
                                timer: 5000,  // 5 seconds
                                timerProgressBar: true,
                                willClose: () => {
                                    location.reload(); // Reload page to update booking status
                                }
                            });
                        } else {
                            Swal.fire('Error!', responseData.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'There was a problem with the request.', 'error');
                    }
                });
            }

            $('#book-meal-form').submit(function(event) {
                handleFormSubmit(event, 'book');
            });

            $('#update-observation-form').submit(function(event) {
                handleFormSubmit(event, 'update');
            });

            $('#cancel-booking-form').submit(function(event) {
                handleFormSubmit(event, 'cancel');
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="meal-box">
            <h2>Today's Meal: <?php echo htmlspecialchars($meal['name']); ?></h2>
            <img src="<?php echo htmlspecialchars($meal['image_url']); ?>" alt="Meal Image">
            <p><strong>Price:</strong> $<?php echo htmlspecialchars($meal['price']); ?></p>

            <?php if ($has_booked): ?>
                <p><strong>Your Booking:</strong></p>
                <form id="update-observation-form">
                    <textarea name="observation"><?php echo htmlspecialchars($booking['observation']); ?></textarea>
                    <button type="submit" class="btn">Update Observation</button>
                </form>
                <form id="cancel-booking-form">
                    <button type="submit" class="btn btn-danger">Cancel Booking</button>
                </form>
            <?php else: ?>
                <p><strong>You haven't booked this meal yet.</strong></p>
                <form id="book-meal-form">
                    <textarea name="observation" placeholder="Leave an observation"></textarea>
                    <button type="submit" class="btn">Book This Meal</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
