<?php
$page_title = "Manage Packages";
$active_page = "manage_packages.php";
require_once('includes/db_connect.php');
require_once('includes/auth.php');
checkAdminAuth();

$upload_dir = __DIR__ . '/../assets/img/packages/';
$upload_display_path = "../assets/img/packages/";

// Ensure folder exists and is writable
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
if (!is_writable($upload_dir)) {
    chmod($upload_dir, 0777);
}



// Handle Add Package
if (isset($_POST['add_package'])) {
    $name = $_POST['name'];
    $city_id = $_POST['city_id'];
    $description = $_POST['description'];
    $itinerary = $_POST['itinerary'];
    $inclusions = $_POST['inclusions'];
    $price = $_POST['price'];
    $duration_days = $_POST['duration_days'];
    $image_url = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $image_name = time() . '_' . uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $image_name;
        
        // Check if image file is actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_url = $image_name;
            } else {
                $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
            }
        } else {
            $_SESSION['error_message'] = "File is not an image.";
        }
    }

    if (!isset($_SESSION['error_message'])) {
        if (!empty($image_url)) {
            $stmt = $conn->prepare("INSERT INTO packages (name, city_id, description, itinerary, inclusions, price, duration_days, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sisssdis", $name, $city_id, $description, $itinerary, $inclusions, $price, $duration_days, $image_url);
        } else {
            $stmt = $conn->prepare("INSERT INTO packages (name, city_id, description, itinerary, inclusions, price, duration_days) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sisssdi", $name, $city_id, $description, $itinerary, $inclusions, $price, $duration_days);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Package added successfully!";
        } else {
            $_SESSION['error_message'] = "Error adding package: " . $stmt->error;
        }
    }
    header("Location: manage_packages.php");
    exit();
}

// Handle Update Package
if (isset($_POST['update_package'])) {
    $package_id = $_POST['package_id'];
    $name = $_POST['name'];
    $city_id = $_POST['city_id'];
    $description = $_POST['description'];
    $itinerary = $_POST['itinerary'];
    $inclusions = $_POST['inclusions'];
    $price = $_POST['price'];
    $duration_days = $_POST['duration_days'];
    $image_url = '';

    // Check if new image is uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    
        if (in_array($file_extension, $allowed_types)) {
            $image_name = time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $image_name;
    
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_url = $image_name;
                
                // Delete old image if exists
                $get_old_image_sql = "SELECT image_url FROM packages WHERE package_id = ?";
                $stmt = $conn->prepare($get_old_image_sql);
                $stmt->bind_param("i", $package_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $old_package = $result->fetch_assoc();
                
                if (!empty($old_package['image_url']) && file_exists($upload_dir . $old_package['image_url'])) {
                    unlink($upload_dir . $old_package['image_url']);
                }
            } else {
                $_SESSION['error_message'] = "Failed to move uploaded file. Check folder permissions.";
            }
        } else {
            $_SESSION['error_message'] = "Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.";
        }
    }

    // Update package in database
    if (!isset($_SESSION['error_message'])) {
        if (!empty($image_url)) {
            // Update with new image
            $stmt = $conn->prepare("UPDATE packages SET name = ?, city_id = ?, description = ?, itinerary = ?, inclusions = ?, price = ?, duration_days = ?, image_url = ? WHERE package_id = ?");
            $stmt->bind_param("sisssdisi", $name, $city_id, $description, $itinerary, $inclusions, $price, $duration_days, $image_url, $package_id);
        } else {
            // Update without changing image
            $stmt = $conn->prepare("UPDATE packages SET name = ?, city_id = ?, description = ?, itinerary = ?, inclusions = ?, price = ?, duration_days = ? WHERE package_id = ?");
            $stmt->bind_param("sisssdii", $name, $city_id, $description, $itinerary, $inclusions, $price, $duration_days, $package_id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Package updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error updating package: " . $stmt->error;
        }
    }
    header("Location: manage_packages.php");
    exit();
}

// Handle Delete Package
if (isset($_POST['delete_package'])) {
    $package_id = $_POST['package_id'];
    
    // Get image URL before deleting to remove the file
    $get_image_sql = "SELECT image_url FROM packages WHERE package_id = ?";
    $stmt = $conn->prepare($get_image_sql);
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $package = $result->fetch_assoc();
    
    // Delete package from database
    $stmt = $conn->prepare("DELETE FROM packages WHERE package_id = ?");
    $stmt->bind_param("i", $package_id);
    
    if ($stmt->execute()) {
        // Delete image file if exists
        if (!empty($package['image_url']) && file_exists($upload_dir . $package['image_url'])) {
            unlink($upload_dir . $package['image_url']);
        }
        $_SESSION['success_message'] = "Package deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting package: " . $stmt->error;
    }
    
    header("Location: manage_packages.php");
    exit();
}

// Fetch cities for dropdown
$cities_sql = "SELECT city_id, city_name FROM cities ORDER BY city_name";
$cities_result = mysqli_query($conn, $cities_sql);
$cities_list = [];
while ($city = mysqli_fetch_assoc($cities_result)) {
    $cities_list[] = $city;
}

// Get all packages
$sql = "SELECT p.*, c.city_name 
        FROM packages p
        LEFT JOIN cities c ON p.city_id = c.city_id
        ORDER BY p.package_id DESC";
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

                <!-- Packages Table -->
                <div class="bg-white rounded-lg shadow border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-800">Tour Packages</h2>
                        <button onclick="openAddModal()" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            + Add New Package
                        </button>
                    </div>

                    <div class="p-6">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while ($row = mysqli_fetch_assoc($result)):
                                            $image_path = !empty($row['image_url']) 
                                            ? $upload_display_path . $row['image_url'] 
                                            : $upload_display_path . 'default.jpg';
                                        ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?php echo $row['package_id']; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <img src="<?php echo $image_path; ?>" 
                                                         class="w-16 h-12 object-cover rounded" 
                                                         alt="<?php echo htmlspecialchars($row['name']); ?>"
                                                         onerror="this.src='<?php echo $upload_display_path; ?>default.jpg'">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($row['name']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    ₹<?php echo number_format($row['price']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($row['duration_days']); ?> days
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($row['city_name'] ?? 'N/A'); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="action-buttons">
                                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)" 
                                                                class="edit-btn text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 px-3 py-1 rounded text-xs">
                                                            Edit
                                                        </button>
                                                        <form method="POST" action="" class="action-form" onsubmit="return confirm('Are you sure you want to delete this package?');">
                                                            <input type="hidden" name="package_id" value="<?php echo $row['package_id']; ?>">
                                                            <button type="submit" name="delete_package" 
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
                                <i class="fas fa-suitcase text-4xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No packages found</h3>
                                <p class="text-gray-500">There are no packages in the system yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Package Modal -->
    <div id="packageModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Add New Package</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl">
                        &times;
                    </button>
                </div>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" id="package_id" name="package_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Package Name</label>
                            <input type="text" id="name" name="name" required 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="city_id" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <select id="city_id" name="city_id" required 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select City</option>
                                <?php foreach ($cities_list as $city): ?>
                                    <option value="<?php echo $city['city_id']; ?>"><?php echo htmlspecialchars($city['city_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" rows="3" required 
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="itinerary" class="block text-sm font-medium text-gray-700 mb-1">Itinerary</label>
                        <textarea id="itinerary" name="itinerary" rows="3" 
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="inclusions" class="block text-sm font-medium text-gray-700 mb-1">Inclusions (comma-separated)</label>
                        <input type="text" id="inclusions" name="inclusions" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (₹)</label>
                            <input type="number" id="price" name="price" required min="0" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label for="duration_days" class="block text-sm font-medium text-gray-700 mb-1">Duration (Days)</label>
                            <input type="number" id="duration_days" name="duration_days" required min="1" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Package Image</label>
                        <input type="file" id="image" name="image" accept="image/*" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Supported formats: JPG, JPEG, PNG, GIF</p>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button type="submit" id="modalSubmitBtn" name="add_package" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Add Package
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').innerText = 'Add New Package';
            document.getElementById('packageModal').classList.remove('hidden');
            document.getElementById('package_id').value = '';
            document.getElementById('modalSubmitBtn').name = 'add_package';
            document.getElementById('modalSubmitBtn').innerText = 'Add Package';
            
            // Reset form
            const form = document.querySelector('#packageModal form');
            form.reset();
        }

        function openEditModal(data) {
            document.getElementById('modalTitle').innerText = 'Edit Package';
            document.getElementById('packageModal').classList.remove('hidden');
            
            // Populate form
            document.getElementById('package_id').value = data.package_id;
            document.getElementById('name').value = data.name;
            document.getElementById('city_id').value = data.city_id;
            document.getElementById('description').value = data.description;
            document.getElementById('itinerary').value = data.itinerary;
            document.getElementById('inclusions').value = data.inclusions;
            document.getElementById('price').value = data.price;
            document.getElementById('duration_days').value = data.duration_days;
            
            document.getElementById('modalSubmitBtn').name = 'update_package';
            document.getElementById('modalSubmitBtn').innerText = 'Update Package';
        }

        function closeModal() {
            document.getElementById('packageModal').classList.add('hidden');
        }

        // Close modal if user clicks outside
        window.onclick = function(event) {
            const modal = document.getElementById('packageModal');
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