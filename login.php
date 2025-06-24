
  <?php
session_start();

// Load employee data
$employees = file_exists('employee.json') ? json_decode(file_get_contents('employee.json'), true) : [];

// Generate CAPTCHA only if it doesn't exist or if we're doing a fresh page load
if (!isset($_SESSION['captcha_generated']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['captcha'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
    $_SESSION['captcha_generated'] = true;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $user_captcha = $_POST['captcha'];
    
    // Validate inputs
    $error = null;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif (strtolower($user_captcha) !== strtolower($_SESSION['captcha'])) {
        $error = "CAPTCHA verification failed";
    } else {
        // Check if email is registered
        $isRegistered = false;
        foreach ($employees as $employee) {
            if (strtolower($employee['email']) === strtolower($email)) {
                $isRegistered = true;
                $_SESSION['employee_data'] = $employee;
                break;
            }
        }
        
        if (!$isRegistered) {
            $error = "This email is not registered. Please sign up first.";
        } else {
            // Generate OTP (6 digits)
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['email'] = $email;
            $_SESSION['otp_expiry'] = time() + 300; // OTP valid for 5 minutes
            
            // Send OTP via email
            require 'mail.php';
            
            // Redirect to OTP verification
            header("Location: otp_verification.php");
            exit();
        }
    }
    
    // Clear the captcha generation flag to allow new CAPTCHA on refresh
    unset($_SESSION['captcha_generated']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employee Login</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 400px; 
            margin: 30px auto; 
            padding: 20px; 
            background-color: #f5f5f5;
        }
        .container { 
            background: #fff; 
            padding: 25px; 
            border-radius: 8px; 
            box-shadow: 0 0 15px rgba(0,0,0,0.1); 
        }
        h2 { 
            color: #333; 
            text-align: center; 
            margin-bottom: 25px; 
        }
        .error { 
            color: #d9534f; 
            background: #fdf7f7; 
            padding: 10px; 
            border-radius: 4px; 
            margin-bottom: 15px; 
            border: 1px solid #ebccd1;
        }
        .form-group { 
            margin-bottom: 15px; 
        }
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: 600; 
            color: #555;
        }
        input { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 4px; 
            box-sizing: border-box; 
            font-size: 16px;
        }
        .captcha-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 15px 0;
        }
        .captcha { 
            font-size: 24px; 
            letter-spacing: 3px; 
            background: #f5f5f5; 
            padding: 10px; 
            text-align: center; 
            flex-grow: 1;
            border-radius: 4px;
            user-select: none;
        }
        .refresh-captcha {
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px;
            cursor: pointer;
        }
        .refresh-captcha:hover {
            background: #5a6268;
        }
        button { 
            width: 100%; 
            padding: 12px; 
            background: #4285f4; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 16px;
            font-weight: 600;
            margin-top: 10px;
        }
        button:hover { 
            background: #3367d6; 
        }
        .register-link { 
            text-align: center; 
            margin-top: 20px; 
            color: #666;
        }
        a { 
            color: #4285f4; 
            text-decoration: none; 
        }
        a:hover { 
            text-decoration: underline; 
        }



    </style>
</head>
<body>
    <div class="container">
        <h2>Employee Login</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="post" id="loginForm">
            <div class="form-group">
                <label for="email">Registered Email:</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>CAPTCHA Verification:</label>
                <div class="captcha-container">
                    <div class="captcha" id="captchaText"><?php echo $_SESSION['captcha']; ?></div>
                    <button type="button" class="refresh-captcha" onclick="refreshCaptcha()">↻</button>
                </div>
                <input type="text" name="captcha" placeholder="Enter CAPTCHA" required>
            </div>
            
            <button type="submit">Send OTP</button>
        </form>
        
        <div class="register-link">
            New employee? <a href="employee_registration.php">Register here</a>
        </div>
    </div>

    <script>
        function refreshCaptcha() {
            fetch('refresh_captcha.php')
                .then(response => response.text())
                .then(captcha => {
                    document.getElementById('captchaText').textContent = captcha;
                })
                .catch(error => console.error('Error refreshing CAPTCHA:', error));
        }
    </script>
</body>
</html>      