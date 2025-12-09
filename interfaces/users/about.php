<!DOCTYPE html>
<html lang="en">
<head>
    <title>About</title>
    <link rel="icon" type="image/x-icon" href="../../assets/search.png">  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../reusable/header.css">
    <link rel="stylesheet" href="../../reusable/form.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="m-0 border-0 bd-example">
<nav class="navbar p-3 sticky-top">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions"
                aria-expanded="false" aria-label="Toggle navigation">
            <img src="/assets/hamburger.png" alt="hamburger icon" width="20px" height="20px">
        </button>
        <div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1"
             id="offcanvasWithBothOptions" aria-labelledby="offcanvasWithBothOptionsLabel">
            <div class="offcanvas-body">
                <a href="dashboard_user.php" id="active_button">Dashboard</a>
                <a href="myposts_user.php">My Posts</a>
                <a href="#">About</a>
                <a class="logout" href="#">Log out</a>
            </div>
        </div>
        <strong><a class="navbar-brand me-auto" href="#">Campus<span class = "find">Find</a></strong>
        <a class="navbar-brand ms-auto text-white" href="#">Hello, <?php echo htmlspecialchars($user["username"]); ?></a>
    </div>
</nav>


</body>
</html>