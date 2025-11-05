<?php
$page_title = "Manage Attractions";
$active_page = "manage_attractions.php";
require_once('includes/db_connect.php');
require_once('includes/auth.php');
checkAdminAuth();

// Configuration for image uploads
$upload_dir = __DIR__ . '/../assets/img/attractions/';
$upload_display_path = "../assets/img/attractions/";

// Ensure folder exists and is writable
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}
if (!is_writable($upload_dir)) {
    $_SESSION['error_message'] = "Upload folder is not writable. Please check folder permissions.";
}

// --- Fetch Cities for Dropdown ---
// This is required to associate an attraction with a city
$cities_result = mysqli_query($conn, "SELECT city_id, city_name FROM cities ORDER BY city_name ASC");
if (!$cities_result) {
    $_SESSION['error_message'] = "Could not fetch cities for dropdown: " . mysqli_error($conn);
    // Continue execution to display the rest of the page with the error
}


// Handle Add Attraction
if (isset($_POST['add_attraction'])) {
    $name = $_POST['name'];
    $city_id = $_POST['city_id'];
    $description = $_POST['description'];
    $best_time = $_POST['best_time_to_visit'];
    $entry_fee = $_POST['entry_fee'];
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
        // Updated SQL and bind_param for attractions table
        if (!empty($image_url)) {
            $stmt = $conn->prepare("INSERT INTO attractions (name, city_id, description, best_time_to_visit, entry_fee, image_url) VALUES (?, ?, ?, ?, ?, ?)");
            // 's'tring, 'i'nt, 's'tring, 's'tring, 's'tring, 's'tring
            $stmt->bind_param("sissss", $name, $city_id, $description, $best_time, $entry_fee, $image_url);
        } else {
            $stmt = $conn->prepare("INSERT INTO attractions (name, city_id, description, best_time_to_visit, entry_fee) VALUES (?, ?, ?, ?, ?)");
            // 's'tring, 'i'nt, 's'tring, 's'tring, 's'tring
            $stmt->bind_param("sisss", $name, $city_id, $description, $best_time, $entry_fee);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Attraction added successfully!";
        } else {
            $_SESSION['error_message'] = "Error adding attraction: " . $stmt->error;
        }
    }
    header("Location: manage_attractions.php");
    exit();
}

// Handle Update Attraction
if (isset($_POST['update_attraction'])) {
    $attraction_id = $_POST['attraction_id'];
    $name = $_POST['name'];
    $city_id = $_POST['city_id'];
    $description = $_POST['description'];
    $best_time = $_POST['best_time_to_visit'];
    $entry_fee = $_POST['entry_fee'];
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
                $get_old_image_sql = "SELECT image_url FROM attractions WHERE attraction_id = ?";
                $stmt = $conn->prepare($get_old_image_sql);
                $stmt->bind_param("i", $attraction_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $old_attraction = $result->fetch_assoc();
                
                if (!empty($old_attraction['image_url']) && file_exists($upload_dir . $old_attraction['image_url'])) {
                    unlink($upload_dir . $old_attraction['image_url']);
                }
            } else {
                $_SESSION['error_message'] = "Failed to move uploaded file. Check folder permissions.";
            }
        } else {
            $_SESSION['error_message'] = "Invalid file type. Only JPG, JPEG, PNG, GIF are allowed.";
        }
    }

    // Update attraction in database
    if (!isset($_SESSION['error_message'])) {
        if (!empty($image_url)) {
            // Update with new image
            $stmt = $conn->prepare("UPDATE attractions SET name = ?, city_id = ?, description = ?, best_time_to_visit = ?, entry_fee = ?, image_url = ? WHERE attraction_id = ?");
            // 's'tring, 'i'nt, 's'tring, 's'tring, 's'tring, 's'tring, 'i'nt
            $stmt->bind_param("sissssi", $name, $city_id, $description, $best_time, $entry_fee, $image_url, $attraction_id);
        } else {
            // Update without changing image
            $stmt = $conn->prepare("UPDATE attractions SET name = ?, city_id = ?, description = ?, best_time_to_visit = ?, entry_fee = ? WHERE attraction_id = ?");
            // 's'tring, 'i'nt, 's'tring, 's'tring, 's'tring, 'i'nt
            $stmt->bind_param("sisssi", $name, $city_id, $description, $best_time, $entry_fee, $attraction_id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Attraction updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error updating attraction: " . $stmt->error;
        }
    }
    header("Location: manage_attractions.php");
    exit();
}

// Handle Delete Attraction
if (isset($_POST['delete_attraction'])) {
    $attraction_id = $_POST['attraction_id'];
    
    // Get image URL before deleting to remove the file
    $get_image_sql = "SELECT image_url FROM attractions WHERE attraction_id = ?";
    $stmt = $conn->prepare($get_image_sql);
    $stmt->bind_param("i", $attraction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $attraction = $result->fetch_assoc();
    
    // Delete attraction from database
    $stmt = $conn->prepare("DELETE FROM attractions WHERE attraction_id = ?");
    $stmt->bind_param("i", $attraction_id);
    
    if ($stmt->execute()) {
        // Delete image file if exists
        if (!empty($attraction['image_url']) && file_exists($upload_dir . $attraction['image_url'])) {
            unlink($upload_dir . $attraction['image_url']);
        }
        $_SESSION['success_message'] = "Attraction deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting attraction: " . $stmt->error;
    }
    
    header("Location: manage_attractions.php");
    exit();
}

// Get all attractions (Join with cities to get city name for display)
$sql = "SELECT a.*, c.city_name 
        FROM attractions a 
        LEFT JOIN cities c ON a.city_id = c.city_id
        ORDER BY a.attraction_id DESC";
$attractions_result = mysqli_query($conn, $sql);
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
        /* ABSOLUTE BUTTON PROTECTION - NO HIDING (Copied from manage_cities.php) */
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

                <div class="bg-white rounded-lg shadow border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-800">Attractions</h2>
                        <button onclick="openAddModal()" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            + Add New Attraction
                        </button>
                    </div>

                    <div class="p-6">
                        <?php if (mysqli_num_rows($attractions_result) > 0): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entry Fee</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Best Time</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while ($row = mysqli_fetch_assoc($attractions_result)):
                                            // Image path logic updated for 'attractions' folder
                                            $image_path = !empty($row['image_url']) 
                                            ? $upload_display_path . $row['image_url'] 
                                            : $upload_display_path . 'default.jpg';
                                        ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?php echo $row['attraction_id']; ?>
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
                                                    <?php echo htmlspecialchars($row['city_name'] ?? 'N/A'); ?>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs">
                                                    <div class="truncate">
                                                        <?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?>
                                                        <?php if (strlen($row['description']) > 100): ?>...<?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($row['entry_fee'] ?? 'N/A'); ?>
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
                                                        <form method="POST" action="" class="action-form" onsubmit="return confirm('Are you sure you want to delete this attraction?');">
                                                            <input type="hidden" name="attraction_id" value="<?php echo $row['attraction_id']; ?>">
                                                            <button type="submit" name="delete_attraction" 
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
                                <i class="fas fa-mountain text-4xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No attractions found</h3>
                                <p class="text-gray-500">There are no attractions in the system yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="attractionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Add New Attraction</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl">
                        &times;
                    </button>
                </div>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" id="attraction_id" name="attraction_id">
                    
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Attraction Name</label>
                        <input type="text" id="name" name="name" required 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="city_id" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <select id="city_id" name="city_id" required 
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a City</option>
                            <?php 
                            // Reset city result pointer to reuse for the modal
                            if ($cities_result) {
                                mysqli_data_seek($cities_result, 0);
                                while ($city = mysqli_fetch_assoc($cities_result)): ?>
                                    <option value="<?php echo $city['city_id']; ?>">
                                        <?php echo htmlspecialchars($city['city_name']); ?>
                                    </option>
                                <?php endwhile;
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" name="description" rows="4" required 
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="entry_fee" class="block text-sm font-medium text-gray-700 mb-1">Entry Fee (e.g., 500 or Free)</label>
                        <input type="text" id="entry_fee" name="entry_fee" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g., 500 or Free (optional)">
                    </div>

                    <div class="mb-4">
                        <label for="best_time_to_visit" class="block text-sm font-medium text-gray-700 mb-1">Best Time to Visit</label>
                        <input type="text" id="best_time_to_visit" name="best_time_to_visit" required 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g., October to March">
                    </div>

                    <div class="mb-6">
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Attraction Image</label>
                        <input type="file" id="image" name="image" accept="image/*" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Supported formats: JPG, JPEG, PNG, GIF. Leave blank to keep current image on edit.</p>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button type="submit" id="modalSubmitBtn" name="add_attraction" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Add Attraction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').innerText = 'Add New Attraction';
            document.getElementById('attractionModal').classList.remove('hidden');
            document.getElementById('attraction_id').value = '';
            document.getElementById('modalSubmitBtn').name = 'add_attraction';
            document.getElementById('modalSubmitBtn').innerText = 'Add Attraction';
            
            // Reset form
            const form = document.querySelector('#attractionModal form');
            form.reset();
        }

        function openEditModal(data) {
            document.getElementById('modalTitle').innerText = 'Edit Attraction';
            document.getElementById('attractionModal').classList.remove('hidden');
            
            // Populate form fields
            document.getElementById('attraction_id').value = data.attraction_id;
            document.getElementById('name').value = data.name;
            document.getElementById('city_id').value = data.city_id; // Set selected city
            document.getElementById('description').value = data.description;
            document.getElementById('entry_fee').value = data.entry_fee;
            document.getElementById('best_time_to_visit').value = data.best_time_to_visit;
            
            document.getElementById('modalSubmitBtn').name = 'update_attraction';
            document.getElementById('modalSubmitBtn').innerText = 'Update Attraction';
        }

        function closeModal() {
            document.getElementById('attractionModal').classList.add('hidden');
        }

        // Close modal if user clicks outside
        window.onclick = function(event) {
            const modal = document.getElementById('attractionModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Force button visibility (Copied)
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

        // Run protection continuously (Copied)
        document.addEventListener('DOMContentLoaded', function() {
            protectButtons();
            setInterval(protectButtons, 100);
        });

        // Auto-hide alerts after 5 seconds (Copied)
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-green-50, .bg-red-50');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>