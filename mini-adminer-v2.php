<?php

// by Indonesian Code Party
// Mini Adminer with improvements

$db_host = 'localhost';
$db_username = 'your_db_username';
$db_password = 'your_db_password';
$db_name = 'your_db_name';

if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] !== 'human' || $_SERVER['PHP_AUTH_PW'] !== 'password') {
    header('WWW-Authenticate: Basic realm="Mini Adminer"');
    header('HTTP/1.0 401 Unauthorized');
    exit('Access denied');
}

$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function safe_query($query) {
    global $conn;
    if (preg_match('/\b(SELECT|SHOW|DESCRIBE|EXPLAIN)\b/i', $query)) {
        return $conn->query($query);
    } else {
        die("Only read-only queries allowed for safety.");
    }
}

if (isset($_POST['query'])) {
    $query = $_POST['query'];
    $result = safe_query($query);
    if ($result) {
        echo "<div style='color: green;'>Query executed successfully!</div>";
        if ($result instanceof mysqli_result) {
            echo "<table border='1'>";
            echo "<tr>";
            // Show column names
            foreach ($result->fetch_fields() as $field) {
                echo "<th>{$field->name}</th>";
            }
            echo "</tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<div style='color: red;'>Error: " . $conn->error . "</div>";
    }
}

if (isset($_POST['import'])) {
    $file = $_FILES['import_file'];
    if ($file['type'] !== 'text/plain' && pathinfo($file['name'], PATHINFO_EXTENSION) !== 'sql') {
        die("Invalid file type.");
    }
    $query = file_get_contents($file['tmp_name']);
    $conn->multi_query($query);
    echo "<div style='color: green;'>Import successful!</div>";
}

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
        $createTable = $conn->query("SHOW CREATE TABLE `$table`")->fetch_assoc();
        $export .= $createTable['Create Table'] . ";\n";
        while ($row = $result->fetch_assoc()) {
            $values = array();
            foreach ($row as $field => $value) {
                $values[] = "'" . addslashes($value) . "'";
            }
            $export .= "INSERT INTO `$table` VALUES (" . implode(", ", $values) . ");\n";
        }
        $export .= "\n";
    }
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="database.sql"');
    echo $export;
    exit;
}

if (isset($_GET['dump'])) {
    $dump = "-- Database: $db_name\n";
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_assoc()) {
        $table = $row['Tables_in_' . $db_name];
        $dump .= "-- Table: $table\n";
        $createTable = $conn->query("SHOW CREATE TABLE `$table`")->fetch_assoc();
        $dump .= $createTable['Create Table'] . ";\n";
        $result_data = $conn->query("SELECT * FROM $table");
        while ($row = $result_data->fetch_assoc()) {
            $values = array();
            foreach ($row as $field => $value) {
                $values[] = "'" . addslashes($value) . "'";
            }
            $dump .= "INSERT INTO `$table` VALUES (" . implode(", ", $values) . ");\n";
        }
        $dump .= "\n";
    }
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="database.sql"');
    echo $dump;
    exit;
}

$conn->close();
?>

<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
    textarea { width: 100%; height: 150px; margin-bottom: 10px; }
    input[type="file"], input[type="submit"] { padding: 8px; margin-top: 10px; }
    table { border-collapse: collapse; margin-top: 20px; width: 100%; }
    td, th { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f4f4f4; }
    .container { max-width: 800px; margin: 0 auto; }
    h1 { margin-top: 40px; }
    .success { color: green; }
    .error { color: red; }
</style>

<div class="container">
    <h1>Execute SQL Query</h1>
    <form action="" method="post">
        <textarea name="query" placeholder="Write your query here..."></textarea>
        <input type="submit" value="Execute">
    </form>

    <h1>Import SQL File</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="import_file">
        <input type="submit" name="import" value="Import">
    </form>

    <h1>Export Database</h1>
    <a href="?export">Export database</a>

    <h1>Dump Database</h1>
    <a href="?dump">Dump database</a>
</div>
