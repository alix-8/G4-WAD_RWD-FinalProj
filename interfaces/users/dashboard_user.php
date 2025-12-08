<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$admin = $_SESSION["user"];
require_once __DIR__ . "/../../database/db.php";
$db = get_db();

// action routing
$action = $_GET["action"] ?? "list";
$msg = $_GET["msg"] ?? "";
$error = "";

// claim item
// if ($action === "claim") {
//     $id = (int)($_GET['id'] ?? 0);

//     if ($id > 0) {
//         $stmt = $db->prepare("UPDATE items SET item_status = 'claimed' WHERE id = ?");
//         $stmt->bindValue(1, $id, SQLITE3_INTEGER);
//         $stmt->execute();

//         header("Location: dashboard_admin.php?msg=Item+Status+Updated+To+Claimed");
//         exit;
//     } else {
//         $error = "Invalid item ID for claiming.";
//     }
// }


//fetch items to display (else condition sa html)
$where = [];

if (!empty($_GET['search'])) {
    $search = $db->escapeString($_GET['search']);
    $where[] = "(title LIKE '%$search%' 
                 OR description LIKE '%$search%'
                 OR location_lost LIKE '%$search%'
                 OR location_found LIKE '%$search%')";
}

if (!empty($_GET['item_status'])) {
    $status = $db->escapeString($_GET['item_status']);
    $where[] = "item_status = '$status'";
}

if (!empty($_GET['category_id'])) {
    $cat = intval($_GET['category_id']);
    $where[] = "category_id = $cat";
}

$sql = "SELECT * FROM items";
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY id DESC";

    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
    <link rel="icon" type="image/x-icon" href="../../assets/search.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../reusable/header.css">
    <link rel="stylesheet" href="../../reusable/cards.css">
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
                <a href="dashboard_user.php" id="active_button">Home</a>
                <a href="dashboard_user.php" id="active_button">Dashboard</a>
                <a href="about.php">About</a>
                <a class="logout" href="../../logout.php" onclick = "return confirm('Are you sure you want to LOG OUT?');">Log out</a>
            </div>
        </div>
        <strong><a class="navbar-brand me-auto" href="#">Campus<span class = "find">Find</a></strong>
        <a class="navbar-brand ms-auto text-white" href="#">Hello, <?php echo htmlspecialchars($admin["username"]); ?></a>
    </div>
</nav>

<div class="container my-3">
    <!-- alert messages -->
    <?php if ($msg): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>

        <!--main na dashboard talaga -->
        
        <!-- hero section -->
        <section class = "heroSection pt-3">
            <h1><strong>Dashboard</strong></h1>
            <p class = "subtext">Browse and search lost & found items</p>
        </section>

        <!-- search + filter section hereee -->
        <div class="search-filter-container my-2">
    
            <form class="d-flex py-2 w-100" method="GET">
                <input 
                    class="form-control me-2" 
                    type="search" 
                    name="search"
                    placeholder="Search (input any keyword e.g. color, item)" 
                    value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                />
                <button class="btn searchBtn" type="submit">Search</button>
            </form>


            <form method="GET" class="d-flex">
                <select name="item_status" class="form-select m-2 me-auto">
                    <option value="">All Items</option>
                    <option value="lost" <?php if(isset($_GET['item_status']) && $_GET['item_status']=="lost") echo "selected"; ?>>Lost</option>
                    <option value="found" <?php if(isset($_GET['item_status']) && $_GET['item_status']=="found") echo "selected"; ?>>Found</option>
                </select>

                <button type="submit" class="btn btn-primary m-2">Filter</button>
            </form>

            <form method="GET" class="d-flex">
                <select id="filterCategory" name="category_id" class="form-select m-2 me-auto">
                    <option value="">All Categories</option>
                    <?php 
                    $catQuery = $db->query("SELECT * FROM categories ORDER BY name");
                    while($row = $catQuery->fetchArray(SQLITE3_ASSOC)): ?>
                        <option value="<?= $row['id']; ?>">
                            <?= ucfirst(str_replace('_', ' ', $row['name'])); ?>
                        </option>
                    <?php endwhile; ?>
                </select>


                <button type="submit" class="btn btn-primary m-2">Filter</button>
            </form>
            
        </div>
            

        <!-- cards dito -->
        <?php
        $result = $db->query($sql);
        $items = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $items[] = $row;
        } ?>

        <div class="row">
            <?php if (empty($items)): ?>
                <p>No items found.</p>
            <?php else: ?>
                <?php foreach ($items as $it): ?>
                    <div class="col-md-4">
                        <!-- cardddd -->
                        <div class="card my-2">
                            <!-- IMAGE DISPLAY LOGIC -->
                            <?php if(!empty($it['image_path'])): ?>
                                <img src="../../<?php echo htmlspecialchars($it['image_path']); ?>" class="card-img-top" alt="Item image">
                            <?php else: ?>
                                <img src="/assets/image.png" class="card-img-top" alt="Default image">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><strong><?php echo htmlspecialchars($it["title"]); ?></strong></h5>
                                <p class="card-text"><?php echo htmlspecialchars($it["description"]); ?></p>
                                <!-- Button para sa modal -->
                                <button id = "seeDetails" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-<?php echo $it['id']; ?>">
                                    See Details
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="modal-<?php echo $it['id']; ?>">
                                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Item Details</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <h5 class="card-title">
                                            <strong><?php echo htmlspecialchars($it["title"]); ?></strong>
                                        </h5>
                                        <p class="card-text">
                                            <strong>Description:</strong>
                                            <?php echo htmlspecialchars($it["description"]); ?><br>
                                        
                                            <?php if ($it["item_status"] == "lost"): ?>
                                                <strong>Location lost:</strong>
                                                <?php echo htmlspecialchars($it["location_lost"]); ?>
                                            <?php elseif ($it["item_status"] == "found"): ?>
                                                <strong>Location found:</strong>
                                                <?php echo htmlspecialchars($it["location_found"]); ?>
                                            <?php endif; ?><br>
                                        
                                            <?php if ($it["item_status"] == "lost"): ?>
                                                <strong>Date lost:</strong>
                                            <?php elseif ($it["item_status"] == "found"): ?>
                                                <strong>Date found:</strong>
                                            <?php endif; ?>  
                                            <?php echo htmlspecialchars($it["date_lost_or_found"]); ?> <br> 
                                        </p>
                                    </div>
                                    <div class="modal-footer">
                                        <p>Is this yours? If you think it is, head to the Lost and Found Office, prove possesion and claim now!</p>
                                        <a href="">Want to know if the office is open?</a>
                                        <!-- <?php if ($it['item_status'] !== 'claimed'): ?>
                                            <a href="?action=claim&id=<?php echo (int)$it["id"]; ?>" onclick="return confirm('Are you sure you want to request to CLAIM this item?');" class="btn btn-success">Request claim</a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary" disabled>Claim Request Pending</button>
                                        <?php endif; ?> -->
                                    </div>
                                    </div>
                                </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
</div>

<script src="../../javascripts/form.js"></script>
</body>
</html>
