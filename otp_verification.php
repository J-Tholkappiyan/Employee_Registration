

        <?php
session_start();

// Strict session validation
if (!isset($_SESSION['otp']) || !isset($_SESSION['employee_data']) || !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$error = null;
$showForm = true;

// Ensure otp_expiry is set (fallback to current time + 5 mins if not)
if (!isset($_SESSION['otp_expiry'])) {
    $_SESSION['otp_expiry'] = time() + 200; // 5 minutes as fallback
}

// Handle OTP submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // First check if OTP expired
    if (time() > $_SESSION['otp_expiry']) {
        $error = "OTP has expired. Please request a new one.";
        $showForm = false;
        unset($_SESSION['otp']);
        unset($_SESSION['otp_expiry']);
    } 
    // Then verify OTP if form was submitted with OTP
    elseif (isset($_POST['otp'])) {
        if ($_POST['otp'] == $_SESSION['otp']) {
            // Successful verification
            $_SESSION['loggedin'] = true;
            $_SESSION['otp_verified'] = true;
            
            // Clear OTP data
            unset($_SESSION['otp']);
            unset($_SESSION['otp_expiry']);
            
            header("Location: welcome.php");
            exit();
        } else {
            $error = "Invalid OTP. Please try again.";
            
            // Track failed attempts
            $_SESSION['failed_attempts'] = ($_SESSION['failed_attempts'] ?? 0) + 1;
            
            // Block after 3 attempts
            if ($_SESSION['failed_attempts'] >= 3) {
                session_unset();
                session_destroy();
                header("Location: login.php?error=too_many_attempts");
                exit();
            }
        }
    }
}

// Auto-resend OTP if expired and no form submission
if (time() > $_SESSION['otp_expiry'] && !isset($_POST['otp'])) {
    header("Location: send_otp.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
    <style>
        
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 30px auto; padding: 20px; }
        .container { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        h2 { color: #333; text-align: center; margin-bottom: 25px; }
        .error { color: #d9534f; background: #fdf7f7; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .employee-info { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #4285f4; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #3367d6; }
        .back-link { text-align: center; margin-top: 20px; }
        
        /* Progress Bar Styles */
        .progress-container {
            width: 100%;
            background: #e0e0e0;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .progress-bar {
            height: 10px;
            border-radius: 5px;
            background: #4285f4;
            width: 100%;
            transition: width 1s linear;
        }
        .time-left {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>OTP Verification</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="employee-info">
            <h3>Hello, <?php echo htmlspecialchars($_SESSION['employee_data']['ename']); ?></h3>
            <p>We've sent an OTP to <?php echo htmlspecialchars($_SESSION['email']); ?></p>
            <p>Employee ID: <?php echo htmlspecialchars($_SESSION['employee_data']['empid']); ?></p>
        </div>
        
        <?php if ($showForm): ?>
        <!-- Countdown Timer & Progress Bar -->
        <div class="time-left" id="timeLeft">Time left: 5:00</div>
        <div class="progress-container">
            <div class="progress-bar" id="progressBar"></div>
        </div>
        
        <form method="post">
            <div class="form-group">
                <label for="otp">Enter OTP:</label>
                <input type="text" id="otp" name="otp" required maxlength="6" pattern="\d{6}" title="6 digit OTP">
            </div>
            
            <button type="submit">Verify OTP</button>
        </form>
        <?php else: ?>
        <div class="resend-link">
            <a href="send_otp.php">Resend OTP</a>
        </div>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="login.php">Back to Login</a>
        </div>
    </div>

    <script>
        // OTP Expiry Time (5 minutes = 300 seconds)
        const expiryTime = <?php echo $_SESSION['otp_expiry'] - time(); ?>;
        let timeLeft = expiryTime > 0 ? expiryTime : 0;
        
        const progressBar = document.getElementById('progressBar');
        const timeLeftElement = document.getElementById('timeLeft');
        
        // Only run timer if form is visible
        <?php if ($showForm): ?>
        const timer = setInterval(() => {
            timeLeft--;
            
            // Update progress bar
            const progressPercentage = (timeLeft / expiryTime) * 100;
            if (progressBar) progressBar.style.width = `${progressPercentage}%`;
            
            // Change color when time is running out            Hiii
            if (timeLeft <= 30 && progressBar) {
                progressBar.style.background = '#d9534f';
            }
            
            // Format time display
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            if (timeLeftElement) timeLeftElement.textContent = `Time left: ${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
            
            // Handle expiry
            if (timeLeft <= 0) {
                clearInterval(timer);
                alert("OTP has expired! A new OTP will be sent.");
                window.location.href = "send_otp.php";
            }
        }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>