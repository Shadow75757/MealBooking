<?php
// Get error message from query parameter
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <link rel="stylesheet" href="login.css"> <!-- Link to your CSS file -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script> <!-- SweetAlert2 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($error_message): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: '<?php echo addslashes($error_message); ?>',
                    confirmButtonText: 'OK',
                    onClose: function() {
                        window.location.href = 'login.php'; // Redirect back to login page after closing the alert
                    }
                });
            <?php endif; ?>
        });
    </script>
</head>
<body>
</body>
</html>
