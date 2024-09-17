<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Booking Login</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container">
        <div class="login-box">
            <h2>Login</h2>
            <form action="login.php" method="post">
                <div class="textbox">
                    <input type="text" placeholder="Username" name="username" required>
                </div>
                <div class="textbox">
                    <input type="password" placeholder="Password" name="password" required>
                </div>
                <input type="submit" class="btn" value="Login">
            </form>
        </div>
    </div>

    <?php
    if (isset($_GET['error'])) {
        echo "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: 'Incorrect username or password!',
            confirmButtonText: 'Try Again'
        });
        </script>
        ";
    }
    ?>
</body>

</html>