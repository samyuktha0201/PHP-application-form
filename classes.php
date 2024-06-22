<?php
include 'db.php';

// Handle add, edit, and delete actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$class_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$nameErr = "";

// Add new class
if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'add') {
    $name = $_POST['name'];

    if (empty($name)) {
        $nameErr = "Class name is required";
    } else {
        $stmt = $conn->prepare("INSERT INTO classes (name, created_at) VALUES (?, NOW())");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            header("Location: classes.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Edit class
if ($_SERVER["REQUEST_METHOD"] == "POST" && $action == 'edit') {
    $name = $_POST['name'];

    if (empty($name)) {
        $nameErr = "Class name is required";
    } else {
        $stmt = $conn->prepare("UPDATE classes SET name = ? WHERE class_id = ?");
        $stmt->bind_param("si", $name, $class_id);
        if ($stmt->execute()) {
            header("Location: classes.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Delete class
if ($action == 'delete' && $class_id > 0) {
    $stmt = $conn->prepare("DELETE FROM classes WHERE class_id = ?");
    $stmt->bind_param("i", $class_id);
    if ($stmt->execute()) {
        header("Location: classes.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all classes
$sql = "SELECT class_id, name, created_at FROM classes";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Classes</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Manage Classes</h1>

        <!-- Add New Class Form -->
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Add New Class</h5>
                <form method="post" action="classes.php?action=add">
                    <div class="form-group">
                        <label for="name">Class Name</label>
                        <input type="text" class="form-control" id="name" name="name">
                        <span class="text-danger"><?php echo $nameErr; ?></span>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Class</button>
                </form>
            </div>
        </div>

        <!-- Edit Class Form -->
        <?php if ($action == 'edit' && $class_id > 0): ?>
            <?php
            // Fetch class details for editing
            $stmt = $conn->prepare("SELECT name FROM classes WHERE class_id = ?");
            $stmt->bind_param("i", $class_id);
            $stmt->execute();
            $class_result = $stmt->get_result();
            $class = $class_result->fetch_assoc();
            $stmt->close();
            ?>
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">Edit Class</h5>
                    <form method="post" action="classes.php?action=edit&id=<?php echo $class_id; ?>">
                        <div class="form-group">
                            <label for="name">Class Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($class['name']); ?>">
                            <span class="text-danger"><?php echo $nameErr; ?></span>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Class</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Class List -->
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Class List</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['class_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                    <td>
                                        <a href="classes.php?action=edit&id=<?php echo $row['class_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="classes.php?action=delete&id=<?php echo $row['class_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this class?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No classes found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>

<?php $conn->close(); ?>
