<?php
include('../includes/db_connect.php');
include('../includes/header.php');

$page_title = "Tour Packages - Gujarat Yatra Portal";
$city_filter = '';

// Check if a city filter is applied from the URL
if (isset($_GET['city_id'])) {
    $city_filter = intval($_GET['city_id']);
}

// Fetch all cities to populate the filter dropdown
$cities_sql = "SELECT * FROM cities ORDER BY city_name";
$cities_result = mysqli_query($conn, $cities_sql);

// Fetch packages based on the filter
$packages_sql = "SELECT p.*, c.city_name FROM packages p JOIN cities c ON p.city_id = c.city_id";
if ($city_filter) {
    $packages_sql .= " WHERE p.city_id = " . $city_filter;
}
$packages_sql .= " ORDER BY p.name ASC";
$packages_result = mysqli_query($conn, $packages_sql);
?>

<style>
    .packages-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('../assets/img/bg/slider3.webp');
        background-size: cover;
        background-position: center;
        padding: 100px 0;
        text-align: center;
        color: white;
    }

    .packages-hero h1 {
        font-size: 3rem;
        margin-bottom: 15px;
    }

    .packages-hero p {
        font-size: 1.2rem;
        max-width: 600px;
        margin: 0 auto;
    }

    .packages-section {
        padding: 50px 5%;
    }

    .filter-section {
        max-width: 500px;
        margin: 0 auto 50px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.07);
        display: flex;
        align-items: center;
        gap: 20px;
        justify-content: center;
    }

    .filter-section label {
        font-weight: 600;
        color: var(--text-primary);
    }

    .filter-section select {
        padding: 10px 15px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        font-size: 1rem;
        cursor: pointer;
        min-width: 200px;
    }
</style>

<section class="packages-hero">
    <div class="container">
        <h1>Our Tour Packages</h1>
        <p>Discover curated experiences that showcase the best of Gujarat's culture, heritage, and natural beauty.</p>
    </div>
</section>

<div class="bg-pattern">
    <section class="packages-section">
        <div class="container">
            <div class="filter-section">
                <label for="city-filter"><i class="fas fa-filter"></i> Filter by City:</label>
                <select id="city-filter" onchange="filterPackages()">
                    <option value="">All Cities</option>
                    <?php mysqli_data_seek($cities_result, 0); ?>
                    <?php while ($city_row = mysqli_fetch_assoc($cities_result)): ?>
                        <option value="<?php echo $city_row['city_id']; ?>" <?php echo ($city_filter == $city_row['city_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($city_row['city_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="results-grid">
                <?php if (mysqli_num_rows($packages_result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($packages_result)): ?>
                        <div class="result-card">
                            <div class="card-img-container">
                                <img src="../assets/img/packages/<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                            </div>
                            <div class="card-content">
                                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                                <p class="description">
                                    <strong><?php echo htmlspecialchars($row['duration_days']); ?> Days</strong> in <?php echo htmlspecialchars($row['city_name']); ?>
                                    <br>
                                    Starting from <strong>₹<?php echo number_format($row['price']); ?></strong>
                                </p>
                                <a href="package-detail.php?id=<?php echo $row['package_id']; ?>" class="btn">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; grid-column: 1 / -1;">No packages found for the selected city.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script>
    function filterPackages() {
        const cityId = document.getElementById('city-filter').value;
        // Construct the new URL and navigate to it
        let url = 'packages.php';
        if (cityId) {
            url += '?city_id=' + cityId;
        }
        window.location.href = url;
    }
</script>

<?php include '../includes/footer.php'; ?>