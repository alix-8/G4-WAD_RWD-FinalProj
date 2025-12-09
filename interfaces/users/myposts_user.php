<?php
session_start();

// SECURITY CHECK
if (!isset($_SESSION["user"])) {
    header("Location: ../../login.php");
    exit;
}

if ($_SESSION["user"]["role"] !== 'user') {
    header("Location: ../users/dashboard_user.php");
    exit;
}

$user = $_SESSION["user"];
require_once __DIR__ . "/../../database/db.php";
$db = get_db();

// action routing
$action = $_GET["action"] ?? "list";
$msg = $_GET["msg"] ?? "";
$error = "";

// ==========================================
// LOGIC: ADD POST (STORE) + IMAGE UPLOAD
// ==========================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && $action === "store") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category_id = intval($_POST["category_id"] ?? 0);
    $item_status = trim($_POST["item_status"] ?? "");
    $location_lost = trim($_POST["location_lost"] ?? "");
    $location_found = trim($_POST["location_found"] ?? "");
    $date_lost_or_found = trim($_POST["date_lost_or_found"] ?? "");
    $current_location = trim($_POST["current_location"] ?? "");
    $user_id = $_SESSION["user"]["id"];
    
    // --- IMAGE UPLOAD LOGIC START DITO ---
    $image_path_db = null; // Default to null if no image uploaded

    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['item_image']['tmp_name'];
        $fileName = $_FILES['item_image']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Allowed extensions
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Create a unique name to prevent overwriting
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            
            $uploadFileDir = __DIR__ . '/../../uploads/';
            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                // Success :D
                $image_path_db = 'uploads/' . $newFileName; 
            }
        }
    }
    // --- IMAGE UPLOAD LOGIC END HEREE---

    if ($title !== "" && $item_status !== "") {
        $stmt = $db->prepare("
            INSERT INTO items (title, description, category_id, item_status, user_id, location_lost, location_found, date_lost_or_found, current_location, image_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
        $stmt->bindValue(10, $image_path_db, SQLITE3_TEXT); // Bind the image path
        
        $stmt->execute();

        header("Location: dashboard_user.php?msg=Item+Added");
        exit;
    } else {
        $error = "Title and Type are required.";
        $action = "create";
    }
}


// ==========================================
// LOGIC: EDIT POST ITO
// ==========================================x
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
        $stmt->bindValue(3, $category_id, SQLITE3_INTEGER); // Fixed type to INTEGER
        $stmt->bindValue(4, $item_status, SQLITE3_TEXT);
        $stmt->bindValue(5, $location_lost, SQLITE3_TEXT);
        $stmt->bindValue(6, $location_found, SQLITE3_TEXT);
        $stmt->bindValue(7, $date_lost_or_found, SQLITE3_TEXT);
        $stmt->bindValue(8, $current_location, SQLITE3_TEXT);
        $stmt->bindValue(9, $id, SQLITE3_INTEGER);

        $stmt->execute();

        header("Location: dashboard_user.php?msg=Item+Updated");
        exit;
    } else {
        $error = "Title and Type are required.";
        $action = "edit";
        $_GET['id'] = (string)$id;
    }
}

// ==========================================
// LOGIC: DELETE ITEM HERE
// ==========================================
if ($action === "delete") {
    $id = (int)($_GET["id"] ?? 0);
    if ($id > 0) {
        $stmt = $db->prepare("DELETE FROM items WHERE id = ?");
        $stmt->bindValue(1, $id, SQLITE3_INTEGER);
        $stmt->execute();

        header("Location: dashboard_user.php?msg=Item+Deleted");
        exit;
    }
}

// ==========================================
// LOGIC: CLAIM ITEMMMM
// ==========================================
if ($action === "claim") {
    $id = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
        $stmt = $db->prepare("UPDATE items SET item_status = 'claimed' WHERE id = ?");
        $stmt->bindValue(1, $id, SQLITE3_INTEGER);
        $stmt->execute();

        header("Location: dashboard_user.php?msg=Item+Status+Updated+To+Claimed");
        exit;
    } else {
        $error = "Invalid item ID for claiming.";
    }
}

// fetch notifss
$userId = $_SESSION["user"]["id"];

$notifQuery = $db->query("
    SELECT notifications.*, items.title AS item_title
    FROM notifications
    LEFT JOIN items ON items.id = notifications.item_id
    WHERE notifications.notify_to = $userId
    AND notifications.type = 'to_user'
    ORDER BY notifications.created_at DESC
");

$userNotifications = [];
while ($n = $notifQuery->fetchArray(SQLITE3_ASSOC)) {
    $userNotifications[] = $n;
}


// ==========================================
// LOGIC: FETCH ITEMS (SEARCH / FILTER / ONLY USER POSTS)
// ==========================================
$where = [];

// Logged-in user filter
$currentUserId = $_SESSION["user"]["id"];
$where[] = "items.user_id = $currentUserId";

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

$sql = "SELECT items.*, categories.name AS category_name, users.username AS posted_by
        FROM items
        LEFT JOIN categories ON items.category_id = categories.id
        LEFT JOIN users ON users.id = items.user_id";

$sql .= " WHERE " . implode(" AND ", $where);

$sql .= " ORDER BY items.id DESC";


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Recent Posts</title>
    <link rel="icon" type="image/x-icon" href="../../assets/search.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../reusable/header.css">
    <link rel="stylesheet" href="../../reusable/cards.css">
    <link rel="stylesheet" href="../../reusable/form.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="m-0 border-0 bd-example m-0 border-0">
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
                    <!-- 1. DASHBOARD (Active) -->
                    <!-- Added bold/color style here because we are ON the dashboard -->
                    <a href="dashboard_user.php">Dashboard</a>

                    <!-- 2. MY POSTS -->
                    <a href="myposts_user.php" style="color: #2289e6; font-weight: 700;">My Feed</a>

                    <!-- 3. ABOUT -->
                    <a href="about_us.php">About</a>

                    <!-- 4. LOG OUT -->
                    <a class="logout" href="../../logout.php" onclick="return confirm('Are you sure you want to LOG OUT?');">Log out</a>
                </div>
            </div>
            <strong><a class="navbar-brand me-auto" href="#">Campus<span class = "find">Find</a></strong>
            
            <?php $notifCount = $db->querySingle("SELECT COUNT(*) FROM notifications WHERE status = 'unread';");?>
            <div class = "ms-auto">
                <a href="myposts_admin.php" class="text-white mx-4">
                    🔔 (<?= $notifCount ?>)
                </a>
                <a class="navbar-brand text-white" href="#">Hello, <?php echo htmlspecialchars($user["username"]); ?></a>
            </div>
        </div>
    </nav>
<div class="container my-3">
    <!-- ========================================== -->
    <!-- DASHBOARD LIST VIEW DITO -->
    <!-- ========================================== -->
        
        <!-- hero section -->
        <section class = "heroSection pt-3">
            <h1><strong>My Feed</strong></h1>
            <p class = "subtext">Review your posts and check your notification</p>
            <a id = "postItems" type="button" class="btn btn-primary" href="?action=create">
                Post Item +
            </a>
        </section>

        <!-- search + filter section -->
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
         
        <!-- notification dito -->
        <div class="row mt-3">
            <?php if (empty($userNotifications)): ?>
                <p class="text-muted">No notifications yet.</p>
            <?php else: ?>
                <?php foreach ($userNotifications as $note): ?>
                    <div class="col-md-6">
                        <div class="card p-3 my-2" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#notifModal-<?= $note['id']; ?>">
                            <strong><?= htmlspecialchars($note['item_title']); ?></strong>
                            <p class="mb-1"><?= htmlspecialchars($note['message']); ?></p>
                            <small class="text-secondary"><?= $note["created_at"]; ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php foreach ($userNotifications as $note): ?>
        <div class="modal fade" id="notifModal-<?= $note['id']; ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Notification – <?= htmlspecialchars($note['item_title']); ?>
                        </h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p><?= nl2br(htmlspecialchars($note['message'])); ?></p>
                        <small class="text-muted">Received: <?= $note['created_at']; ?></small>
                    </div>

                    <div class="modal-footer">
                        <form method="POST">
                            <input type="hidden" name="mark_read" value="<?= $note['id']; ?>">
                            <button class="btn btn-success">Okay</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
        <?php endforeach; ?>

            


        <!-- cards dito -->
        <?php
        $result = $db->query($sql);
        $items = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $items[] = $row;
        } ?>

        <div class="row">
            <?php if (empty($items)): ?>
                <p class = "noItemFound">No items found.</p>
            <?php else: ?>
                <?php $itemCount = count($items);?>
                <p>Showing <strong><?php echo $itemCount; ?></strong>  items.</p>
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
                                <p class="posted-by">
                                    Posted by: <strong><?= htmlspecialchars($it["posted_by"]); ?></strong>
                                </p>


                                <!-- badgess -->
                                <div class="d-flex gap-2 mb-2">
                                    <span class="badge rounded-pill bg-<?php echo $it['item_status']; ?>">
                                        <?php echo ucfirst($it["item_status"]); ?>
                                    </span>
                                    <?php if ($it["category_name"]): ?>
                                    <span class="category badge rounded-pill">
                                        <?php echo ucfirst(str_replace('_', ' ', $it["category_name"])); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>

                                <p class="card-text"><?php echo htmlspecialchars($it["description"]); ?></p>
                                <!-- Button para sa modal -->
                                <button id = "seeDetails" type="button" class="btn btn-primary rounded-3 w-100" data-bs-toggle="modal" data-bs-target="#modal-<?php echo $it['id']; ?>">
                                    <img class = "view" src="/assets/eye.png" alt="view" > See Details
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="modal-<?php echo $it['id']; ?>">
                                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content mx-3">
                                        <div class="modal-header">
                                            <h5 class="modal-title fs-5" id="staticBackdropLabel">
                                                <strong><?php echo htmlspecialchars($it["title"]); ?></strong>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            
                                            <div class="d-flex gap-2 mb-2">
                                                <span class="badge rounded-pill bg-<?php echo $it['item_status']; ?>">
                                                    <?php echo ucfirst($it["item_status"]); ?>
                                                </span>
                                                <?php if ($it["category_name"]): ?>
                                                <span class="category badge rounded-pill">
                                                    <?php echo ucfirst(str_replace('_', ' ', $it["category_name"])); ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>

                                            <p class="card-text">
                                                <strong>Description</strong><br>
                                                <?php echo htmlspecialchars($it["description"]); ?><br>
                                                <div class = "details">
                                                    <div class="field">
                                                        <?php if ($it["item_status"] == "lost"): ?>
                                                        <strong>Location lost <br></strong>
                                                        <?php echo htmlspecialchars($it["location_lost"]); ?>
                                                        <?php elseif ($it["item_status"] == "found"): ?>
                                                            <strong>Location found <br></strong>
                                                            <?php echo htmlspecialchars($it["location_found"]); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="field">
                                                        <?php if ($it["item_status"] == "lost"): ?>
                                                        <strong>Date lost <br></strong>
                                                        <?php elseif ($it["item_status"] == "found"): ?>
                                                            <strong>Date found <br></strong>
                                                        <?php endif; ?>  
                                                        <?php echo htmlspecialchars($it["date_lost_or_found"]); ?> <br> 
                                                    </div>
                                                    
                                                </div>
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
    
</div>   
  </body>
</html>