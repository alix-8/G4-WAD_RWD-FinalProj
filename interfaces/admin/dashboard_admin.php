<!-- dashboard ng adminnn -->

<?php
session_start();

// require_once __DIR__ . "/../../reusable/filter.php";

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

// logic ng add post
if ($_SERVER["REQUEST_METHOD"] === "POST" && $action === "store") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category_id = intval($_POST["category_id"] ?? 0);
    $item_status = trim($_POST["item_status"] ?? "");
    $location_lost = trim($_POST["location_lost"] ?? "");
    $location_found = trim($_POST["location_found"] ?? "");
    $date_lost_or_found = trim($_POST["date_lost_or_found"] ?? "");
    $current_location = trim($_POST["current_location"] ?? "");
    // i-add na lang yung category id and image upload 

    $user_id = $admin['id'];

    if ($title !== "" && $item_status !== "") {
        $stmt = $db->prepare("
            INSERT INTO items (title, description, category_id, item_status, user_id, location_lost, location_found, date_lost_or_found, current_location)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bindValue(1, $title, SQLITE3_TEXT);
        $stmt->bindValue(2, $description, SQLITE3_TEXT);
        $stmt->bindValue(3, $category_id, SQLITE3_INTEGER);
        $stmt->bindValue(4, $item_status, SQLITE3_TEXT);
        $stmt->bindValue(5, $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(6, $location_lost, SQLITE3_TEXT);
        $stmt->bindValue(7, $location_found, SQLITE3_TEXT);
        $stmt->bindValue(8, $date_lost_or_found, SQLITE3_TEXT);
        $stmt->bindValue(9, $current_location, SQLITE3_TEXT);
        $stmt->execute();

        header("Location: dashboard_admin.php?msg=Item+Added");
        exit;
    } else {
        $error = "Title and Type are required.";
        $action = "create";
    }
}


//edit ng post
if ($_SERVER["REQUEST_METHOD"] === "POST" && $action === "edit") {
    $id = (int)($_POST['id'] ?? 0);

    $title = trim($_POST['title'] ?? "");
    $description = trim($_POST['description'] ?? "");
    $category_id = intval($_POST["category_id"] ?? 0);
    $item_status = trim($_POST['item_status'] ?? "");
    $location_lost = trim($_POST['location_lost'] ?? "");
    $location_found = trim($_POST['location_found'] ?? "");
    $date_lost_or_found = trim($_POST['date_lost_or_found'] ?? "");
    $current_location = trim($_POST['current_location'] ?? "");


    if ($id > 0 && $title !== "" && $item_status !== "") {
        $stmt = $db->prepare("
            UPDATE items
            SET title = ?, description = ?, category_id = ?, item_status = ?, location_lost = ?, location_found = ?, date_lost_or_found = ?, current_location = ?
            WHERE id = ?
        ");
        $stmt->bindValue(1, $title, SQLITE3_TEXT);
        $stmt->bindValue(2, $description, SQLITE3_TEXT);
        $stmt->bindValue(3, $category_id, SQLITE3_TEXT);
        $stmt->bindValue(4, $item_status, SQLITE3_TEXT);
        $stmt->bindValue(5, $location_lost, SQLITE3_TEXT);
        $stmt->bindValue(6, $location_found, SQLITE3_TEXT);
        $stmt->bindValue(7, $date_lost_or_found, SQLITE3_TEXT);
        $stmt->bindValue(8, $current_location, SQLITE3_TEXT);
        $stmt->bindValue(9, $id, SQLITE3_INTEGER);

        $stmt->execute();

        header("Location: dashboard_admin.php?msg=Item+Updated");
        exit;
    } else {
        $error = "Title and Type are required.";
        $action = "edit";
        $_GET['id'] = (string)$id;
    }
}



//delete item
if ($action === "delete") {
    $id = (int)($_GET["id"] ?? 0);
    if ($id > 0) {
        $stmt = $db->prepare("DELETE FROM items WHERE id = ?");
        $stmt->bindValue(1, $id, SQLITE3_INTEGER);
        $stmt->execute();

        header("Location: dashboard_admin.php?msg=Item+Deleted");
        exit;
    }
}

// claim item
if ($action === "claim") {
    $id = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
        $stmt = $db->prepare("UPDATE items SET item_status = 'claimed' WHERE id = ?");
        $stmt->bindValue(1, $id, SQLITE3_INTEGER);
        $stmt->execute();

        header("Location: dashboard_admin.php?msg=Item+Status+Updated+To+Claimed");
        exit;
    } else {
        $error = "Invalid item ID for claiming.";
    }
}


//fetch items to display (else condition sa html)

$where = [];

if (!empty($_GET['search'])) {
    $search = $db->escapeString($_GET['search']);
    $where[] = "(items.title LIKE '%$search%' 
                 OR items.description LIKE '%$search%'
                 OR items.location_lost LIKE '%$search%'
                 OR items.location_found LIKE '%$search%')";
}

if (!empty($_GET['category_id'])) {
    $cat = intval($_GET['category_id']);
    $where[] = "items.category_id = $cat";
}

if (!empty($_GET['item_status'])) {
    $status = $db->escapeString($_GET['item_status']);
    $where[] = "items.item_status = '$status'";
}



$sql = "SELECT items.*, categories.name AS category_name 
        FROM items 
        LEFT JOIN categories ON items.category_id = categories.id";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY items.id DESC";



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../reusable/header.css">
    <link rel="stylesheet" href="../../reusable/cards.css">
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
                <a href="dashboard_admin.php" id="active_button">Dashboard</a>
                <a class="logout" href="/logout.php" onclick = "return confirm('Are you sure you want to LOG OUT?');">Log out</a>
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

    <?php if ($action === "create"): ?>
        <!-- add post form -->
        <h3>Add New Item</h3>
        <form method="post" action="?action=store" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Title" required>
            <textarea name="description" placeholder="Description"></textarea>

        <select name="category_id" id="category_id" required>
            <option value="">Select Category</option>
            <?php
            $catQ = $db->query("SELECT * FROM categories ORDER BY name");
            while($c = $catQ->fetchArray(SQLITE3_ASSOC)):
            ?>
                <option value="<?= $c['id']; ?>">
                    <?= ucfirst(str_replace('_', ' ', $c['name'])); ?>
                </option>
            <?php endwhile; ?>
        </select>


        <select name="item_status" id="status" required>
            <option value="">Select Type</option>
            <option value="lost">Lost</option>
            <option value="found">Found</option>
        </select>

        <input type="text" name="location_lost" id="location_lost" placeholder="Location Lost">
        <input type="text" name="location_found" id="location_found" placeholder="Location Found">

        <label for="date_lost_or_found" id="dateLabel">Date Lost</label>
        <input type="date" name="date_lost_or_found" id="date_lost_or_found">

            <input type="text" name="current_location" placeholder="Current Location">

            <button class="btn" type="submit">Save</button>
            <a class="btn" href="dashboard_admin.php">Cancel</a>
        </form>

    <?php elseif ($action === "edit"): 
        $id = (int)($_GET["id"] ?? 0);
        $stmt = $db->prepare("SELECT * FROM items WHERE id = ?");
        $stmt->bindValue(1, $id, SQLITE3_INTEGER);
        $res = $stmt->execute();
        $item = $res->fetchArray(SQLITE3_ASSOC);

        if ($item): ?>
            <!--edit item form -->
            <h3>Edit Item</h3>
            <form method="post" action="?action=edit">
        <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>">

        <input type="text" name="title" placeholder="Title" value="<?php echo htmlspecialchars($item['title']); ?>" required>
        <select name="category_id" required>
            <option value="">Select Category</option>
            <?php
            $catQ = $db->query("SELECT * FROM categories ORDER BY name");
            while($c = $catQ->fetchArray(SQLITE3_ASSOC)):
            ?>
                <option value="<?= $c['id']; ?>" 
                    <?= ($item['category_id'] == $c['id']) ? 'selected' : '' ?>>
                    <?= ucfirst(str_replace('_', ' ', $c['name'])); ?>
                </option>
            <?php endwhile; ?>
        </select>


        <textarea name="description" placeholder="Description"><?php echo htmlspecialchars($item['description']); ?></textarea>

        <select name="item_status" id = "status" required>
            <option value="">Select Type</option>
            <option value="lost" <?php if($item['item_status']=='lost') echo 'selected'; ?>>Lost</option>
            <option value="found" <?php if($item['item_status']=='found') echo 'selected'; ?>>Found</option>
        </select>

        <input type="text" name="location_lost" id="location_lost" placeholder="Location Lost" value="<?php echo htmlspecialchars($item['location_lost']); ?>">
        <input type="text" name="location_found" id="location_found" placeholder="Location Found" value="<?php echo htmlspecialchars($item['location_found']); ?>">

        <label for="date_lost_or_found" id = "dateLabel">Date Lost</label>
        <input type="date" name="date_lost_or_found" value="<?php echo $item['date_lost_or_found']; ?>">

        <input type="text" name="current_location" placeholder="Current Location" value="<?php echo htmlspecialchars($item['current_location']); ?>">

        <button class="btn" type="submit">Update</button>
        <a class="btn" href="dashboard_admin.php">Cancel</a>
    </form>


        <?php else: ?>
            <p>Item not found.</p>
            <a class="btn btn-secondary" href="dashboard.php">Back</a>
        <?php endif; ?>

    <?php else: ?>
    <!--main na dashboard talaga -->
        
        <!-- hero section -->
        <section class = "heroSection pt-3">
            <h1><strong>Dashboard</strong></h1>
            <p class = "subtext">Browse and search lost & found items</p>
            <a id = "postItems" type="button" class="btn btn-primary" href="?action=create">
                Post Item +
            </a>
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
                    <option value="claimed" <?php if(isset($_GET['item_status']) && $_GET['item_status']=="claimed") echo "selected"; ?>>Claimed</option>
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
                    <div class="col-md-3">
                        <!-- cardddd -->
                        <div class="card my-2">
                            <img src="/assets/image.png" class="card-img-top" alt="Item image">
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
                                        <?php if ($it['item_status'] !== 'claimed'): ?>
                                            <a href="?action=claim&id=<?php echo (int)$it["id"]; ?>" onclick="return confirm('Are you sure you want to mark this item CLAIMED?');" class="btn btn-success">Claim</a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary" disabled>Claimed</button>
                                        <?php endif; ?>

                                        
                                        <a href="?action=edit&id=<?php echo (int)$it["id"]; ?>" class="btn btn-warning">Edit</a>
                                        <a href="?action=delete&id=<?php echo (int)$it["id"]; ?>" class="btn btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
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
    <?php endif; ?>
</div>

<script src="../../javascripts/form.js"></script>
</body>
</html>
