<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['employee_data'])) {
    header("Location: login.php");
    exit();
}

// This second check for otp_verified might be redundant if loggedin already implies it,
// but keeping it if it's part of your intended security flow.
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['otp_verified'])) {
    header("Location: login.php");
    exit();
}

// The 'echo' here will output "Welcome, [Employee Name]" before the HTML.
// If you want it inside the HTML structure, remove this line and keep only the HTML part.
// echo "Welcome, ".htmlspecialchars($_SESSION['employee_data']['ename']);

$employee = $_SESSION['employee_data'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Welcome - Mahendra Textile</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f1f1;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #ffffff;
            padding: 35px;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
        }

        .welcome-header {
            text-align: center;
            margin-bottom: 35px;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        p {
            color: #777;
        }

        .employee-details {
            background: #fafafa;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .detail-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-label {
            width: 200px;
            font-weight: 600;
            color: #444;
        }

        .detail-value {
            flex: 1;
            color: #333;
        }

        .logout-btn {
            display: inline-block;
            margin: 30px auto 0;
            padding: 12px 30px;
            background: #e74c3c;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        @media (max-width: 600px) {
            .detail-row {
                flex-direction: column;
            }

            .detail-label {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-header">
            <h1>Welcome, <?php echo htmlspecialchars($employee['ename']); ?>!</h1>
            <p>You have successfully logged in to the Mahendra Textile Employee Portal</p>
        </div>

        <div class="employee-details">
            <h2>Your Details</h2>

            <div class="detail-row">
                <div class="detail-label">Employee ID:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['empid']); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Full Name:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['ename']); ?></div>
            </div>

            <?php if (!empty($employee['lname'])): ?>
            <div class="detail-row">
                <div class="detail-label">Last Name:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['lname']); ?></div>
            </div>
            <?php endif; ?>
            <div class="detail-row">
                <div class="detail-label">Email:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['email']); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Mobile Number:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['mobileno']); ?></div>
            </div>

            <?php if (!empty($employee['aadhar'])): ?>
            <div class="detail-row">
                <div class="detail-label">Aadhar Number:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['aadhar']); ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($employee['pincode'])): ?>
            <div class="detail-row">
                <div class="detail-label">Pincode:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['pincode']); ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($employee['address'])): ?>
            <div class="detail-row">
                <div class="detail-label">Address:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['address']); ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($employee['city'])): ?>
            <div class="detail-row">
                <div class="detail-label">City:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['city']); ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($employee['state'])): ?>
            <div class="detail-row">
                <div class="detail-label">State:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['state']); ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($employee['country'])): ?>
            <div class="detail-row">
                <div class="detail-label">Country:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['country']); ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($employee['gender'])): ?>
            <div class="detail-row">
                <div class="detail-label">Gender:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['gender']); ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($employee['dob'])): ?>
            <div class="detail-row">
                <div class="detail-label">Date of Birth:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['dob']); ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($employee['religion'])): ?>
            <div class="detail-row">
                <div class="detail-label">Religion:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['religion']); ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($employee['designation'])): ?>
            <div class="detail-row">
                <div class="detail-label">Designation:</div>
                <div class="detail-value"><?php echo htmlspecialchars($employee['designation']); ?></div>
            </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center;">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</body>
</html>