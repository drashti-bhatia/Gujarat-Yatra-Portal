<?php
include('../includes/db_connect.php');
include('../includes/header.php');

// Check if a city ID is passed in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // If no ID is provided, redirect to the main cities page
    header("Location: cities.php");
    exit();
}

// Sanitize the input to prevent SQL injection
$city_id = intval($_GET['id']);

// Use a prepared statement to fetch city details securely
$city_sql = "SELECT * FROM cities WHERE city_id = ?";
$stmt_city = mysqli_prepare($conn, $city_sql);
mysqli_stmt_bind_param($stmt_city, "i", $city_id);
mysqli_stmt_execute($stmt_city);
$city_result = mysqli_stmt_get_result($stmt_city);
$city = mysqli_fetch_assoc($city_result);

// If no city is found with the given ID, redirect back
if (!$city) {
    header("Location: cities.php");
    exit();
}

// Fetch attractions for this city using a prepared statement
$attractions_sql = "SELECT * FROM attractions WHERE city_id = ?";
$stmt_attractions = mysqli_prepare($conn, $attractions_sql);
mysqli_stmt_bind_param($stmt_attractions, "i", $city_id);
mysqli_stmt_execute($stmt_attractions);
$attractions_result = mysqli_stmt_get_result($stmt_attractions);

$page_title = htmlspecialchars($city['city_name']) . " - Gujarat Yatra Portal";

// Fetch transport options for the city
$transport_sql = "SELECT t.*, dc.city_name as departure_city_name, ac.city_name as arrival_city_name 
                  FROM transport_options t
                  JOIN cities dc ON t.departure_city_id = dc.city_id
                  JOIN cities ac ON t.arrival_city_id = ac.city_id
                  WHERE t.departure_city_id = ? OR t.arrival_city_id = ?";
$stmt_transport = mysqli_prepare($conn, $transport_sql);
mysqli_stmt_bind_param($stmt_transport, "ii", $city_id, $city_id);
mysqli_stmt_execute($stmt_transport);
$transport_result = mysqli_stmt_get_result($stmt_transport);

?>

<style>
    .city-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('../assets/img/cities/<?php echo htmlspecialchars($city['image_url']); ?>');
        background-size: cover;
        background-position: center;
        padding: 100px 0;
        text-align: center;
        color: white;
    }

    .city-hero h1 {
        font-size: 3.5rem;
        margin-bottom: 10px;
    }

    .city-hero p {
        font-size: 1.2rem;
    }

    .city-section {
        padding: 50px 5%;
        background-color: transparent;
    }

    /* NEW: Styling for the main description container */
    .city-description-card {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 10px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid #EAEAEA;
        padding: 40px;
        margin-bottom: 50px;
        text-align: center;
    }

    .city-description-card h2 {
        font-size: 2.5rem;
        margin-bottom: 20px;
        color: #333;
    }

    .city-description-card p {
        font-size: 1.1rem;
        line-height: 1.8;
        color: #555;
        max-width: 800px;
        /* Constrain text width for readability */
        margin: 0 auto 25px auto;
        /* Center the paragraph */
    }

    /* NEW: Info items for 'Best Time' and 'Duration' */
    .info-items {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-top: 20px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.1rem;
        background: #f8f9fa;
        padding: 10px 20px;
        border-radius: 50px;
    }

    .info-item i {
        color: var(--orange);
    }

    .info-item strong {
        color: #333;
    }

    /* Standardized Card Styling */
    .attraction-card,
    .transport-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 10px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid #EAEAEA;
        transition: transform 0.3s ease;
    }

    .attraction-card:hover,
    .transport-card:hover {
        transform: translateY(-5px);
    }

    .attractions-list-section,
    .transport-options-section {
        padding-top: 30px;
    }

    .attractions-grid,
    .transport-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 30px;
    }

    .attraction-card {
        overflow: hidden;
    }

    .attraction-image {
        width: 100%;
        height: 220px;
        object-fit: cover;
    }

    .attraction-content {
        padding: 25px;
    }

    .attraction-content h3 {
        font-size: 1.5rem;
        margin-bottom: 10px;
        color: #333;
    }

    .attraction-content p {
        font-size: 1rem;
        color: #666;
        margin-bottom: 15px;
        line-height: 1.6;
    }

    .attraction-details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        font-size: 0.9rem;
        color: #555;
    }

    .attraction-details .fee {
        font-weight: 600;
        color: var(--darkblue);
    }

    .transport-card {
        padding: 25px;
    }

    .transport-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #EAEAEA;
    }

    .transport-icon {
        font-size: 1.5rem;
        color: white;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .transport-header h4 {
        margin: 0;
        font-size: 1.2rem;
        flex-grow: 1;
    }

    .transport-price {
        font-size: 1.2rem;
        font-weight: bold;
        color: var(--orange);
    }

    .icon-bus {
        background-color: #28a745;
    }

    .icon-train {
        background-color: #007bff;
    }

    .icon-flight {
        background-color: #ffc107;
    }

    .icon-car {
        background-color: #6c757d;
    }

    .transport-details p {
        margin-bottom: 8px;
        color: #555;
        font-size: 0.95rem;
    }
</style>

<section class="city-hero">
    <div class="container">
        <h1><?php echo htmlspecialchars($city['city_name']); ?></h1>
        <p>Your guide to the wonders of <?php echo htmlspecialchars($city['city_name']); ?></p>
    </div>
</section>

<div class="bg-pattern">
    <section class="city-section">
        <div class="container">
            <div class="city-description-card">
                <h2>About <?php echo htmlspecialchars($city['city_name']); ?></h2>
                <p><?php echo htmlspecialchars($city['description']); ?></p>
                <div class="info-items">
                    <div class="info-item">
                        <i class="fas fa-sun"></i>
                        <strong>Best Time to Visit:</strong>
                        <span><?php echo htmlspecialchars($city['best_time_to_visit']); ?></span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <strong>Ideal Duration:</strong>
                        <span>3-5 days</span>
                    </div>
                </div>
                <a href="packages.php?city_id=<?php echo $city['city_id']; ?>" class="btn">View Packages</a>
            </div>

            <div class="attractions-list-section">
                <h2 class="section-title">Top Attractions in <?php echo htmlspecialchars($city['city_name']); ?></h2>
                <div class="attractions-grid">
                    <?php if (mysqli_num_rows($attractions_result) > 0): ?>
                        <?php while ($attraction = mysqli_fetch_assoc($attractions_result)): ?>
                            <div class="attraction-card">
                                <img src="../assets/img/attractions/<?php echo htmlspecialchars($attraction['image_url']); ?>" alt="<?php echo htmlspecialchars($attraction['name']); ?>" class="attraction-image">
                                <div class="attraction-content">
                                    <h3><?php echo htmlspecialchars($attraction['name']); ?></h3>
                                    <p><?php echo htmlspecialchars(substr($attraction['description'], 0, 120)); ?>...</p>
                                    <div class="attraction-details">
                                        <span class="fee">Fee: ₹<?php echo htmlspecialchars($attraction['entry_fee']); ?></span>
                                        <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($attraction['opening_hours']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; width: 100%;">No attractions found for this city.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="transport-options-section">
                <h2 class="section-title">Transport Options</h2>
                <div class="transport-grid">
                    <?php if (mysqli_num_rows($transport_result) > 0): ?>
                        <?php while ($transport = mysqli_fetch_assoc($transport_result)):
                            $icon_class = 'fa-question-circle';
                            $icon_bg = 'icon-car';
                            if ($transport['transport_type'] == 'bus') {
                                $icon_class = 'fa-bus';
                                $icon_bg = 'icon-bus';
                            }
                            if ($transport['transport_type'] == 'train') {
                                $icon_class = 'fa-train';
                                $icon_bg = 'icon-train';
                            }
                            if ($transport['transport_type'] == 'flight') {
                                $icon_class = 'fa-plane';
                                $icon_bg = 'icon-flight';
                            }
                            if ($transport['transport_type'] == 'car') {
                                $icon_class = 'fa-car';
                                $icon_bg = 'icon-car';
                            }
                        ?>
                            <div class="transport-card">
                                <div class="transport-header">
                                    <div class="transport-icon <?php echo $icon_bg; ?>">
                                        <i class="fas <?php echo $icon_class; ?>"></i>
                                    </div>
                                    <h4><?php echo ucfirst($transport['transport_type']); ?></h4>
                                    <span class="transport-price">₹<?php echo htmlspecialchars($transport['approx_price']); ?></span>
                                </div>
                                <div class="transport-details">
                                    <p><strong>From:</strong> <?php echo htmlspecialchars($transport['departure_city_name']); ?> at <?php echo date('h:i A', strtotime($transport['departure_time'])); ?></p>
                                    <p><strong>To:</strong> <?php echo htmlspecialchars($transport['arrival_city_name']); ?> at <?php echo date('h:i A', strtotime($transport['arrival_time'])); ?></p>
                                    <p><?php echo htmlspecialchars($transport['description']); ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="text-align: center; width: 100%;">No transport options found for this city.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include "../includes/footer.php"; ?>