<?php
include('../includes/db_connect.php');
include('../includes/header.php');

$search_query = '';
if (isset($_GET['query'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['query']);
}

$page_title = "Search Results for '" . htmlspecialchars($search_query) . "'";

// --- Search Queries ---
$cities_sql = "SELECT * FROM cities WHERE city_name LIKE '%$search_query%' OR description LIKE '%$search_query%'";
$cities_result = mysqli_query($conn, $cities_sql);

$packages_sql = "SELECT p.*, c.city_name FROM packages p JOIN cities c ON p.city_id = c.city_id WHERE p.name LIKE '%$search_query%' OR p.description LIKE '%$search_query%' OR p.itinerary LIKE '%$search_query%'";
$packages_result = mysqli_query($conn, $packages_sql);

$attractions_sql = "SELECT a.*, c.city_name FROM attractions a JOIN cities c ON a.city_id = c.city_id WHERE a.name LIKE '%$search_query%' OR a.description LIKE '%$search_query%'";
$attractions_result = mysqli_query($conn, $attractions_sql);

$total_results = 0;
if ($cities_result) $total_results += mysqli_num_rows($cities_result);
if ($packages_result) $total_results += mysqli_num_rows($packages_result);
if ($attractions_result) $total_results += mysqli_num_rows($attractions_result);
?>

<style>
    .search-hero {
        padding: 80px 0;
        text-align: center;
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .search-hero h1 {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }

    .search-hero p {
        font-size: 1.2rem;
        color: #6c757d;
    }

    .results-section {
        padding: 50px 5%;
    }

    .no-results {
        text-align: center;
        padding: 50px;
        background: #fff;
        border-radius: 15px;
        border: 1px solid #EAEAEA;
    }
</style>

<section class="search-hero">
    <div class="container">
        <h1>Search Results</h1>
        <p>Found <?php echo $total_results; ?> results for "<strong><?php echo htmlspecialchars($search_query); ?></strong>"</p>
    </div>
</section>

<div class="bg-pattern">
    <section class="results-section">
        <div class="container">
            <?php if ($total_results > 0): ?>
                <div class="results-grid">
                    <?php if ($cities_result) while ($city = mysqli_fetch_assoc($cities_result)): ?>
                        <div class="result-card">
                            <div class="card-img-container">
                                <img src="../assets/img/cities/<?php echo htmlspecialchars($city['image_url']); ?>" alt="<?php echo htmlspecialchars($city['city_name']); ?>">
                                <span class="item-type-badge">City</span>
                            </div>
                            <div class="card-content">
                                <h3><?php echo htmlspecialchars($city['city_name']); ?></h3>
                                <p class="description"><?php echo htmlspecialchars(substr($city['description'], 0, 100)); ?>...</p>
                                <a href="city-detail.php?id=<?php echo $city['city_id']; ?>" class="btn" style="margin: auto;">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>

                    <?php if ($packages_result) while ($package = mysqli_fetch_assoc($packages_result)): ?>
                        <div class="result-card">
                            <div class="card-img-container">
                                <img src="../assets/img/packages/<?php echo htmlspecialchars($package['image_url']); ?>" alt="<?php echo htmlspecialchars($package['name']); ?>">
                                <span class="item-type-badge">Package</span>
                            </div>
                            <div class="card-content">
                                <h3><?php echo htmlspecialchars($package['name']); ?></h3>
                                <p class="description"><?php echo htmlspecialchars(substr($package['description'], 0, 100)); ?>...</p>
                                <a href="package-detail.php?id=<?php echo $package['package_id']; ?>" class="btn" style="margin: auto;" ;>View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>

                    <?php if ($attractions_result) while ($attraction = mysqli_fetch_assoc($attractions_result)): ?>
                        <div class="result-card">
                            <div class="card-img-container">
                                <img src="../assets/img/attractions/<?php echo htmlspecialchars($attraction['image_url']); ?>" alt="<?php echo htmlspecialchars($attraction['name']); ?>">
                                <span class="item-type-badge">Attraction</span>
                            </div>
                            <div class="card-content">
                                <h3><?php echo htmlspecialchars($attraction['name']); ?></h3>
                                <p class="description"><?php echo htmlspecialchars(substr($attraction['description'], 0, 100)); ?>...</p>
                                <a href="city-detail.php?id=<?php echo $attraction['city_id']; ?>" class="btn">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <h2>No Results Found</h2>
                    <p>We couldn't find anything matching your search. Please try a different keyword.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include('../includes/footer.php'); ?>