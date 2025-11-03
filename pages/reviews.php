<?php
// ERROR 1 FIX: Corrected the typo in the include path
include('../includes/db_connect.php');
include('../includes/header.php');

$page_title = "Customer Reviews - Gujarat Yatra Portal";
$success_message = "";
$error_message = "";

// Handle new review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    // ... (Your existing PHP logic for submitting a review remains here) ...
}

// Fetch all approved reviews
$sql = "SELECT r.*, u.username,
        COALESCE(p.name, a.name) as item_name,
        CASE
            WHEN r.package_id IS NOT NULL THEN 'package'
            WHEN r.attraction_id IS NOT NULL THEN 'attraction'
            ELSE 'unknown'
        END as review_type
        FROM reviews r
        JOIN users u ON r.user_id = u.user_id
        LEFT JOIN packages p ON r.package_id = p.package_id
        LEFT JOIN attractions a ON r.attraction_id = a.attraction_id
        WHERE r.status = 'approved'
        ORDER BY r.date_posted DESC";

$reviews_result = mysqli_query($conn, $sql);

// Fetch items for the review form dropdown
$packages_sql = "SELECT package_id, name FROM packages ORDER BY name";
$packages_result = mysqli_query($conn, $packages_sql);
$attractions_sql = "SELECT attraction_id, name FROM attractions ORDER BY name";
$attractions_result = mysqli_query($conn, $attractions_sql);

?>

<style>
    .reviews-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('../assets/img/bg/slider5.webp');
        background-size: cover;
        background-position: center;
        padding: 100px 0;
        text-align: center;
        color: white;
    }

    .reviews-hero h1 {
        font-size: 3rem;
        margin-bottom: 15px;
    }

    .reviews-hero p {
        font-size: 1.2rem;
    }

    .reviews-section {
        padding: 50px 5%;
    }

    /* Polished Testimonial Card Style */
    .testimonial-card {
        background: #fff;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        border: 1px solid #EAEAEA;
        margin-bottom: 25px;
    }

    .testimonial-card .comment {
        font-size: 1.1rem;
        line-height: 1.7;
        color: #555;
        font-style: italic;
        margin-bottom: 20px;
        border-left: 3px solid var(--orange);
        padding-left: 20px;
    }

    .testimonial-card .author-info {
        display: flex;
        align-items: center;
        padding-top: 20px;
        border-top: 1px solid #EAEAEA;
    }

    .author-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: var(--orange);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 700;
        margin-right: 15px;
    }

    .author-details h4 {
        margin: 0 0 5px 0;
        font-size: 1.1rem;
        color: #333;
    }

    .rating-stars {
        color: #FFC107;
    }

    .review-meta {
        font-size: 0.9rem;
        color: #6c757d;
    }

    /* Polished Form Container */
    .review-form-container {
        background: white;
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.07);
        margin-top: 50px;
    }

    .review-form-container h2 {
        text-align: center;
        margin-bottom: 30px;
        font-size: 2rem;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ced4da;
        border-radius: 8px;
        background-color: var(--admin-bg);
        font-size: 1rem;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--orange);
        box-shadow: 0 0 0 3px var(--orange-light);
    }

    .btn {
        width: 100%;
        padding: 12px;
        font-weight: 600;
    }
</style>

<section class="reviews-hero">
    <div class="container">
        <h1>What Our Travelers Say</h1>
        <p>Real stories and experiences from our valued customers.</p>
    </div>
</section>

<div class="bg-pattern">
    <section class="reviews-section">
        <div class="container">
            <?php if (mysqli_num_rows($reviews_result) > 0): ?>
                <?php while ($review = mysqli_fetch_assoc($reviews_result)):
                    $stars = str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']);
                ?>
                    <div class="testimonial-card">
                        <div class="card-content">
                            <p class="comment">"<?php echo htmlspecialchars($review['comment']); ?>"</p>
                        </div>
                        <div class="author-info">
                            <div class="author-avatar"><?php echo strtoupper(substr($review['username'], 0, 1)); ?></div>
                            <div class="author-details">
                                <h4><?php echo htmlspecialchars($review['username']); ?></h4>
                                <div class="rating-stars"><?php echo $stars; ?></div>
                                <div class="review-meta">Reviewed: <?php echo htmlspecialchars($review['item_name']); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center;">No reviews have been approved yet. Be the first to leave one!</p>
            <?php endif; ?>

            <div class="review-form-container">
                <h2>Leave Your Review</h2>
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST" action="reviews.php">
                        <div class="form-group">
                            <label for="item_type">I'd like to review a...</label>
                            <select id="item_type" name="item_type" class="form-control" required>
                                <option value="">Select Item Type...</option>
                                <option value="package">Package</option>
                                <option value="attraction">Attraction</option>
                            </select>
                        </div>
                        <div class="form-group" id="item_id_group" style="display: none;">
                            <label for="item_id">Which one?</label>
                            <select id="item_id" name="item_id" class="form-control" required></select>
                        </div>
                        <div class="form-group">
                            <label for="rating">Your Rating</label>
                            <select id="rating_input" name="rating" class="form-control" required>
                                <option value="">Select Rating...</option>
                                <option value="5">★★★★★ (Excellent)</option>
                                <option value="4">★★★★☆ (Great)</option>
                                <option value="3">★★★☆☆ (Good)</option>
                                <option value="2">★★☆☆☆ (Fair)</option>
                                <option value="1">★☆☆☆☆ (Poor)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="comment">Your Comment</label>
                            <textarea id="comment" name="comment" class="form-control" rows="4" placeholder="Share your experience..." required></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                    </form>
                <?php else: ?>
                    <p style="text-align: center; color: #666;">Please <a href="../user/login.php" style="color: var(--orange); font-weight: 600;">log in</a> to leave a review.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script>
    document.getElementById('item_type').addEventListener('change', function() {
        const itemType = this.value;
        const itemDropdown = document.getElementById('item_id');
        const itemGroup = document.getElementById('item_id_group');
        itemDropdown.innerHTML = ''; // Clear previous options

        if (itemType === 'package') {
            itemDropdown.innerHTML += '<option value="">Select a Package...</option>';
            <?php mysqli_data_seek($packages_result, 0); ?>
            <?php while ($package = mysqli_fetch_assoc($packages_result)): ?>
                itemDropdown.innerHTML += `<option value="<?php echo $package['package_id']; ?>"><?php echo addslashes(htmlspecialchars($package['name'])); ?></option>`;
            <?php endwhile; ?>
        } else if (itemType === 'attraction') {
            itemDropdown.innerHTML += '<option value="">Select an Attraction...</option>';
            <?php mysqli_data_seek($attractions_result, 0); ?>
            <?php while ($attraction = mysqli_fetch_assoc($attractions_result)): ?>
                itemDropdown.innerHTML += `<option value="<?php echo $attraction['attraction_id']; ?>"><?php echo addslashes(htmlspecialchars($attraction['name'])); ?></option>`;
            <?php endwhile; ?>
        }
        itemGroup.style.display = itemType ? 'block' : 'none';
    });
</script>

<?php include '../includes/footer.php'; ?>