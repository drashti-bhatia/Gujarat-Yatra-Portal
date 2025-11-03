<?php
session_start();
// Define a base path for correct asset and page linking
// $base_path = '/project/'; // Adjust this if your project is in a different subfolder on your server/////////////
$base_path = '/Gujarat-Yatra-Portal/';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gujarat Yatra Portal</title>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <header>
        <div class="container main-header">
            <div class="logo">
                <a href="<?php echo $base_path; ?>index.php">
                    <img src="<?php echo $base_path; ?>assets/img/logo/logo.png" alt="The Gujarat Yatra Portal logo">
                    <span class="logo-text">Gujarat Yatra Portal</span>
                </a>
            </div>

            <div class="links">
                <nav class="main-nav">
                    <ul>
                        <li><a href="<?php echo $base_path; ?>index.php">Home</a></li>
                        <li><a href="<?php echo $base_path; ?>pages/attractions.php">Attractions</a></li>
                        <li><a href="<?php echo $base_path; ?>pages/packages.php">Packages</a></li>
                        <li><a href="<?php echo $base_path; ?>pages/cities.php">Cities</a></li>
                        <li><a href="<?php echo $base_path; ?>pages/about.php">About Us</a></li>
                    </ul>
                </nav>

                <form action="<?php echo $base_path; ?>pages/search.php" method="GET" class="search-container">
                    <button type="submit" class="search-submit-btn"></button>
                    <input type="text" name="query" class="search-input" placeholder="Search for cities, packages..."
                        required>
                </form>

                <div class="user-actions">
                    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                    <div class="dropdown">
                        <button class="user-btn">
                            <span class="material-symbols-rounded">
                                person
                            </span>
                        </button>
                        <div class="dropdown-content">
                            <a href="<?php echo $base_path; ?>pages/my_bookings.php">My Bookings</a>
                            <a href="<?php echo $base_path; ?>pages/reviews.php">My Reviews</a>
                                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                                    <a href="<?php echo $base_path; ?>admin/dashboard.php">Dashboard</a>
                                <?php endif; ?>
                                <a href="<?php echo $base_path; ?>user/logout.php">Logout</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo $base_path; ?>user/login.php" class="btn btn-login">Login</a>
            <?php endif; ?>

            </div>
        </div>
        </div>
    </header>
    <main>