<?php 
// Remove the include and checkAuth from here since it's already in sidebar
?>

<header class="bg-white shadow-sm border-b top-0 z-40">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
            <h1 class="text-2xl font-bold text-gray-900"><?php echo $page_title ?? 'Admin Panel'; ?></h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700 hidden sm:inline">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="text-red-600 hover:text-red-800 font-medium transition-colors duration-200">Logout</a>
            </div>
        </div>
    </div>
</header>