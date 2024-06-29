<?php
// by Indonesian Code Party
// Use your brain, be creative. Stop recoding it.
// Configuration
$db_host = 'localhost';
$db_username = 'your_db_username';
$db_password = 'your_db_Password';
$db_name = 'your_db_name';

// Create connection
$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Execute SQL query
if (isset($_POST['query'])) {
    $query = $_POST['query'];
    $result = $conn->query($query);
    if ($result) {
        echo "<p>Query executed successfully!</p>";
        if ($result instanceof mysqli_result) {
            echo "<table border='1'>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $field => $value) {
                    echo "<td>$value</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p>Error: " . $conn->error . "</p>";
    }
}

// Import SQL file
if (isset($_POST['import'])) {
    $file = $_FILES['import_file'];
    $query = file_get_contents($file['tmp_name']);
    $conn->multi_query($query);
    echo "<p>Import successful!</p>";
}

// Export database
if (isset($_GET['export'])) {
    $tables = array();
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_assoc()) {
        $tables[] = $row['Tables_in_' . $db_name];
    }
    $export = "-- Database: $db_name\n";
    foreach ($tables as $table) {
        $result = $conn->query("SELECT * FROM $table");
        $export .= "-- Table: $table\n";
        $export .= "DROP TABLE IF EXISTS $table;\n";
        $export .= "CREATE TABLE $table (\n";
        $fields = array();
        $result_fields = $conn->query("SHOW COLUMNS FROM $table");
        while ($row = $result_fields->fetch_assoc()) {
            $fields[] = $row['Field'];
        }
        $export .= implode(",\n", $fields) . "\n";
        $export .= ");\n";
        while ($row = $result->fetch_assoc()) {
            $values = array();
            foreach ($fields as $field) {
                $values[] = "'" . addslashes($row[$field]) . "'";
            }
            $export .= "INSERT INTO $table VALUES (" . implode(", ", $values) . ");\n";
        }
        $export .= "\n";
    }
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="database.sql"');
    echo $export;
    exit;
}

// Dump database
if (isset($_GET['dump'])) {
    $dump = "-- Database: $db_name\n";
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_assoc()) {
        $table = $row['Tables_in_' . $db_name];
        $dump .= "-- Table: $table\n";
        $result_fields = $conn->query("SHOW COLUMNS FROM $table");
        $fields = array();
        while ($row = $result_fields->fetch_assoc()) {
            $fields[] = $row['Field'];
        }
        $dump .= "DROP TABLE IF EXISTS $table;\n";
        $dump .= "CREATE TABLE $table (\n";
        $dump .= implode(",\n", $fields) . "\n";
        $dump .= ");\n";
        $result_data = $conn->query("SELECT * FROM $table");
        while ($row = $result_data->fetch_assoc()) {
            $values = array();
            foreach ($fields as $field) {
                $values[] = "'" . addslashes($row[$field]) . "'";
            }
            $dump .= "INSERT INTO $table VALUES (" . implode(", ", $values) . ");\n";
        }
        $dump .= "\n";
    }
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="database.sql"');
    echo $dump;
    exit;
}

// Close connection
$conn->close();
?>

<!-- HTML interface -->
<form action="" method="post">
    <h1>Execute SQL Query</h1>
    <textarea name="query" cols="50" rows="10"></textarea>
    <input type="submit" value="Execute">
</form>

<form action="" method="post" enctype="multipart/form-data">
    <h1>Import SQL File</h1>
    <input type<input type="file" name="import_file">
<input type="submit" name="import" value="Import">
</form>

<h1>Export Database</h1>
<a href="?export">Export database</a>

<h1>Dump Database</h1>
<a href="?dump">Dump database</a>
