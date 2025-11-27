<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../reusable/header.css">
    <link rel="stylesheet" href="../../reusable/cards.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://getbootstrap.com/docs/5.3/assets/css/docs.css" rel="stylesheet">
    <title>Admin Dashboard</title>
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="m-0 border-0 bd-example m-0 border-0">
    <!-- navigation tapos sidebar to -->
    <nav class="navbar p-1 sticky-top">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions" aria-expanded="false" aria-label="Toggle navigation">
                <img src="/assets/hamburger.png" alt="hamburger icon" width="20px" height="20px">
            </button>

            <div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions" aria-labelledby="offcanvasWithBothOptionsLabel">
                <div class="offcanvas-body">
                    <a href="#">Dashboard</a>
                    <a href="#">My posts</a>
                    <a href="#">About</a>
                    <a class = "logout" ref="#">Log out</a>
                </div>
            </div>
            <a class="navbar-brand ms-auto text-white" href="#">Hello, Admin</a>
        </div>
    </nav>

    <!-- hero section hereee :) -->

    <!-- here yung cards poo -->
    <div class="container"> 
            <div class="card h-100 my-3">
            <img src="/assets/image.png" class="card-img-top" alt="..." >
            <div class="card-body">
                <h5 class="card-title">Card title</h5>
                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
                <div>
                    <a href="#" class="btn btn-primary">See Details</a>
                </div>
            </div>
            </div>

            <div class="card h-100 my-3">
            <img src="/assets/image.png" class="card-img-top" alt="...">
            <div class="card-body">
                <h5 class="card-title">Card title</h5>
                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
                <div>
                    <a href="#" class="btn btn-primary">See Details</a>
                </div>
            </div>
            </div>

            <div class="card h-100 my-3">
            <img src="/assets/image.png" class="card-img-top" alt="...">
            <div class="card-body">
                <h5 class="card-title">Card title</h5>
                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
                <div>
                    <a href="#" class="btn btn-primary">See Details</a>
                </div>
            </div>
            </div>

            <div class="card h-100 my-3">
            <img src="/assets/image.png" class="card-img-top" alt="...">
            <div class="card-body">
                <h5 class="card-title">Card title</h5>
                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
                <div>
                    <a href="#" class="btn btn-primary">See Details</a>
                </div>
            </div>
            </div>

            <div class="card h-100 my-3">
            <img src="/assets/image.png" class="card-img-top" alt="...">
            <div class="card-body">
                <h5 class="card-title">Card title</h5>
                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card’s content.</p>
                <div>
                    <a href="#" class="btn btn-primary">See Details</a>
                </div>
            </div>
            </div>
    </div>

    
    
    
  </body>
</html>