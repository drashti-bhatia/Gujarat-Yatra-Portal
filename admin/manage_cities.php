<?php
$page_title = "Manage Cities";
$active_page = "manage_cities.php";
require_once('includes/db_connect.php');
require_once('includes/auth.php');
checkAdminAuth();

$upload_dir = __DIR__ . '/../assets/img/cities/';
$upload_display_path = "../assets/img/cities/";

// Ensure folder exists and is writable
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
if (!is_writable($upload_dir)) {
    // Remove chmod line — just throw an error if not writable
    $_SESSION['error_message'] = "Upload folder is not writable. Please check folder permissions.";
}


// Handle Add City
if (isset($_POST['add_city'])) {
    $city_name = $_POST['city_name'];
    $description = $_POST['description'];
    $best_time = $_POST['best_time_to_visit'];
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
            $stmt = $conn->prepare("INSERT INTO cities (city_name, description, best_time_to_visit, image_url) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $city_name, $description, $best_time, $image_url);
        } else {
            $stmt = $conn->prepare("INSERT INTO cities (city_name, description, best_time_to_visit) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $city_name, $description, $best_time);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "City added successfully!";
        } else {
            $_SESSION['error_message'] = "Error adding city: " . $stmt->error;
        }
    }
    header("Location: manage_cities.php");
    exit();
}

// Handle Update City
if (isset($_POST['update_city'])) {
    $city_id = $_POST['city_id'];
    $city_name = $_POST['city_name'];
    $description = $_POST['description'];
    $best_time = $_POST['best_time_to_visit'];
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
                $get_old_image_sql = "SELECT image_url FROM cities WHERE city_id = ?";
                $stmt = $conn->prepare($get_old_image_sql);
                $stmt->bind_param("i", $city_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $old_city = $result->fetch_assoc();
                
                if (!empty($old_city['image_url']) && file_exists($upload_dir . $old_city['image_url'])) {
                    unlink($upload_dir . $old_city['image_url']);
                }
            } else {
                $_SESSION['error_message'] = "Failed to move uploaded file. Check folder permissions.";
            }
        } else {
            $_SESSION['error_message'] = "Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.";
        }
    }

    // Update city in database
    if (!isset($_SESSION['error_message'])) {
        if (!empty($image_url)) {
            // Update with new image
            $stmt = $conn->prepare("UPDATE cities SET city_name = ?, description = ?, best_time_to_visit = ?, image_url = ? WHERE city_id = ?");
            $stmt->bind_param("ssssi", $city_name, $description, $best_time, $image_url, $city_id);
        } else {
            // Update without changing image
            $stmt = $conn->prepare("UPDATE cities SET city_name = ?, description = ?, best_time_to_visit = ? WHERE city_id = ?");
            $stmt->bind_param("sssi", $city_name, $description, $best_time, $city_id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "City updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error updating city: " . $stmt->error;
        }
    }
    header("Location: manage_cities.php");
    exit();
}

// Handle Delete City
if (isset($_POST['delete_city'])) {
    $city_id = $_POST['city_id'];
    
    // Get image URL before deleting to remove the file
    $get_image_sql = "SELECT image_url FROM cities WHERE city_id = ?";
    $stmt = $conn->prepare($get_image_sql);
    $stmt->bind_param("i", $city_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $city = $result->fetch_assoc();
    
    // Delete city from database
    $stmt = $conn->prepare("DELETE FROM cities WHERE city_id = ?");
    $stmt->bind_param("i", $city_id);
    
    if ($stmt->execute()) {
        // Delete image file if exists
        if (!empty($city['image_url']) && file_exists($upload_dir . $city['image_url'])) {
            unlink($upload_dir . $city['image_url']);
        }
        $_SESSION['success_message'] = "City deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting city: " . $stmt->error;
    }
    
    header("Location: manage_cities.php");
    exit();
}

// Get all cities
$sql = "SELECT * FROM cities ORDER BY city_id DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Gujarat Yatra Admin</title>
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

                <!-- Cities Table -->
                <div class="bg-white rounded-lg shadow border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-800">Cities</h2>
                        <button onclick="openAddModal()" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            + Add New City
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
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Best Time to Visit</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while ($row = mysqli_fetch_assoc($result)):
                                            $image_path = !empty($row['image_url']) 
                                            ? $upload_display_path . $row['image_url'] 
                                            : 'uploads/default.jpg';
                                        ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?php echo $row['city_id']; ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <img src="<?php echo $image_path; ?>" 
                                                         class="w-16 h-12 object-cover rounded" 
                                                         alt="<?php echo htmlspecialchars($row['city_name']); ?>"
                                                         onerror="this.src='<?php echo $upload_display_path; ?>default.jpg'">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($row['city_name']); ?>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                                                    <div class="truncate">
                                                        <?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?>
                                                        <?php if (strlen($row['description']) > 100): ?>...<?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($row['best_time_to_visit']); ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div class="action-buttons">
                                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)" 
                                                                class="edit-btn text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 px-3 py-1 rounded text-xs">
                                                            Edit
                                                        </button>
                                                        <form method="POST" action="" class="action-form" onsubmit="return confirm('Are you sure you want to delete this city?');">
                                                            <input type="hidden" name="city_id" value="<?php echo $row['city_id']; ?>">
                                                            <button type="submit" name="delete_city" 
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
                                <i class="fas fa-city text-4xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No cities found</h3>
                                <p class="text-gray-500">There are no cities in the system yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit City Modal -->
    <div id="cityModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Add New City</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl">
                        &times;
                    </button>
                </div>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" id="city_id" name="city_id">
                    
                    <div class="mb-4">
                        <label for="city_name" class="block text-sm font-medium text-gray-700 mb-1">City Name</label>
                        <input type="text" id="city_name" name="city_name" required 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" rows="4" required 
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="best_time_to_visit" class="block text-sm font-medium text-gray-700 mb-1">Best Time to Visit</label>
                        <input type="text" id="best_time_to_visit" name="best_time_to_visit" required 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g., October to March">
                    </div>

                    <div class="mb-6">
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1">City Image</label>
                        <input type="file" id="image" name="image" accept="image/*" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Supported formats: JPG, JPEG, PNG, GIF</p>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button type="submit" id="modalSubmitBtn" name="add_city" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Add City
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').innerText = 'Add New City';
            document.getElementById('cityModal').classList.remove('hidden');
            document.getElementById('city_id').value = '';
            document.getElementById('modalSubmitBtn').name = 'add_city';
            document.getElementById('modalSubmitBtn').innerText = 'Add City';
            
            // Reset form
            const form = document.querySelector('#cityModal form');
            form.reset();
        }

        function openEditModal(data) {
            document.getElementById('modalTitle').innerText = 'Edit City';
            document.getElementById('cityModal').classList.remove('hidden');
            
            // Populate form
            document.getElementById('city_id').value = data.city_id;
            document.getElementById('city_name').value = data.city_name;
            document.getElementById('description').value = data.description;
            document.getElementById('best_time_to_visit').value = data.best_time_to_visit;
            
            document.getElementById('modalSubmitBtn').name = 'update_city';
            document.getElementById('modalSubmitBtn').innerText = 'Update City';
        }

        function closeModal() {
            document.getElementById('cityModal').classList.add('hidden');
        }

        // Close modal if user clicks outside
        window.onclick = function(event) {
            const modal = document.getElementById('cityModal');
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