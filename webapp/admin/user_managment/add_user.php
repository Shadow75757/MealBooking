<?php
session_start();

// Ensure the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. You must be an admin to view this page.");
}

include('../../connect_db.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SESSION['form_submitted'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Backend validation for password criteria
    if (strlen($password) >= 8 && preg_match('/[A-Z]/', $password) && (preg_match('/[0-9]/', $password) || preg_match('/[\W_]/', $password))) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user'; // Default role

        // Insert the user into the database with the role
        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        try {
            $stmt->execute([$username, $hashed_password, $role]);
            $_SESSION['form_submitted'] = true; // Prevent resubmission
            $_SESSION['user_added'] = true; // Flag for showing success message
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Password does not meet the criteria.";
    }
    
    // Redirect back to the same page to avoid resubmission
    header("Location: add_user.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Add User</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.0/sweetalert2.min.css">
    <style>
        body {
            font-family: "Arial", sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            box-sizing: border-box;
            position: relative;
            min-height: 100vh;
        }

        .form-container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            position: relative;
        }

        .form-container h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-size: 16px;
        }

        .form-container input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            margin-bottom: 20px;
        }

        .form-container button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            background-color: #3f6fa7;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            box-sizing: border-box;
        }

        .form-container button:hover {
            background-color: #2b517c;
        }

        .password-strength {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            width: 100%;
            opacity: 0;
            transition: opacity 0.3s ease;
            visibility: hidden;
        }

        .password-strength.show {
            opacity: 1;
            visibility: visible;
        }

        .strength-bar-container {
            flex: 1;
            display: flex;
            align-items: center;
            height: 8px;
            background-color: #ddd;
            border-radius: 5px;
            overflow: hidden;
        }


        .strength-text {
            font-size: 14px;
            color: #333;
            width: 80px;
            text-align: left;
            margin-right: 10px;
        }



        .strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s ease;
            border-radius: 5px;
        }

        .weak {
            color: red;
        }

        .medium {
            color: orange;
        }

        .strong {
            color: green;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 30px;
                width: 100%;
                max-width: 350px;
            }

            .form-container button {
                font-size: 14px;
                padding: 8px;
            }
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 20px;
                width: 100%;
                max-width: 300px;
            }

            .form-container button {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h1>Add New User</h1>
        <br>
        <form action="add_user.php" method="post">
            <input type="text" id="username" name="username" placeholder="Username" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <div class="password-strength" id="password-strength">
                <div id="strength-text" class="strength-text weak" style="font-weight: bold;">Weak</div>
                <div class="strength-bar-container">
                    <div id="strength-bar" class="strength-bar"></div>
                </div>
            </div>
            <button type="submit">Add User</button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.0/sweetalert2.all.min.js"></script>
    <script>
        function updatePasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('strength-bar');
            const strengthText = document.getElementById('strength-text');
            const passwordStrength = document.getElementById('password-strength');

            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password) || /[\W_]/.test(password)) strength++;
            if (/[0-9]/.test(password) || /[\W_]/.test(password)) strength++;

            let width = '0%';
            let color = '#ddd';
            let text = 'Weak';

            switch (strength) {
                case 1:
                    width = '33%';
                    color = 'red';
                    text = 'Weak';
                    break;
                case 2:
                    width = '66%';
                    color = 'orange';
                    text = 'Medium';
                    break;
                case 3:
                    width = '100%';
                    color = 'green';
                    text = 'Strong';
                    break;
            }

            strengthBar.style.width = width;
            strengthBar.style.backgroundColor = color;
            strengthText.textContent = text;
            strengthText.className = `strength-text ${text.toLowerCase()}`;
        }

        function handlePasswordFocus() {
            document.getElementById('password-strength').classList.add('show');
        }

        function handlePasswordBlur() {
            document.getElementById('password-strength').classList.remove('show');
        }

        function validateForm(event) {
            const password = document.getElementById('password').value;
            if (!/[A-Z]/.test(password) || !/[0-9]/.test(password) || password.length < 8) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Password Unmatched Criteria',
                    html: '<p><u><strong>Password must:</strong></u></p>' +
                        '<br>' +
                        '<p>- Be at least <strong>8 characters</strong> long</p>' +
                        '<p>- Contain at least <strong>1 capital</strong> letter</p>' +
                        '<p>- Contain either <strong>1 number</strong> or <strong>1 symbol</strong></p>' +
                        '</ul>',
                });
            }
        }

        document.getElementById('password').addEventListener('input', updatePasswordStrength);
        document.getElementById('password').addEventListener('focus', handlePasswordFocus);
        document.getElementById('password').addEventListener('blur', handlePasswordBlur);
        document.querySelector('form').addEventListener('submit', validateForm);

        // Show success message if user was added
        <?php if (isset($_SESSION['user_added'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'User Added Successfully',
                timer: 5000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                window.location.reload(); // Reload the page after 5 seconds
            });
            <?php unset($_SESSION['user_added']); ?>
        <?php endif; ?>
    </script>
</body>

</html>