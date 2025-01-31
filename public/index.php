<?php
    include('../includes/db.php');

    $classes_query = "SELECT * FROM grades";
    $classes_result = mysqli_query($conn, $classes_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechnicAll Nőnap</title>
</head>
<body>
    <h1>Osztályok</h1>
    <div class="class-container">
        <?php 
        if (mysqli_num_rows($classes_result) > 0) {
            while ($class = mysqli_fetch_assoc($classes_result)) {
                echo "<div class='class-card'>";
                echo "<a href='grade.php?class_id=" . urlencode($class['id']) . "'>" . $class['grade_name'] . " - " . $class['location'] . "</a>";
                echo "</div>";
            }
        } else {
            echo "Nincsenek osztályok!";
        }
        ?>
        <a href="grade.php">SAD</a>
    </div>
</body>
</html>