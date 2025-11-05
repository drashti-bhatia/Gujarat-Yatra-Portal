<?php
$page_title = "Manage Reviews";
$active_page = "manage_reviews.php";
require_once('includes/db_connect.php');
require_once('includes/auth.php');
checkAdminAuth();

// Handle review status update
if (isset($_POST['update_review'])) {
    $review_id = mysqli_real_escape_string($conn, $_POST['review_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "UPDATE reviews SET status = '$status' WHERE review_id = '$review_id'";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success_message'] = "Review status updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating review status: " . mysqli_error($conn);
    }

    header("Location: manage_reviews.php");
    exit();
}

// Handle review deletion
if (isset($_POST['delete_review'])) {
    $review_id = mysqli_real_escape_string($conn, $_POST['review_id']);

    $sql = "DELETE FROM reviews WHERE review_id = '$review_id'";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success_message'] = "Review deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting review: " . mysqli_error($conn);
    }

    header("Location: manage_reviews.php");
    exit();
}

// Get all reviews
$sql = "SELECT r.*, u.username, 
        COALESCE(p.name, a.name) as item_name,
        CASE 
            WHEN r.package_id IS NOT NULL THEN 'Package'
            WHEN r.attraction_id IS NOT NULL THEN 'Attraction'
            ELSE 'N/A'
        END as review_type
        FROM reviews r 
        JOIN users u ON r.user_id = u.user_id 
        LEFT JOIN packages p ON r.package_id = p.package_id
        LEFT JOIN attractions a ON r.attraction_id = a.attraction_id
        ORDER BY r.date_posted DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Gujarat Yatra Admin</title>
    <script src="../assets/tailwindcss.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ABSOLUTE BUTTON PROTECTION - NO HIDING */
        .action-buttons {
            display: flex !important;
            gap: 8px;
            flex-wrap: nowrap;
        }
        
        .delete-btn {
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            z-index: 9999 !important;
        }
        
        .edit-btn {
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        .action-form {
            display: inline !important;
        }

        /* Star rating styling */
        .star-rating {
            color: #fbbf24;
            font-size: 14px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex">
        <?php require_once('components/sidebar.php'); ?>
        
        <div class="flex-1 lg:ml-64">
            <header class="bg-white shadow-sm border-b">
                <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center py-4">
                        <h1 class="text-2xl font-bold text-gray-900"><?php echo $page_title; ?></h1>
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                            <a href="logout.php" class="text-red-600 hover:text-red-800 font-medium">Logout</a>
                        </div>
                    </div>
                </div>
            </header>
            
            <main class="p-6">
                <!-- Success/Error Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                        <i class="fas fa-check-circle mr-3 text-green-500"></i>
                        <span><?php echo $_SESSION['success_message']; ?></span>
                        <button onclick="this.parentElement.remove()" class="ml-auto text-green-600 hover:text-green-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                        <i class="fas fa-exclamation-circle mr-3 text-red-500"></i>
                        <span><?php echo $_SESSION['error_message']; ?></span>
                        <button onclick="this.parentElement.remove()" class="ml-auto text-red-600 hover:text-red-800">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <!-- Reviews Table -->
                <div class="bg-white rounded-lg shadow border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">Reviews Management</h2>
                    </div>

                    <div class="p-6">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviewed Item</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($row['username']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600 star-rating">
                                                    <?php echo str_repeat('★', $row['rating']) . str_repeat('☆', 5 - $row['rating']); ?>
                                                    <span class="text-gray-500 text-xs ml-1">(<?php echo $row['rating']; ?>/5)</span>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500 max-w-[150px]">
                                                    <div class="truncate">
                                                        <?php echo htmlspecialchars(substr($row['comment'], 0, 50)); ?>
                                                        <?php if (strlen($row['comment']) > 50): ?>...<?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($row['item_name']); ?>
                                                    <br><small class="text-gray-400"><?php echo $row['review_type']; ?></small>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo date('M d, Y', strtotime($row['date_posted'])); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        <?php echo $row['status'] == 'approved' ? 'bg-green-100 text-green-800' : 
                                                              ($row['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                                        <?php echo ucfirst($row['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="action-buttons">
                                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)" 
                                                                class="edit-btn text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 px-3 py-1 rounded text-xs">
                                                            Edit Status
                                                        </button>
                                                        <form method="POST" action="" class="action-form" onsubmit="return confirm('Are you sure you want to delete this review?');">
                                                            <input type="hidden" name="review_id" value="<?php echo $row['review_id']; ?>">
                                                            <button type="submit" name="delete_review" 
                                                                    class="delete-btn text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 px-3 py-1 rounded text-xs">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <i class="fas fa-star text-4xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No reviews found</h3>
                                <p class="text-gray-500">There are no reviews in the system yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Edit Status Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Update Review Status</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        &times;
                    </button>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" id="review_id" name="review_id">
                    
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="status" name="status" required 
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button type="submit" name="update_review" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openEditModal(reviewData) {
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('review_id').value = reviewData.review_id;
            document.getElementById('status').value = reviewData.status;
        }

        function closeModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Close modal if user clicks outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Force button visibility
        function protectButtons() {
            const deleteButtons = document.querySelectorAll('.delete-btn');
            const editButtons = document.querySelectorAll('.edit-btn');
            const actionForms = document.querySelectorAll('.action-form');
            
            deleteButtons.forEach(btn => {
                btn.style.display = 'inline-flex';
                btn.style.visibility = 'visible';
                btn.style.opacity = '1';
            });
            
            editButtons.forEach(btn => {
                btn.style.display = 'inline-flex';
                btn.style.visibility = 'visible';
                btn.style.opacity = '1';
            });
            
            actionForms.forEach(form => {
                form.style.display = 'inline';
            });
        }

        // Run protection continuously
        document.addEventListener('DOMContentLoaded', function() {
            protectButtons();
            setInterval(protectButtons, 100);
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-green-50, .bg-red-50');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>