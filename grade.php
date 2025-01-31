<?php
    include('includes/db.php');

    if (!isset($_GET['class_id']) || empty($_GET['class_id'])) {
        die("Error: class_id is missing or invalid.");
    }

    $class_id = $_GET['class_id'];

    $query_class = "SELECT * FROM grades WHERE id = $class_id";
    $result_class = mysqli_query($conn, $query_class);
    $class = mysqli_fetch_assoc($result_class);

    if (!$class) {
        die("Error: Class not found.");
    }

    $query_kids = "SELECT * FROM students WHERE grade_id = $class_id";
    $result_kids = mysqli_query($conn, $query_kids);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Osztály: <?php echo $class['grade_name']; ?></title>
</head>
<body>
    <h1>Tanulók</h1>
    <div class="kids-container">
    <?php
        if (mysqli_num_rows($result_kids) > 0) {
            while ($kid = mysqli_fetch_assoc($result_kids)) {
                echo "<div class='kid-card'>";
                echo "<a href='student.php?student_id=" . $kid['id'] . "'>";
                if ($kid['cover_photo']) {
                    echo "<img src='" . $kid['cover_photo'] . "' alt='Kid Photo'>";
                }
                echo "<h3>" . $kid['name'] . "</h3>";
                echo "</a>";
                echo "</div>";
            }
        } else {
            echo "No students found in this class.";
        }
        ?>
    </div>
</body>
</html>