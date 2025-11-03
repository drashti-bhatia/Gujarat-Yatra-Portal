<?php
require_once('includes/db_connect.php');

echo "<h2>Database Connection Test</h2>";

if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
} else {
    echo "✅ Connected successfully to database: " . $conn->host_info . "<br>";
    echo "✅ Database: traveldb<br>";
    
    // Test query
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✅ Admin users found: " . $row['count'] . "<br>";
    } else {
        echo "❌ Query failed: " . $conn->error . "<br>";
    }
}

$conn->close();
?>