<?php
    require '../includes/db.php';
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {
        die("Error: student_id is missing or invalid.");
    }

    $student_id = $_GET['student_id'];

    // Fetch student info
    $query = "SELECT * FROM students WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die("Error: No student found with this ID.");
    }

    $student = $result->fetch_assoc();

    // Prepare all available photos
    $all_photos = array();

    // Add cover photo
    if (!empty($student['cover_photo'])) {
        $all_photos[] = array(
            'path' => $student['cover_photo'],
            'label' => 'Cover Photo'
        );
    }

    // Add other photos
    $query = "SELECT * FROM photos WHERE student_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $photos_result = $stmt->get_result();

    $photo_counter = 1;
    while ($photo = $photos_result->fetch_assoc()) {
        $all_photos[] = array(
            'path' => $photo['photo_filename'],
            'label' => 'Photo ' . $photo_counter++
        );
    }

    // Define products and packages
    $individual_products = array(
        array('code' => '10x15', 'name' => 'Fénykép 10x15', 'price' => 2.5),
        array('code' => '20x30', 'name' => 'Vászonkép 20x30', 'price' => 60),
        array('code' => 'a4', 'name' => 'A4', 'price' => 10),
        array('code' => '30x40', 'name' => '30x40', 'price' => 85),
        array('code' => 'a3', 'name' => 'A3', 'price' => 25),
        array('code' => '40x60', 'name' => '40x60', 'price' => 135),
        array('code' => 'mug', 'name' => 'Fényképes bögre', 'price' => 30),
        array('code' => 'frame', 'name' => 'Képkeret', 'price' => 25),
        array('code' => 'card', 'name' => 'Üdvözlőkártya', 'price' => 6),
        array('code' => 'wood_magnet', 'name' => 'Fa hűtőmágnes', 'price' => 10),
        array('code' => 'photo_magnet', 'name' => 'Fényképes hűtőmágnes 10x15', 'price' => 8),
    );

    $packages = array(
        array(
            'id' => 1,
            'name' => 'Csomag 1',
            'description' => '2 darab 20x30 vászonkép + 2 darab bögre',
            'price' => 150,
            'components' => array(
                array('code' => '20x30', 'name' => '20x30 Vászonkép'),
                array('code' => 'mug', 'name' => 'Bögre')
            )
        ),
        array(
            'id' => 2,
            'name' => 'Csomag 2',
            'description' => '2 darab képkeret + 2 darab fa hűtőmágnes',
            'price' => 60,
            'components' => array(
                array('code' => 'frame', 'name' => 'Képkeret'),
                array('code' => 'wood_magnet', 'name' => 'Fa hűtőmágnes')
            )
        ),
        array(
            'id' => 3,
            'name' => 'Csomag 3',
            'description' => '2 darab A4 + 2 darab üdvözlőkártya',
            'price' => 27,
            'components' => array(
                array('code' => 'a4', 'name' => 'A4 Fénykép'),
                array('code' => 'card', 'name' => 'Üdvözlőkártya')
            )
        )
    );
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Form</title>
    <style>
        .product-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .photo-option { margin: 10px 0; }
        img.student-photo { max-width: 150px; margin: 5px; }
    </style>
</head>
<body>
    <h2>Student: <?= htmlspecialchars($student['name']) ?></h2>
    <img src="<?= htmlspecialchars($student['cover_photo']) ?>" alt="Cover Photo" style="max-width: 200px;">

    <h3>Available Photos:</h3>
    <div class="photos-container">
        <?php foreach ($all_photos as $photo): ?>
            <img src="<?= htmlspecialchars($photo['path']) ?>" 
                 alt="<?= htmlspecialchars($photo['label']) ?>" 
                 class="student-photo">
        <?php endforeach; ?>
    </div>

    <form action="process_order.php" method="POST" novalidate>
        <input type="hidden" name="student_id" value="<?= $student_id ?>">
        <div class="product-section">
            <h3>Individual Products</h3>
            <?php foreach ($individual_products as $product): ?>
                <div class="product">
                    <h4><?= htmlspecialchars($product['name']) ?> (<?= $product['price'] ?> RON)</h4>
                    <div class="photo-option">
                        <label>Select Photo:</label>
                        <select name="individual[<?= $product['code'] ?>][photo]" required>
                            <?php foreach ($all_photos as $photo): ?>
                                <option value="<?= htmlspecialchars($photo['path']) ?>">
                                    <?= htmlspecialchars($photo['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label>Quantity:</label>
                        <input type="number" name="individual[<?= $product['code'] ?>][quantity]" 
                               min="0" value="0" required>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Packages -->
        <div class="product-section">
            <h3>Packages</h3>
            <?php foreach ($packages as $package): ?>
                <div class="package">
                    <h4><?= htmlspecialchars($package['name']) ?> (<?= $package['price'] ?> RON)</h4>
                    <p><?= htmlspecialchars($package['description']) ?></p>
                    <?php foreach ($package['components'] as $component): ?>
                        <div class="photo-option">
                            <label><?= $component['name'] ?> Photo:</label>
                            <select name="packages[<?= $package['id'] ?>][<?= $component['code'] ?>][photo]" required>
                                <?php foreach ($all_photos as $photo): ?>
                                    <option value="<?= htmlspecialchars($photo['path']) ?>">
                                        <?= htmlspecialchars($photo['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                    <label>Quantity:</label>
                    <input type="number" name="packages[<?= $package['id'] ?>][quantity]" 
                           min="0" value="0" required>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit">Submit Order</button>
    </form>
</body>
</html>