<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="icon" type="image/x-icon" href="../assets/search.png">
    <link rel="stylesheet" href="../reusable/style.css">

    <!-- EXTRA layout overrides (keeps your styles unchanged) -->
    <style>
        body {
            display: flex;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: #fafafa;
        }

        .login-left, .login-right {
            width: 50%;
            height: 100vh;
        }

        /* LEFT PANEL */
        .login-left {
            background: white;
            display: flex;
            flex-direction: column;
            padding: 3rem;
            position: relative;
            border-right: 1px solid #e5e7eb;
        }

        /* Title stays on top */
        .login-title {
            margin-bottom: 2rem;
        }

        /* Form is vertically centered */
        .login-form-wrapper {
            position: absolute;
            top: 50%;
            left: 3rem;
            right: 3rem;
            transform: translateY(-50%);
            max-width: 360px;
        }

        /* RIGHT PANEL */
        .login-right {
            background: url('../assets/login-cover.jpg') center/cover no-repeat;
            position: relative;
        }

        .right-description {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 1.5rem;
            background: rgba(0,0,0,0.55);
            color: white;
            font-size: 1rem;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 900px) {
            body {
                flex-direction: column;
            }

            .login-left, .login-right {
                width: 100%;
                height: auto;
            }

            .login-form-wrapper {
                position: static;
                transform: none;
                margin-top: 2rem;
            }
        }
    </style>
</head>

<body>

<div class="login-left">

    <div class="login-title">
        <h2>Login to Campus Find</h2>
        <?php if (isset($_GET["registered"])) echo "<p style='color:green;'>Registration successful! Please login.</p>"; ?>
        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    </div>

    <div class="login-form-wrapper">
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Email Ad
