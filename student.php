<?php
    require 'includes/db.php';
    
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
    header("Pragma: no-cache");

    if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {
        die("Hiba: diák nem létezik vagy invalid!");
    }

    $student_id = $_GET['student_id'];

    // Fetch student info
    $query = "SELECT * FROM students WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die("Hiba: Ilyen diák nem létezik!");
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
            'path' => $photo['path'],
            'label' => 'Fénykép ' . $photo_counter++
        );
    }

    // Define products and packages
    $individual_products = array(
        array('code' => 'fenykep_10x15', 'name' => 'Fénykép - 10x15', 'price' => 2.5),
        array('code' => 'fenykep_a4', 'name' => 'Fénykép - A4', 'price' => 10),
        array('code' => 'fenykep_a3', 'name' => 'Fénykép - A3', 'price' => 25),
        array('code' => 'vaszon_20x30', 'name' => 'Vászonkép - 20x30', 'price' => 60),
        array('code' => 'vaszon_30x40', 'name' => 'Vászonkép - 30x40', 'price' => 85),
        array('code' => 'vaszon_40x60', 'name' => 'Vászonkép - 40x60', 'price' => 135),
        array('code' => 'bogre', 'name' => 'Fényképes bögre', 'price' => 30),
        array('code' => 'kepkeret', 'name' => 'Képkeret', 'price' => 25),
        array('code' => 'udvozlokartya', 'name' => 'Üdvözlőkártya', 'price' => 6),
        array('code' => 'fa_hutomagnes', 'name' => 'Fa hűtőmágnes', 'price' => 10),
        array('code' => 'fenykepes_hutomagnes', 'name' => 'Fényképes hűtőmágnes 10x15', 'price' => 8),
    );

    $packages = array(
        array(
            'id' => 1,
            'name' => 'Csomag 1',
            'description' => '2 darab 20x30 vászonkép + 2 darab bögre',
            'price' => 150,
            'components' => array(
                array('code' => 'vaszon_20x30_1', 'name' => 'Vászonkép 1'),
                array('code' => 'vaszon_20x30_2', 'name' => 'Vászonkép 2'),
                array('code' => 'bogre_1', 'name' => 'Bögre 1'),
                array('code' => 'bogre_2', 'name' => 'Bögre 2')
            )
        ),
        array(
            'id' => 2,
            'name' => 'Csomag 2',
            'description' => '2 darab képkeret + 2 darab fa hűtőmágnes',
            'price' => 60,
            'components' => array(
                array('code' => 'kepkeret_1', 'name' => 'Képkeret 1'),
                array('code' => 'kepkeret_2', 'name' => 'Képkeret 2'),
                array('code' => 'fa_hutomagnes_1', 'name' => 'Fa hűtőmágnes 1'),
                array('code' => 'fa_hutomagnes_2', 'name' => 'Fa hűtőmágnes 2')
            )
        ),
        array(
            'id' => 3,
            'name' => 'Csomag 3',
            'description' => '2 darab A4 fénykép + 2 darab üdvözlőkártya',
            'price' => 27,
            'components' => array(
                array('code' => 'fenykep_a4_1', 'name' => 'Fénykép 1'),
                array('code' => 'fenykep_a4_2', 'name' => 'Fénykép 2'),
                array('code' => 'udvozlokartya_1', 'name' => 'Üdvözlőkártya 1'),
                array('code' => 'udvozlokartya_2', 'name' => 'Üdvözlőkártya 2')
            )
        ),
        array(
            'id' => 4,
            'name' => 'Csomag 4',
            'description' => '2 darab bögre + 2 darab képkeret + 2 darab AJÁNDÉK 10x15 fénykép',
            'price' => 99,
            'components' => array(
                array('code' => 'bogre_1', 'name' => 'Bögre 1'),
                array('code' => 'bogre_2', 'name' => 'Bögre 2'),
                array('code' => 'kepkeret_1', 'name' => 'Képkeret 1'),
                array('code' => 'kepkeret_2', 'name' => 'Képkeret 2'),
                array('code' => 'ajandek_fenykep_1', 'name' => 'Fénykép 1'),
                array('code' => 'ajandek_fenykep_2', 'name' => 'Fénykép 2'),
            )
        )
    );
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rendelés</title>
    <link rel="stylesheet" href="/assets/student.css">
</head>
<body>
    <h2><?= htmlspecialchars($student['name']) ?></h2>
    <h3>Választható képek:</h3>
    <div class="photos-container">
        <?php foreach ($all_photos as $photo): ?>
            <div class="photo-card">
                <img src="<?= htmlspecialchars($photo['path']) ?>" 
                    alt="<?= htmlspecialchars($photo['label']) ?>" 
                    class="student-photo">
                <p class="photo-num"><?php echo htmlspecialchars($photo['label']); ?></p>
                <a href="scripts/download.php?file=<?php echo $photo['path']?>" class="download-btn">Letöltés</a>
            </div>
        <?php endforeach; ?>
    </div>

    <form action="/scripts/process_order.php" method="POST" id="orderForm" novalidate>
        <input type="hidden" name="student_id" value="<?= $student_id ?>">
        <div class="product-section">
            <h3>Egyedülálló termékek</h3>
            <?php foreach ($individual_products as $product): ?>
                <div id="product-<?= $product['code'] ?>" class="product">
                    <h4><?= htmlspecialchars($product['name']) ?> (<?= $product['price'] ?> RON)</h4>
                    <div class="photo-option">
                        <label>Válassz képet:</label>
                        <select name="individual[<?= $product['code'] ?>][photo][]">
                            <?php foreach ($all_photos as $photo): ?>
                                <option value="<?= htmlspecialchars($photo['path']) ?>">
                                    <?= htmlspecialchars($photo['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label>Mennyiség:</label>
                        <input type="number" name="individual[<?= $product['code'] ?>][quantity][]" min="0" value="0" class="product-quantity"/>
                        <button type="button" onclick="addPhotoOption('<?= $product['code'] ?>')">+</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Packages -->
        <div class="product-section">
            <h3>Csomagok</h3>
            <?php foreach ($packages as $package): ?>
                <div class="package">
                    <h4><?= htmlspecialchars($package['name']) ?> (<?= $package['price'] ?> RON)</h4>
                    <p><i><?= htmlspecialchars($package['description']) ?></i></p>
                    <input type="hidden" name="packages[<?= $package['id'] ?>][description]" value="<?= htmlspecialchars($package['description']) ?>">
                    <?php foreach ($package['components'] as $component): ?>
                        <div class="photo-option">
                            <label><?= $component['name'] ?>:</label>
                            <select name="packages[<?= $package['id'] ?>][<?= $component['code'] ?>][photo]">
                                <?php foreach ($all_photos as $photo): ?>
                                    <option value="<?= htmlspecialchars($photo['path']) ?>">
                                        <?= htmlspecialchars($photo['label']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endforeach; ?>
                    <label>Mennyiség (1 csomag):</label>
                    <input type="number" name="packages[<?= $package['id'] ?>][quantity]" 
                           min="0" value="0" class="package-quantity">
                </div>
            <?php endforeach; ?>
        </div>
        <div class="product-section">
            <h3>Elérhetőség</h3>
            <label for="phone_number">Telefonszám:</label>
            <input type="tel" name="phone_number" id="phone_number" 
                placeholder="0712345678" 
                pattern="^0\d{9}$" 
                required>
        </div>
        <button type="submit">Rendelés</button>
        <script>
            document.getElementById("orderForm").addEventListener("submit", function(event) {
                const phoneInput = document.getElementById("phone_number");
                const productInputs = document.querySelectorAll(".product-quantity");
                const packageInputs = document.querySelectorAll(".package-quantity");
                
                let hasOrder = false;
                
                // Check if at least one individual product or package is selected (quantity > 0)
                productInputs.forEach(input => {
                    if (parseInt(input.value) > 0) {
                        hasOrder = true;
                    }
                });
                
                packageInputs.forEach(input => {
                    if (parseInt(input.value) > 0) {
                        hasOrder = true;
                    }
                });

                // Check if the phone number is valid
                if (!phoneInput.value.match(/^0\d{9}$/)) {
                    alert("Hiba: Kérlek adj meg egy érvényes telefonszámot (pl. 0712345678)!");
                    event.preventDefault();
                    return;
                }

                // Check if at least one product or package is selected
                if (!hasOrder) {
                    alert("Hiba: Kérlek válassz legalább egy terméket vagy csomagot!");
                    event.preventDefault();
                    return;
                }
            });

            function addPhotoOption(productCode) {
                const productSection = document.getElementById(`product-${productCode}`);

                if (!productSection) {
                    console.error(`Hiba: Nem található 'product-${productCode}' ID-jű elem!`);
                    return;
                }
                
                // Get existing input count to ensure unique names
                const existingInputs = productSection.querySelectorAll('input[type="number"]');
                const index = existingInputs.length;

                const newPhotoOption = document.createElement("div");
                newPhotoOption.classList.add("photo-option");
                newPhotoOption.innerHTML = `
                    <label>Válassz képet:</label>
                    <select name="individual[${productCode}][photo][]">
                        <?php foreach ($all_photos as $photo): ?>
                            <option value="<?= htmlspecialchars($photo['path']) ?>">
                                <?= htmlspecialchars($photo['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label>Mennyiség:</label>
                    <input type="number" name="individual[${productCode}][quantity][]" min="0" value="1" class="product-quantity"/>
                    <button class="rmv-btn" type="button" onclick="this.parentElement.remove()">-</button>
                `;

                productSection.appendChild(newPhotoOption);
            }
        </script>
    </form>
</body>
</html>