<?php
include 'db.php';

$nameErr = $imageErr = "";
$name = $email = $address = $class_id = $image = "";

// Fetch classes for dropdown
$class_query = "SELECT class_id, name FROM classes";
$class_result = $conn->query($class_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $isValid = true;
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $class_id = $_POST['class_id'];

    // Validate name
    if (empty($name)) {
        $nameErr = "Name is required";
        $isValid = false;
    }

    // Validate and upload image
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_ext = array('jpg', 'jpeg', 'png');
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($file_ext), $allowed_ext)) {
            $imageErr = "Invalid image format. Only JPG and PNG are allowed.";
            $isValid = false;
        } else {
            // Generate unique filename
            $image = uniqid() . '.' . $file_ext;
            move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image);
        }
    }

    if ($isValid) {
        $stmt = $conn->prepare("INSERT INTO student (name, email, address, class_id, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssiss", $name, $email, $address, $class_id, $image);
        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Add Student</h1>
        <form method="post" action="create.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name">
                <span class="text-danger"><?php echo $nameErr; ?></span>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email">
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea class="form-control" id="address" name="address"></textarea>
            </div>
            <div class="form-group">
                <label for="class_id">Class</label>
                <select class="form-control" id="class_id" name="class_id">
                    <?php
                    if ($class_result->num_rows > 0) {
                        while($class_row = $class_result->fetch_assoc()) {
                            echo "<option value='" . $class_row['class_id'] . "'>" . $class_row['name'] . "</option>";
                        }
                    } else {
                        echo "<option>No classes available</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="image">Image</label>
                <input type="file" class="form-control-file" id="image" name="image">
                <span class="text-danger"><?php echo $imageErr; ?></span>
            </div>
            <button type="submit" class="btn btn-primary">Add Student</button>
        </form>
        <a href="index.php" class="btn btn-secondary mt-3">Back to List</a>
    </div>
</body>
</html>
