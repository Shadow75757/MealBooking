<?php
session_start();
$conn = new mysqli("localhost", "root", "", "meal_booking");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($_POST['password'], $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            session_regenerate_id(true);

            if ($user['role'] === 'admin') {
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['is_admin'] = true;
                header("Location: admin/admin.php");
                exit();
            } else {
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['is_admin'] = false;
                header("Location: meals.php");
                exit();
            }
        } else {
            header("Location: index.php?error=invalid_credentials");
            exit();
        }
    } else {
        header("Location: index.php?error=user_not_found");
        exit();
    }
    $stmt->close();
}
$conn->close();
