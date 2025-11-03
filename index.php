<?php
include 'includes/db_connect.php';
include 'includes/header.php';
?>
<div class="bg-pattern">
    <section class="hero" style="padding-top: 25px;">
        <div class="slider-container">
            <div class="slider-wrapper">
                <div class="slide active" style="background-image: url('assets/img/bg/slider1.webp')"></div>
                <div class="slide" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('assets/img/bg/slider2.webp')">
                    <div class="slide-content">
                        <h2>Statue of Unity - A Symbol of Unity</h2>
                        <p>Explore the world's tallest statue, a tribute to Sardar Vallabhbhai Patel.</p>
                    </div>
                </div>
                <div class="slide" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('assets/img/bg/slider3.webp')">
                    <div class="slide-content">
                        <h2>Rann of Kutch - A White Desert Wonderland</h2>
                        <p>A mesmerizing white salt desert that hosts the vibrant Rann Utsav festival.</p>
                    </div>
                </div>
                <div class="slide" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('assets/img/bg/slider4.webp')">
                    <div class="slide-content">
                        <h2>Modhera Sun Temple - An Architectural Marvel</h2>
                        <p>An 11th-century temple celebrated for its intricate carvings and grand stepwell.</p>
                    </div>
                </div>
                <div class="slide" style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('assets/img/bg/slider5.webp')">
                    <div class="slide-content">
                        <h2>Adalaj Stepwell - A Water Building Marvel</h2>
                        <p>A magnificent five-story deep stepwell with a beautiful fusion of Hindu and Islamic architecture.</p>
                    </div>
                </div>
            </div>
            <button class="arrow prev" onclick="changeSlide(-1)"><span class="material-symbols-rounded">arrow_back</span></button>
            <button class="arrow next" onclick="changeSlide(1)"><span class="material-symbols-rounded">arrow_forward</span></button>
            <div class="navigation">
                <div class="nav-dot active" onclick="currentSlide(1)"></div>
                <div class="nav-dot" onclick="currentSlide(2)"></div>
                <div class="nav-dot" onclick="currentSlide(3)"></div>
                <div class="nav-dot" onclick="currentSlide(4)"></div>
                <div class="nav-dot" onclick="currentSlide(5)"></div>
            </div>
            <div class="progress-bar" id="progressBar"></div>
        </div>
    </section>
    <script src="assets/js/slider.js"></script>



    <section class="home-section">
        <div class="container">
            <div class="home-section-header">
                <h2 class="section-title">Popular Destinations</h2>
                <a href="pages/cities.php" class="view-all-link">View All</a>
            </div>
            <div class="results-grid">
                <?php
                $sql = "SELECT * FROM cities ORDER BY city_id LIMIT 3";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)):
                ?>
                    <div class="result-card">
                        <div class="card-img-container">
                            <img src="assets/img/cities/<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['city_name']); ?>">
                        </div>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($row['city_name']); ?></h3>
                            <p class="description"><?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?>...</p>
                            <a href="pages/city-detail.php?id=<?php echo $row['city_id']; ?>" class="btn">Explore</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <section class="home-section">
        <div class="container">
            <div class="home-section-header">
                <h2 class="section-title">Featured Packages</h2>
                <a href="pages/packages.php" class="view-all-link">View All</a>
            </div>
            <div class="results-grid">
                <?php
                $sql = "SELECT * FROM packages ORDER BY package_id DESC LIMIT 3";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)):
                ?>
                    <div class="result-card">
                        <div class="card-img-container">
                            <img src="assets/img/packages/<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        </div>
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p class="description"><strong><?php echo htmlspecialchars($row['duration_days']); ?> Days</strong> starting from <strong>₹<?php echo number_format($row['price']); ?></strong> per person.</p>
                            <a href="pages/package-detail.php?id=<?php echo $row['package_id']; ?>" class="btn">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <section class="home-section">
        <div class="container">
            <div class="home-section-header">
                <h2 class="section-title">What Our Travelers Say</h2>
                <a href="pages/reviews.php" class="view-all-link">View All</a>
            </div>
            <div class="testimonial-grid">
                <?php
                $sql = "SELECT r.*, u.username FROM reviews r JOIN users u ON r.user_id = u.user_id WHERE r.status = 'approved' ORDER BY r.date_posted DESC LIMIT 3";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)):
                    $stars = str_repeat('★', $row['rating']) . str_repeat('☆', 5 - $row['rating']);
                ?>
                    <div class="testimonial-card">
                        <div class="card-content">
                            <p class="comment">"<?php echo htmlspecialchars($row['comment']); ?>"</p>
                        </div>
                        <div class="author-info">
                            <div class="author-avatar"><?php echo strtoupper(substr($row['username'], 0, 1)); ?></div>
                            <div class="author-details">
                                <h4><?php echo htmlspecialchars($row['username']); ?></h4>
                                <div class="rating-stars"><?php echo $stars; ?></div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

</div>

<?php include "includes/footer.php"; ?>