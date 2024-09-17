<?php
session_start();
$conn = new mysqli("localhost", "root", "", "meal_booking");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            header("Location: meals.php");
        } else {
            // Wrong password
            header("Location: index.php?error=1");
        }
    } else {
        // User does not exist
        header("Location: index.php?error=1");
    }
}
?>
