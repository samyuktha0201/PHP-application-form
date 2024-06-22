<?php
include 'db.php';

// Get the student ID from the URL
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id > 0) {
    // Fetch student details
    $sql = "SELECT student.id, student.name, student.image 
            FROM student 
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Delete the student
    $stmt = $conn->prepare("DELETE FROM student WHERE id = ?");
    $stmt->bind_param("i", $student_id);

    if ($stmt->execute()) {
        // Delete the image file from the server
        if (!empty($student['image']) && file_exists('images/' . $student['image'])) {
            unlink('images/' . $student['image']);
        }
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Student</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">Delete Student</h1>
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($student['name']); ?></h5>
                <?php if (!empty($student['image'])): ?>
                    <p>Current Image: <img src="images/<?php echo htmlspecialchars($student['image']); ?>" alt="Student Image" style="max-width: 100px;"></p>
                <?php endif; ?>
                <p>Are you sure you want to delete this student?</p>
                <form method="post" action="delete.php?id=<?php echo $student_id; ?>">
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
