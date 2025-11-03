<?php require_once('includes/auth.php');
checkAdminAuth(); ?>
<nav class="bg-gray-800 w-64 min-h-screen p-4 flex flex-col fixed h-full z-30">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <!-- Logo Section -->
    <div class="mb-8 flex-shrink-0">
        <a href="../index.php">
            <h2 class="text-white text-xl font-bold">Gujarat Yatra</h2>
        </a>
        <p class="text-gray-400 text-sm">Admin Panel</p>
    </div>

    <!-- Navigation Links - No Scroll -->
    <ul class="space-y-2 flex-1 overflow-hidden">
        <li>
            <a href="dashboard.php"
                class="flex items-center p-2 text-gray-300 hover:bg-gray-700 rounded transition-colors duration-200 <?php echo ($active_page ?? '') == 'dashboard.php' ? 'bg-gray-700' : ''; ?>">
                <span class="mr-3">📊</span>
                Dashboard
            </a>
        </li>
        <li>
            <a href="manage_bookings.php"
                class="flex items-center p-2 text-gray-300 hover:bg-gray-700 rounded transition-colors duration-200 <?php echo ($active_page ?? '') == 'manage_bookings.php' ? 'bg-gray-700' : ''; ?>">
                <span class="mr-3">📋</span>
                Bookings
            </a>
        </li>
        <li>
            <a href="manage_packages.php"
                class="flex items-center p-2 text-gray-300 hover:bg-gray-700 rounded transition-colors duration-200 <?php echo ($active_page ?? '') == 'manage_packages.php' ? 'bg-gray-700' : ''; ?>">
                <span class="mr-3">🎒</span>
                Packages
            </a>
        </li>
        <li>
            <a href="manage_cities.php"
                class="flex items-center p-2 text-gray-300 hover:bg-gray-700 rounded transition-colors duration-200 <?php echo ($active_page ?? '') == 'manage_cities.php' ? 'bg-gray-700' : ''; ?>">
                <span class="mr-3">🏙️</span>
                Cities
            </a>
        </li>
        <li>
            <a href="manage_attractions.php"
                class="flex items-center p-2 text-gray-300 hover:bg-gray-700 rounded transition-colors duration-200 <?php echo ($active_page ?? '') == 'manage_attractions.php' ? 'bg-gray-700' : ''; ?>">
                <span class="mr-3">🏛️</span>
                Attractions
            </a>
        </li>
        <li>
            <a href="manage_transport.php"
                class="flex items-center p-2 text-gray-300 hover:bg-gray-700 rounded transition-colors duration-200 <?php echo ($active_page ?? '') == 'manage_transport.php' ? 'bg-gray-700' : ''; ?>">
                <span class="mr-3">🚌</span>
                Transport
            </a>
        </li>
        <li>
            <a href="manage_reviews.php"
                class="flex items-center p-2 text-gray-300 hover:bg-gray-700 rounded transition-colors duration-200 <?php echo ($active_page ?? '') == 'manage_reviews.php' ? 'bg-gray-700' : ''; ?>">
                <span class="mr-3">⭐</span>
                Reviews
            </a>
        </li>
    </ul>

    <!-- User Info Section - Fixed at bottom -->
    <div class="pt-4 mt-4 border-t border-gray-700 flex-shrink-0">
        <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                    <span
                        class="text-white text-sm font-medium uppercase"><?php echo substr($_SESSION['admin_username'], 0, 1); ?></span>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">
                    <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                </p>
                <p class="text-xs text-gray-400 truncate">
                    Administrator
                </p>
            </div>
        </div>
    </div>
</nav>