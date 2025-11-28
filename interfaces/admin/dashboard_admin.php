<!-- dashboard ng adminnn -->

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

//add post
if ($_SERVER["REQUEST_METHOD"] === "POST" && $action === "store") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $item_type = trim($_POST["item_type"] ?? "");
    $location_lost = trim($_POST["location_lost"] ?? "");
    $location_found = trim($_POST["location_found"] ?? "");
    $date_lost_or_found = trim($_POST["date_lost_or_found"] ?? "");
    $current_location = trim($_POST["current_location"] ?? "");
    // i-add na lang yung category id and image upload 

    $user_id = $admin['id'];

    if ($title !== "" && $item_type !== "") {
        $stmt = $db->prepare("
            INSERT INTO items 
            (title, description, item_type, user_id, location_lost, location_found, date_lost_or_found, current_location) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bindValue(1, $title, SQLITE3_TEXT);
        $stmt->bindValue(2, $description, SQLITE3_TEXT);
        $stmt->bindValue(3, $item_type, SQLITE3_TEXT);
        $stmt->bindValue(4, $user_id, SQLITE3_INTEGER);
        $stmt->bindValue(5, $location_lost, SQLITE3_TEXT);
        $stmt->bindValue(5, $location_found, SQLITE3_TEXT);
        $stmt->bindValue(6, $date_lost_or_found, SQLITE3_TEXT);
        $stmt->bindValue(7, $current_location, SQLITE3_TEXT);
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
    $item_type = trim($_POST['item_type'] ?? "");
    $location_lost = trim($_POST['location_lost'] ?? "");
    $location_found = trim($_POST['location_found'] ?? "");
    $date_lost_or_found = trim($_POST['date_lost_or_found'] ?? "");
    $current_location = trim($_POST['current_location'] ?? "");


    if ($id > 0 && $title !== "" && $item_type !== "") {
        $stmt = $db->prepare("
            UPDATE items
            SET title = ?, description = ?, item_type = ?, location_lost = ?, location_found = ?, date_lost_or_found = ?, current_location = ?
            WHERE id = ?
        ");
        $stmt->bindValue(1, $title, SQLITE3_TEXT);
        $stmt->bindValue(2, $description, SQLITE3_TEXT);
        $stmt->bindValue(3, $item_type, SQLITE3_TEXT);
        $stmt->bindValue(4, $location_lost, SQLITE3_TEXT);
        $stmt->bindValue(4, $location_found, SQLITE3_TEXT);
        $stmt->bindValue(5, $date_lost_or_found, SQLITE3_TEXT);
        $stmt->bindValue(6, $current_location, SQLITE3_TEXT);
        $stmt->bindValue(7, $id, SQLITE3_INTEGER);

        $stmt->execute();

        header("Location: dashboard_admin.php?msg=Item+Updated");
        exit;
    } else {
        $error = "Title and Type are required.";
        $action = "edit";
        $_GET['id'] = (string)$id;
    }
}



//delete
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

//fetch items to display
$items = [];
$result = $db->query("SELECT * FROM items ORDER BY id DESC");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $items[] = $row;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../reusable/header.css">
    <link rel="stylesheet" href="../../reusable/cards.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Admin Dashboard</title>
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="m-0 border-0 bd-example">
<nav class="navbar p-1 sticky-top">
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
                <a href="?action=create">Add Item</a>
                <a href="#">About</a>
                <a class="logout" href="logout.php">Log out</a>
            </div>
        </div>
        <a class="navbar-brand ms-auto text-white" href="#">Hello, <?php echo htmlspecialchars($admin["username"]); ?></a>
    </div>
</nav>

<div class="container my-3">

    <?php if ($msg): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($action === "create"): ?>
        <!-- add post form -->
        <h3>Add New Item</h3>
        <form method="post" action="?action=store" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Title" required>
                <textarea name="description" placeholder="Description"></textarea>

                <select name="item_type" required>
                    <option value="">Select Type</option>
                    <option value="lost">Lost</option>
                    <option value="found">Found</option>
                </select>

                <input type="text" name="location_lost" placeholder="Location Lost">
                <input type="text" name="location_found" placeholder="Location Found">
                <input type="date" name="date_lost_or_found" placeholder="Date Lost/Found">
                <input type="text" name="current_location" placeholder="Current Location">

                <button class="btn" type="submit">Save</button>
                <a class="btn" href="dashboard_admin.php">Cancel</a>
            </form>

    <?php elseif ($action === "edit"): 
        $id = (int)($_GET["id"] ?? 0);
        $item = null;
        foreach ($items as $it) {
            if ($it["id"] === $id) $item = $it;
        }
        if ($item): ?>
            <!--edit item form -->
            <h3>Edit Item</h3>
            <form method="post" action="?action=edit">
        <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>">

        <input type="text" name="title" placeholder="Title" value="<?php echo htmlspecialchars($item['title']); ?>" required>
        <textarea name="description" placeholder="Description"><?php echo htmlspecialchars($item['description']); ?></textarea>

        <select name="item_type" required>
            <option value="">Select Type</option>
            <option value="lost" <?php if($item['item_type']=='lost') echo 'selected'; ?>>Lost</option>
            <option value="found" <?php if($item['item_type']=='found') echo 'selected'; ?>>Found</option>
        </select>

        <input type="text" name="location_lost" placeholder="Location Lost" value="<?php echo htmlspecialchars($item['location_lost']); ?>">
        <input type="text" name="location_found" placeholder="Location Found" value="<?php echo htmlspecialchars($item['location_found']); ?>">
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
        <!--di-display ng items -->
        <div class="row">
            <?php if (empty($items)): ?>
                <p>No items found.</p>
            <?php else: ?>
                <?php foreach ($items as $it): ?>
                    <div class="col-md-4">
                        <div class="card h-100 my-3">
                            <img src="/assets/image.png" class="card-img-top" alt="Item image">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($it["title"]); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($it["description"]); ?></p>
                                <a href="?action=edit&id=<?php echo (int)$it["id"]; ?>" class="btn btn-warning">Edit</a>
                                <a href="?action=delete&id=<?php echo (int)$it["id"]; ?>" class="btn btn-danger"
                                   onclick="return confirm('Delete this item?');">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
