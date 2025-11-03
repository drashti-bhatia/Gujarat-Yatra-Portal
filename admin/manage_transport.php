<?php
$page_title = "Manage Transport";
$active_page = "manage_transport.php";
require_once('includes/db_connect.php');
require_once('includes/auth.php');
checkAdminAuth();

// Handle Add Transport
if (isset($_POST['add_transport'])) {
    $departure_city_id = $_POST['departure_city_id'];
    $arrival_city_id = $_POST['arrival_city_id'];
    $transport_type = $_POST['transport_type'];
    $details = $_POST['details'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];

    $stmt = $conn->prepare("INSERT INTO transport_options (departure_city_id, arrival_city_id, transport_type, description, approx_price) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iissd", $departure_city_id, $arrival_city_id, $transport_type, $details, $price);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Transport option added successfully!";
    } else {
        $_SESSION['error_message'] = "Error adding transport option: " . $stmt->error;
    }
    header("Location: manage_transport.php");
    exit();
}

// Handle Update Transport
if (isset($_POST['update_transport'])) {
    $transport_id = $_POST['transport_id'];
    $departure_city_id = $_POST['departure_city_id'];
    $arrival_city_id = $_POST['arrival_city_id'];
    $transport_type = $_POST['transport_type'];
    $details = $_POST['details'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];

    $stmt = $conn->prepare("UPDATE transport_options SET departure_city_id = ?, arrival_city_id = ?, transport_type = ?, description = ?, approx_price = ? WHERE transport_id = ?");
    $stmt->bind_param("iissdi", $departure_city_id, $arrival_city_id, $transport_type, $details, $price, $transport_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Transport option updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating transport option: " . $stmt->error;
    }
    header("Location: manage_transport.php");
    exit();
}

// Handle Delete Transport
if (isset($_POST['delete_transport'])) {
    $transport_id = $_POST['transport_id'];
    
    $stmt = $conn->prepare("DELETE FROM transport_options WHERE transport_id = ?");
    $stmt->bind_param("i", $transport_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Transport option deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting transport option: " . $stmt->error;
    }
    
    header("Location: manage_transport.php");
    exit();
}

// Fetch cities for dropdowns
$cities_sql = "SELECT city_id, city_name FROM cities ORDER BY city_name";
$cities_result = mysqli_query($conn, $cities_sql);
$cities_list = [];
while ($city = mysqli_fetch_assoc($cities_result)) {
    $cities_list[] = $city;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Gujarat Yatra Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php require_once('components/sidebar.php'); ?>
        
        <div class="flex-1 ml-64">
            <?php require_once('components/header.php'); ?>
            
            <main class="p-6">
                <?php
                // Display success/error messages
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">' . $_SESSION['success_message'] . '</div>';
                    unset($_SESSION['success_message']);
                }
                if (isset($_SESSION['error_message'])) {
                    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">' . $_SESSION['error_message'] . '</div>';
                    unset($_SESSION['error_message']);
                }
                ?>

                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-800">Transport Options</h2>
                        <button onclick="openAddModal()" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            + Add Transport
                        </button>
                    </div>

                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departure City</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arrival City</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price (₹)</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php
                                    $sql = "SELECT t.*, dep_city.city_name as departure_city, arr_city.city_name as arrival_city 
                                    FROM transport_options t 
                                    LEFT JOIN cities dep_city ON t.departure_city_id = dep_city.city_id 
                                    LEFT JOIN cities arr_city ON t.arrival_city_id = arr_city.city_id 
                                    ORDER BY t.transport_id DESC";
                                    $result = mysqli_query($conn, $sql);
                                    while ($row = mysqli_fetch_assoc($result)):
                                    ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $row['transport_id']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['departure_city'] ?? 'N/A'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['arrival_city'] ?? 'N/A'); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    <?php echo $row['transport_type'] == 'bus' ? 'bg-blue-100 text-blue-800' : 
                                                          ($row['transport_type'] == 'train' ? 'bg-green-100 text-green-800' : 
                                                          ($row['transport_type'] == 'flight' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800')); ?>">
                                                    <?php echo ucfirst($row['transport_type']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₹<?php echo number_format($row['approx_price']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>)" 
                                                            class="text-blue-600 hover:text-blue-900 bg-blue-100 hover:bg-blue-200 px-3 py-1 rounded text-xs">
                                                        Edit
                                                    </button>
                                                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this transport option?');">
                                                        <input type="hidden" name="transport_id" value="<?php echo $row['transport_id']; ?>">
                                                        <button type="submit" name="delete_transport" 
                                                                class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 px-3 py-1 rounded text-xs">
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
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Transport Modal -->
    <div id="transportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Add Transport Option</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-xl">
                        &times;
                    </button>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" id="transport_id" name="transport_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="departure_city_id" class="block text-sm font-medium text-gray-700 mb-1">Departure City</label>
                            <select id="departure_city_id" name="departure_city_id" required 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select City</option>
                                <?php foreach ($cities_list as $city): ?>
                                    <option value="<?php echo $city['city_id']; ?>"><?php echo htmlspecialchars($city['city_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="arrival_city_id" class="block text-sm font-medium text-gray-700 mb-1">Arrival City</label>
                            <select id="arrival_city_id" name="arrival_city_id" required 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select City</option>
                                <?php foreach ($cities_list as $city): ?>
                                    <option value="<?php echo $city['city_id']; ?>"><?php echo htmlspecialchars($city['city_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="transport_type" class="block text-sm font-medium text-gray-700 mb-1">Transport Type</label>
                            <select id="transport_type" name="transport_type" required 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Type</option>
                                <option value="bus">Bus</option>
                                <option value="train">Train</option>
                                <option value="flight">Flight</option>
                                <option value="car">Car</option>
                            </select>
                        </div>
                       
                    </div>

                    <div class="mb-4">
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (₹)</label>
                        <input type="number" id="price" name="price" required min="0" step="0.01"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-6">
                        <label for="details" class="block text-sm font-medium text-gray-700 mb-1">Details</label>
                        <textarea id="details" name="details" rows="3" 
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="e.g., AC bus, overnight train, etc."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button type="submit" id="modalSubmitBtn" name="add_transport" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Add Transport
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').innerText = 'Add Transport Option';
            document.getElementById('transportModal').classList.remove('hidden');
            document.getElementById('transport_id').value = '';
            document.getElementById('modalSubmitBtn').name = 'add_transport';
            document.getElementById('modalSubmitBtn').innerText = 'Add Transport';
            
            // Reset form
            const form = document.querySelector('#transportModal form');
            form.reset();
        }

        function openEditModal(data) {
            document.getElementById('modalTitle').innerText = 'Edit Transport Option';
            document.getElementById('transportModal').classList.remove('hidden');
            
            // Populate form
            document.getElementById('transport_id').value = data.transport_id;
            document.getElementById('departure_city_id').value = data.departure_city_id;
            document.getElementById('arrival_city_id').value = data.arrival_city_id;
            document.getElementById('transport_type').value = data.transport_type;
            document.getElementById('details').value = data.details;
            document.getElementById('price').value = data.approx_price;
            document.getElementById('capacity').value = data.capacity;
            
            document.getElementById('modalSubmitBtn').name = 'update_transport';
            document.getElementById('modalSubmitBtn').innerText = 'Update Transport';
        }

        function closeModal() {
            document.getElementById('transportModal').classList.add('hidden');
        }

        // Close modal if user clicks outside
        window.onclick = function(event) {
            const modal = document.getElementById('transportModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Auto-hide messages
        setTimeout(function() {
            const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100');
            alerts.forEach(alert => {
                alert.style.display = 'none';
            });
        }, 5000);
    </script>
</body>
</html>