<?php
include 'db.php';

// Get the student ID from the URL
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id > 0) {
    // Fetch student details
    $sql = "SELECT student.id, student.name, student.email, student.address, student.class_id, student.image, classes.name as class_name 
            FROM student 
            JOIN classes ON student.class_id = classes.class_id 
            WHERE student.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $student = $result->fetch_assoc();
    } else {
        echo "Student not found.";
        exit;
    }

    $stmt->close();
} else {
    echo "Invalid student ID.";
    exit;
}

// Fetch classes for dropdown
$class_query = "SELECT class_id, name FROM classes";
$class_result = $conn->query($class_query);

$nameErr = $imageErr = "";
$name = $email = $address = $class_id = $image = $existing_image = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $isValid = true;
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $class_id = $_POST['class_id'];
    $existing_image = $_POST['existing_image'];

    // Validate name
    if (empty($name)) {
        $nameErr = "Name is required";
        $isValid = false;
    }

    // Validate image if a new image is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_ext = array('jpg', 'jpeg', 'png');
        $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($file_ext), $allowed_ext)) {
            $imageErr = "Invalid image format. Only JPG and PNG are allowed.";
            $isValid = false;
        } else {
            // Upload new image
            $image = time() . '_' . $_FILES['image']['name'];
            move_uploaded_file($_FILES['image']['tmp_name'], 'images/' . $image);
        }
    } else {
        // Keep existing image if no new image is uploaded
        $image = $existing_image;
    }

    if ($isValid) {
        // Update database
        $stmt = $conn->prepare("UPDATE student SET name = ?, email = ?, address = ?, class_id = ?, image = ? WHERE id = ?");
        $stmt->bind_param("sssisi", $name, $email, $address, $class_id, $image, $student_id);

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
    <title>Edit Student</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Edit Student</h1>
        <form method="post" action="edit.php?id=<?php echo $student_id; ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>">
                <span class="text-danger"><?php echo $nameErr; ?></span>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>">
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea class="form-control" id="address" name="address"><?php echo htmlspecialchars($student['address']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="class_id">Class</label>
                <select class="form-control" id="class_id" name="class_id">
                    <?php
                    if ($class_result->num_rows > 0) {
                        while($class_row = $class_result->fetch_assoc()) {
                            $selected = ($class_row['class_id'] == $student['class_id']) ? 'selected' : '';
                            echo "<option value='" . $class_row['class_id'] . "' $selected>" . $class_row['name'] . "</option>";
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
                <?php if (!empty($student['image'])): ?>
                    <p>Current Image: <img src="images/<?php echo htmlspecialchars($student['image']); ?>" alt="Student Image" style="max-width: 100px;"></p>
                <?php endif; ?>
                <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($student['image']); ?>">
                <span class="text-danger"><?php echo $imageErr; ?></span>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
        <a href="index.php" class="btn btn-secondary mt-3">Back to List</a>
    </div>
</body>
</html>
