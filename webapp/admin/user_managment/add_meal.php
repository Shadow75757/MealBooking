<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Access denied. You must be an admin to view this page.");
}

include('../../connect_db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mealName = $_POST['meal_name'];
    $price = $_POST['price'];

    $sql = "INSERT INTO meals (name, price) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$mealName, $price]);

    echo "Meal added successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Meal</title>
</head>
<body>
    <h1>Add New Meal</h1>
    <form action="add_meal.php" method="post">
        <label for="meal_name">Meal Name:</label>
        <input type="text" id="meal_name" name="meal_name" required>
        <label for="price">Price:</label>
        <input type="number" id="price" name="price" required>
        <button type="submit">Add Meal</button>
    </form>
</body>
</html>
