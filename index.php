<?php
// Include the configuration file to establish database connection
include 'config.php';

// JOIN query to fetch students with their associated class names
$sql = "
    SELECT student.id, student.name, student.email, student.address, student.created_at, student.class_id, student.image, classes.name AS class_name 
    FROM student 
    JOIN classes ON student.class_id = classes.class_id
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List</title>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        img {
            width: 50px;
            height: 50px;
        }
        a {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <h1>Student List</h1>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Creation Date</th>
                <th>Class</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['class_name']) . "</td>";
                    echo "<td><img src='" . htmlspecialchars($row['image']) . "' alt='Student Image'></td>";
                    echo "<td>";
                    echo "<a href='view.php?id=" . htmlspecialchars($row['id']) . "'>View</a>";
                    echo "<a href='edit.php?id=" . htmlspecialchars($row['id']) . "'>Edit</a>";
                    echo "<a href='delete.php?id=" . htmlspecialchars($row['id']) . "' onclick='return confirm(\"Are you sure you want to delete this student?\")'>Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No students found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>

<?php
$conn->close();
?>
