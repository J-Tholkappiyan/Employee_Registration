<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mahendra Textile - Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #e3f2fd, #ffffff);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .container {
            text-align: center;
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .button {
            display: block;
            width: 100%;
            padding: 15px;
            margin: 15px 0;
            font-size: 16px;
            font-weight: 600;
            color: white;
            background: #007BFF;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .button:hover {
            background: #0056b3;
        }

        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #666;
        }

        @media (max-width: 500px) {
            .container {
                padding: 25px 20px;
            }

            h1 {
                font-size: 24px;
            }

            .button {
                font-size: 15px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Mahendra Textile </h1>
        <a href="login.php" class="button">Employee Login</a>
        <a href="employee_registration.php" class="button">Employee Registration</a>
        <div class="footer">
            &copy; <?php echo date("Y"); ?> Mahendra Textile. All rights reserved.
        </div>
    </div>

</body>
</html>
