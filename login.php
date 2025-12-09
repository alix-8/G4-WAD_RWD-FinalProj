<?php
session_start();
require_once __DIR__ . "/database/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $db = get_db();
    $stmt = $db->prepare("SELECT id, username, email, password_hash, role FROM users WHERE email = ?");
    $stmt->bindValue(1, $email, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    if ($user && password_verify($password, $user["password_hash"])) {
        $_SESSION["user"] = [
            "id" => $user["id"],
            "username" => $user["username"],
            "email" => $user["email"],
            "role" => $user["role"]
        ];

        if ($user["role"] === "admin") {
            header("Location: interfaces/admin/dashboard_admin.php");
            exit;
        } else {
            header("Location: interfaces/users/dashboard_user.php");
            exit;
        }
    }
    $error = "Invalid email or password.";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="icon" type="image/x-icon" href="../assets/search.png">

    <style>
        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: flex;
            height: 100vh;
        }

        .left, .right {
            width: 50%;
            height: 100%;
        }

        .left {
            display: flex;
            justify-content: center;
            align-items: center;
            background: #ffffff;
            flex-direction: column;
            padding: 40px;
        }

        .login-box {
            width: 100%;
            max-width: 350px;
        }

        .login-box h2 {
            font-size: 28px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        input, button {
            width: 100%;
            padding: 12px;
            margin-top: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        button {
            background: #0561ff;
            color: white;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #0047cc;
        }

        .right {
            background: url('../assets/login-cover.jpg') center/cover no-repeat;
            position: relative;
        }

        .right .desc {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 30px;
            background: rgba(0, 0, 0, 0.55);
            color: white;
            text-align: center;
            font-size: 18px;
            line-height: 1.5;
        }

        /* Responsive */
        @media (max-width: 900px) {
            body {
                flex-direction: column;
            }
            .left, .right {
                width: 100%;
                height: 50vh;
            }
        }
    </style>
</head>

<body>
    <div class="left">
        <div class="login-box">
            <h2>Login to Campus Find</h2>

            <?php if (isset($_GET["registered"])) echo "<p style='color:green;'>Registration successful! Please login.</p>"; ?>
            <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

            <form method="POST" action="">
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>

            <p style="margin-top:15px;">
                Don’t have an account? <a href="register.php">Register</a>
            </p>
        </div>
    </div>

    <div class="right">
        <div class="desc">
            <strong>CampusFind</strong> — Your gateway to discovering opportunities, people, and events around campus.
        </div>
    </div>
</body>

</html>
