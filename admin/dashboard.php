<?php
$page_title = "Dashboard";
$active_page = "dashboard.php";
require_once('includes/db_connect.php');
require_once('includes/auth.php');
checkAdminAuth();

// Fetch stats from database
$bookings_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings"))['count'];
$packages_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM packages"))['count'];
$cities_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM cities"))['count'];
$reviews_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM reviews"))['count'];

// Recent bookings
$recent_bookings_sql = "SELECT b.*, u.username, p.name as package_name 
                       FROM bookings b 
                       JOIN users u ON b.user_id = u.user_id 
                       JOIN packages p ON b.package_id = p.package_id 
                       ORDER BY b.booking_date DESC LIMIT 5";
$recent_bookings = mysqli_query($conn, $recent_bookings_sql);

// Revenue stats
$revenue_sql = "SELECT SUM(total_amount) as total_revenue FROM bookings WHERE payment_status = 'paid'";
$revenue_result = mysqli_query($conn, $revenue_sql);
$total_revenue = mysqli_fetch_assoc($revenue_result)['total_revenue'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gujarat Yatra Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="flex">
        <?php require_once('components/sidebar.php'); ?>
        
        <div class="flex-1 ml-0 lg:ml-64">
            <?php require_once('components/header.php'); ?>
            
            <main class="p-4 lg:p-6">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">
                    <div class="bg-white p-4 lg:p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-50 text-blue-600">
                                <i class="fas fa-calendar-check text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm lg:text-base font-medium text-gray-700">Total Bookings</h3>
                                <p class="text-xl lg:text-3xl font-bold text-blue-600"><?php echo number_format($bookings_count); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-4 lg:p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-50 text-green-600">
                                <i class="fas fa-suitcase text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm lg:text-base font-medium text-gray-700">Packages</h3>
                                <p class="text-xl lg:text-3xl font-bold text-green-600"><?php echo number_format($packages_count); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-4 lg:p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-50 text-purple-600">
                                <i class="fas fa-city text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm lg:text-base font-medium text-gray-700">Cities</h3>
                                <p class="text-xl lg:text-3xl font-bold text-purple-600"><?php echo number_format($cities_count); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-4 lg:p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-50 text-yellow-600">
                                <i class="fas fa-star text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm lg:text-base font-medium text-gray-700">Reviews</h3>
                                <p class="text-xl lg:text-3xl font-bold text-yellow-600"><?php echo number_format($reviews_count); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Card -->
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-md p-6 mb-6 lg:mb-8 text-black">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                        <div>
                            <h3 class="text-lg font-semibold">Total Revenue</h3>
                            <p class="text-2xl lg:text-3xl font-bold mt-2">₹<?php echo number_format($total_revenue, 2); ?></p>
                            <p class="text-black-100 mt-1">From all paid bookings</p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <i class="fas fa-chart-line text-3xl opacity-80"></i>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="px-4 lg:px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg lg:text-xl font-semibold text-gray-800">Recent Bookings</h2>
                        <a href="manage_bookings.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View All
                        </a>
                    </div>
                    <div class="p-4 lg:p-6">
                        <?php if(mysqli_num_rows($recent_bookings) > 0): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking ID</th>
                                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package</th>
                                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while($booking = mysqli_fetch_assoc($recent_bookings)): ?>
                                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php echo $booking['booking_id']; ?></td>
                                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($booking['username']); ?></td>
                                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars($booking['package_name']); ?></td>
                                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm text-gray-500">₹<?php echo number_format($booking['total_amount']); ?></td>
                                            <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php echo $booking['payment_status'] == 'paid' ? 'bg-green-100 text-green-800' : 
                                                          ($booking['payment_status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                                    <?php echo ucfirst($booking['payment_status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500">No bookings found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>