<?php
require_once 'config/db.php';

echo "<h1>Database Connection Test</h1>";
echo "Attempting to connect to: " . DB_HOST . "<br>";
echo "Using Username: " . DB_USER . "<br>";
echo "Database Name: " . DB_NAME . "<br><br>";

if ($conn->connect_error) {
    echo "<p style='color:red;'><b>CONNECTION FAILED:</b> " . $conn->connect_error . "</p>";
    echo "<p>Double-check your credentials in <b>config/db.php</b></p>";
} else {
    echo "<p style='color:green;'><b>CONNECTION SUCCESSFUL!</b></p>";
    
    $result = $conn->query("SHOW TABLES");
    if ($result->num_rows > 0) {
        echo "<h3>Tables found:</h3><ul>";
        while($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:orange;'>Connected, but <b>NO TABLES FOUND</b>. Did you import database_hosting.sql?</p>";
    }
}
?>
