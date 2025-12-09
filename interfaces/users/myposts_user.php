<?php
session_start();

// 1. SECURITY: Check if user is logged in
if (!isset($_SESSION["user"])) {
    header("Location: ../../login.php");
    exit;
}

$user = $_SESSION["user"];
$user_id = $user['id'];

require_once __DIR__ . "/../../database/db.php";
$db = get_db();

// Action Routing
$action = $_GET["action"] ?? "list";
$msg = $_GET["msg"] ?? "";
$error = "";

// ==========================================
// 1. HANDLE "POST ITEM" (CREATE)
// ==========================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && $action === "store") {
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category_id = intval($_POST["category_id"] ?? 0);
    $item_status = trim($_POST["item_status"] ?? "lost"); // Default to lost
    $location_lost = trim($_POST["location_lost"] ?? "");
    $location_found = trim($_POST["location_found"] ?? "");
    $date_lost_or_found = trim($_POST["date_lost_or_found"] ?? "");
    $current_location = trim($_POST["current_location"] ?? "");
    
    // --- IMAGE UPLOAD LOGIC ---
    $image_path_db = null;
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['item_image']['tmp_name'];
        $fileName = $_FILES['item_image']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = __DIR__ . '/../../uploads/';
            
            // Create folder if it doesn't exist
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            $dest_path = $uploadFileDir . $newFileName;
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_path_db = 'uploads/' . $newFileName; 
            }
        }
    }

    if ($title !== "") {
        $stmt = $db->prepare("
            INSERT INTO items (title, description, category_id, item_status, user_id, location_lost, location_found, date_lost_or_found, current_location, image_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bindValue(1, $title, SQLITE3_TEXT);
        $stmt->bindValue(2, $description, SQLITE3_TEXT);
        $stmt->bindValue(3, $category_id, SQLITE3_INTEGER);
        $stmt->bindValue(4, $item_status, SQLITE3_TEXT);
        $stmt->bindValue(5, $user_id, SQLITE3_INTEGER); // Link to CURRENT USER
        $stmt->bindValue(6, $location_lost, SQLITE3_TEXT);
        $stmt->bindValue(7, $location_found, SQLITE3_TEXT);
        $stmt->bindValue(8, $date_lost_or_found, SQLITE3_TEXT);
        $stmt->bindValue(9, $current_location, SQLITE3_TEXT);
        $stmt->bindValue(10, $image_path_db, SQLITE3_TEXT);
        
        $stmt->execute();
        header("Location: myposts_user.php?msg=Post+Created+Successfully");
        exit;
    } else {
        $error = "Title is required.";
    }
}

// ==========================================
// 2. HANDLE "EDIT POST" (UPDATE)
// ==========================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && $action === "edit") {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? "");
    $description = trim($_POST['description'] ?? "");
    $category_id = intval($_POST["category_id"] ?? 0);
    $item_status = trim($_POST['item_status'] ?? "");
    $location_lost = trim($_POST['location_lost'] ?? "");
    $location_found = trim($_POST['location_found'] ?? "");
    $date_lost_or_found = trim($_POST['date_lost_or_found'] ?? "");
    
    // Security: Ensure user owns this item
    $check = $db->prepare("SELECT id FROM items WHERE id = ? AND user_id = ?");
    $check->bindValue(1, $id, SQLITE3_INTEGER);
    $check->bindValue(2, $user_id, SQLITE3_INTEGER);
    $res = $check->execute()->fetchArray();

    if ($res && $title !== "") {
        $stmt = $db->prepare("
            UPDATE items
            SET title = ?, description = ?, category_id = ?, item_status = ?, location_lost = ?, location_found = ?, date_lost_or_found = ?
            WHERE id = ? AND user_id = ?
        ");
        $stmt->bindValue(1, $title, SQLITE3_TEXT);
        $stmt->bindValue(2, $description, SQLITE3_TEXT);
        $stmt->bindValue(3, $category_id, SQLITE3_INTEGER);
        $stmt->bindValue(4, $item_status, SQLITE3_TEXT);
        $stmt->bindValue(5, $location_lost, SQLITE3_TEXT);
        $stmt->bindValue(6, $location_found, SQLITE3_TEXT);
        $stmt->bindValue(7, $date_lost_or_found, SQLITE3_TEXT);
        $stmt->bindValue(8, $id, SQLITE3_INTEGER);
        $stmt->bindValue(9, $user_id, SQLITE3_INTEGER);

        $stmt->execute();
        header("Location: myposts_user.php?msg=Post+Updated");
        exit;
    } else {
        $error = "Error updating post.";
    }
}

// ==========================================
// 3. HANDLE DELETE
// ==========================================
if ($action === "delete") {
    $id = (int)($_GET["id"] ?? 0);
    if ($id > 0) {
        // Only delete if it belongs to the current user
        $stmt = $db->prepare("DELETE FROM items WHERE id = ? AND user_id = ?");
        $stmt->bindValue(1, $id, SQLITE3_INTEGER);
        $stmt->bindValue(2, $user_id, SQLITE3_INTEGER);
        $stmt->execute();

        header("Location: myposts_user.php?msg=Post+Deleted");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Posts - CampusFind</title>
    <link rel="icon" type="image/x-icon" href="../../assets/search.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../../reusable/header.css">
    <link rel="stylesheet" href="../../reusable/cards.css">
    <link rel="stylesheet" href="../../reusable/form.css"> <!-- Modern Form CSS -->
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="m-0 border-0">

<!-- NAVBAR -->
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
                <a href="dashboard_user.php">Dashboard</a>
                <a href="myposts_user.php" style="color: #2289e6; font-weight: 700;">My Posts</a>
                <a href="about_us.php">About</a>
                <a class="logout" href="../../logout.php" onclick="return confirm('Are you sure you want to LOG OUT?');">Log out</a>
            </div>
        </div>
        <strong><a class="navbar-brand me-auto" href="dashboard_user.php">Campus<span class="find">Find</span></a></strong>
        <a class="navbar-brand ms-auto text-white" href="#">Hello, <?php echo htmlspecialchars($user["username"]); ?></a>
    </div>
</nav>

<div class="container my-3">
    
    <!-- MESSAGES -->
    <?php if ($msg): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>


    <!-- ========================================== -->
    <!-- VIEW: CREATE NEW POST (MODERN FORM) -->
    <!-- ========================================== -->
    <?php if ($action === "create"): ?>
        <h3 class="mb-4" style="font-weight: 700; color: #334155;">Post a Lost Item</h3>
        
        <form method="post" action="?action=store" enctype="multipart/form-data">
            <div class="form-grid-layout">
                
                <!-- LEFT COLUMN -->
                <div class="form-left">
                    <div class="input-group-modern">
                        <label>Item Name</label>
                        <input type="text" name="title" placeholder="What did you lose?" required>
                    </div>

                    <div class="form-row">
                        <div class="input-group-modern">
                            <label>Status</label>
                            <select name="item_status" id="status" required>
                                <option value="lost" selected>Lost</option>
                                <option value="found">Found</option> <!-- Can remove if leader insists -->
                            </select>
                        </div>
                        <div class="input-group-modern">
                            <label>Category</label>
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
                        </div>
                    </div>

                    <div class="input-group-modern">
                        <label>Description</label>
                        <textarea name="description" rows="3" placeholder="Describe color, size, or unique marks..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="input-group-modern">
                            <label>Lost Location</label>
                            <input type="text" name="location_lost" id="location_lost" placeholder="Where did you last see it?">
                        </div>
                        <div class="input-group-modern">
                            <label>Found Location</label>
                            <input type="text" name="location_found" id="location_found" placeholder="N/A" disabled>
                        </div>
                    </div>

                    <div class="input-group-modern">
                        <label id="dateLabel">Date Lost</label>
                        <div class="date-card">
                            <input type="date" name="date_lost_or_found" id="date_lost_or_found" style="border:none; background:transparent;">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-2">
                        <button class="btn btn-primary w-50" type="submit">Submit Post</button>
                        <a class="btn btn-secondary w-50" href="myposts_user.php" style="background-color: #cbd5e1; border:none; color: #334155;">Cancel</a>
                    </div>
                </div>

                <!-- RIGHT COLUMN: IMAGE UPLOAD -->
                <div class="form-right">
                    <div class="input-group-modern" style="height: 100%;">
                        <label>Upload Photo</label>
                        <div class="image-upload-wrapper">
                            <input type="file" name="item_image" id="file-input-real" accept="image/*" onchange="previewImage(event)">
                            <img id="image-preview" src="#" alt="Image Preview">
                            <div class="upload-placeholder" id="upload-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/></svg>
                                <p><strong>Click to Upload</strong><br>Photo of the item</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <script>
        function previewImage(event) {
            const input = event.target;
            const preview = document.getElementById('image-preview');
            const placeholder = document.getElementById('upload-placeholder');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        </script>


    <!-- ========================================== -->
    <!-- VIEW: EDIT POST -->
    <!-- ========================================== -->
    <?php elseif ($action === "edit"): 
        $id = (int)($_GET["id"] ?? 0);
        // Only fetch if it belongs to user
        $stmt = $db->prepare("SELECT * FROM items WHERE id = ? AND user_id = ?");
        $stmt->bindValue(1, $id, SQLITE3_INTEGER);
        $stmt->bindValue(2, $user_id, SQLITE3_INTEGER);
        $res = $stmt->execute();
        $item = $res->fetchArray(SQLITE3_ASSOC);

        if ($item): ?>
            <div class="edit-form-wrapper">
                <h3>Edit My Post</h3>
                <form method="post" action="?action=edit">
                    <input type="hidden" name="id" value="<?php echo (int)$item['id']; ?>">
                    
                    <div class="input-group-modern">
                        <label>Item Name</label>
                        <input type="text" name="title" value="<?php echo htmlspecialchars($item['title']); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="input-group-modern">
                            <label>Status</label>
                            <select name="item_status" id="status" required>
                                <option value="lost" <?php if($item['item_status']=='lost') echo 'selected'; ?>>Lost</option>
                                <option value="found" <?php if($item['item_status']=='found') echo 'selected'; ?>>Found</option>
                            </select>
                        </div>
                        <div class="input-group-modern">
                            <label>Category</label>
                            <select name="category_id" required>
                                <option value="">Select Category</option>
                                <?php
                                $catQ = $db->query("SELECT * FROM categories ORDER BY name");
                                while($c = $catQ->fetchArray(SQLITE3_ASSOC)):
                                ?>
                                    <option value="<?= $c['id']; ?>" <?= ($item['category_id'] == $c['id']) ? 'selected' : '' ?>>
                                        <?= ucfirst(str_replace('_', ' ', $c['name'])); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="input-group-modern">
                        <label>Description</label>
                        <textarea name="description" rows="3"><?php echo htmlspecialchars($item['description']); ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="input-group-modern">
                            <label>Location</label>
                            <input type="text" name="location_lost" value="<?php echo htmlspecialchars($item['location_lost']); ?>">
                        </div>
                        <div class="input-group-modern">
                            <label>Date</label>
                            <input type="date" name="date_lost_or_found" value="<?php echo $item['date_lost_or_found']; ?>">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-2">
                        <button class="btn btn-primary w-50" type="submit">Update Post</button>
                        <a class="btn btn-secondary w-50" href="myposts_user.php">Cancel</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <p>Post not found or access denied.</p>
            <a class="btn btn-secondary" href="myposts_user.php">Back</a>
        <?php endif; ?>


    <!-- ========================================== -->
    <!-- VIEW: LIST MY POSTS -->
    <!-- ========================================== -->
    <?php else: ?>
        
        <section class="heroSection pt-3">
            <h1><strong>My Posts</strong></h1>
            <p class="subtext">Manage items you have reported.</p>
            <!-- BUTTON TO CREATE NEW POST -->
            <a id="postItems" type="button" class="btn btn-primary" href="?action=create">
                Report Lost Item +
            </a>
        </section>

        <!-- List Items -->
        <?php
        // Query: Only items by THIS user
        $stmt = $db->prepare("SELECT items.*, categories.name AS category_name 
                              FROM items 
                              LEFT JOIN categories ON items.category_id = categories.id
                              WHERE items.user_id = ? 
                              ORDER BY items.id DESC");
        $stmt->bindValue(1, $user_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $items = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $items[] = $row;
        } 
        ?>

        <div class="row">
            <?php if (empty($items)): ?>
                <div class="col-12 text-center mt-5">
                    <p class="text-muted">You haven't posted anything yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($items as $it): ?>
                    <div class="col-md-4">
                        <div class="card my-2">
                            <!-- Image -->
                            <?php if(!empty($it['image_path'])): ?>
                                <img src="../../<?php echo htmlspecialchars($it['image_path']); ?>" class="card-img-top" alt="Item image">
                            <?php else: ?>
                                <img src="/assets/image.png" class="card-img-top" alt="Default image">
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title"><strong><?php echo htmlspecialchars($it["title"]); ?></strong></h5>
                                
                                <div class="d-flex gap-2 mb-2">
                                    <span class="badge rounded-pill bg-<?php echo $it['item_status']; ?>">
                                        <?php echo ucfirst($it["item_status"]); ?>
                                    </span>
                                    <span class="category badge rounded-pill">
                                        <?php echo ucfirst(str_replace('_', ' ', $it["category_name"] ?? 'Other')); ?>
                                    </span>
                                </div>

                                <p class="card-text text-truncate"><?php echo htmlspecialchars($it["description"]); ?></p>
                                
                                <!-- USER ACTIONS -->
                                <div class="d-flex gap-2">
                                    <a href="?action=edit&id=<?php echo (int)$it["id"]; ?>" class="btn btn-warning w-50" style="font-size:0.8rem;">Edit</a>
                                    <a href="?action=delete&id=<?php echo (int)$it["id"]; ?>" class="btn btn-danger w-50" style="font-size:0.8rem;"
                                       onclick="return confirm('Delete this post?');">Delete</a>
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