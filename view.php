<?php
include 'db.php';

// Get the student ID from the URL
$student_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($student_id > 0) {
    // Fetch student details with class name
    $sql = "SELECT student.name, student.email, student.address, student.created_at, student.image, classes.name as class_name 
            FROM student 
            JOIN classes ON student.class_id = classes.class_id 
            WHERE student.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
    } else {
        echo "Student not found.";
        exit;
    }

    $stmt->close();
} else {
    echo "Invalid student ID.";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Student</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">View Student</h1>
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                <p class="card-text"><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($row['address'])); ?></p>
                <p class="card-text"><strong>Class:</strong> <?php echo htmlspecialchars($row['class_name']); ?></p>
                <p class="card-text"><strong>Created At:</strong> <?php echo htmlspecialchars($row['created_at']); ?></p>
                <?php if (!empty($row['image'])): ?>
                    <p class="card-text"><strong>Image:</strong></p>
                    <img src="images/<?php echo htmlspecialchars($row['image']); ?>" alt="Student Image" class="img-fluid" style="max-width: 200px;">
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="index.php" class="btn btn-primary">Back to List</a>
            </div>
        </div>
    </div>
</body>
</html>
