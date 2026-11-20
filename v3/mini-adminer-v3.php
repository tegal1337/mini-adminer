<?php

// by Indonesian Code Party and Tegalsec.org
// Do with your own risk.
// Feel free to recode it, just please leave the credit/source. Thanks!
// Best Regards, Lalergod - Tegalsec
// ./eof

$db_host = 'localhost';
$db_username = 'db_username';
$db_password = 'db_pass';
$db_name = 'db_name';

if (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] !== 'tegalsec' || $_SERVER['PHP_AUTH_PW'] !== 'idcparty') {
    header('WWW-Authenticate: Basic realm="Mini Adminer"');
    header('HTTP/1.0 401 Unauthorized');
    exit('Access denied');
}

$conn = new mysqli($db_host, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Fungsi Export / Dump Database
function export_database($conn, $db_name) {
    $tables = array();
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }

    $export = "-- Database: `$db_name`\n";
    $export .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

    foreach ($tables as $table) {
        $export .= "-- Table: `$table`\n";
        $export .= "DROP TABLE IF EXISTS `$table`;\n";
        $createTable = $conn->query("SHOW CREATE TABLE `$table`")->fetch_assoc();
        $export .= $createTable['Create Table'] . ";\n\n";

        $result_data = $conn->query("SELECT * FROM `$table`");
        while ($row = $result_data->fetch_assoc()) {
            $values = array();
            foreach ($row as $value) {
                if ($value === null) {
                    $values[] = "NULL";
                } else {
                    $values[] = "'" . $conn->real_escape_string($value) . "'";
                }
            }
            $export .= "INSERT INTO `$table` VALUES (" . implode(", ", $values) . ");\n";
        }
        $export .= "\n";
    }

    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $db_name . '_' . date('Ymd_His') . '.sql"');
    echo $export;
    exit;
}

if (isset($_GET['export']) || isset($_GET['dump'])) {
    export_database($conn, $db_name);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Mini Adminer</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; color: #333; }
        textarea { width: 100%; height: 180px; margin-bottom: 10px; font-family: monospace; padding: 10px; box-sizing: border-box; }
        input[type="file"], input[type="submit"], .btn { padding: 8px 14px; margin-top: 5px; cursor: pointer; }
        table { border-collapse: collapse; margin-top: 15px; width: 100%; background: #fff; font-size: 14px; }
        td, th { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { margin-top: 30px; font-size: 20px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .success { color: #155724; background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin-top: 15px; border-radius: 4px; }
        .error { color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin-top: 15px; border-radius: 4px; }
        .actions a { display: inline-block; padding: 8px 14px; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px; margin-right: 10px; }
        .actions a:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="container">
    <h1>Execute SQL Query</h1>
    <form action="" method="post">
        <textarea name="query" placeholder="Write your SQL query here (SELECT, INSERT, UPDATE, DELETE, DROP, etc.)..."><?= isset($_POST['query']) ? htmlspecialchars($_POST['query']) : '' ?></textarea>
        <input type="submit" value="Execute">
    </form>

    <?php
    // Eksekusi Query (Support Multi-Query & Non-Read-Only)
    if (isset($_POST['query']) && trim($_POST['query']) !== '') {
        $query = trim($_POST['query']);

        if ($conn->multi_query($query)) {
            do {
                if ($result = $conn->store_result()) {
                    echo "<div class='success'>Query executed! Rows returned: " . $result->num_rows . "</div>";
                    echo "<div style='overflow-x:auto;'><table><tr>";
                    foreach ($result->fetch_fields() as $field) {
                        echo "<th>" . htmlspecialchars($field->name) . "</th>";
                    }
                    echo "</tr>";
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        foreach ($row as $value) {
                            echo "<td>" . ($value === null ? '<i>NULL</i>' : htmlspecialchars($value)) . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table></div>";
                    $result->free();
                } else {
                    if ($conn->errno === 0) {
                        echo "<div class='success'>Query executed successfully! Affected rows: " . $conn->affected_rows . "</div>";
                    }
                }
            } while ($conn->more_results() && $conn->next_result());

            if ($conn->errno) {
                echo "<div class='error'>Error: " . htmlspecialchars($conn->error) . "</div>";
            }
        } else {
            echo "<div class='error'>Error: " . htmlspecialchars($conn->error) . "</div>";
        }
    }

    // Import SQL File
    if (isset($_POST['import'])) {
        if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['import_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if ($ext !== 'sql' && $ext !== 'txt') {
                echo "<div class='error'>Invalid file type. Only .sql or .txt allowed.</div>";
            } else {
                $sql_content = file_get_contents($file['tmp_name']);
                if ($conn->multi_query($sql_content)) {
                    do {
                        if ($res = $conn->store_result()) {
                            $res->free();
                        }
                    } while ($conn->more_results() && $conn->next_result());

                    if ($conn->errno) {
                        echo "<div class='error'>Import stopped with error: " . htmlspecialchars($conn->error) . "</div>";
                    } else {
                        echo "<div class='success'>Import successful!</div>";
                    }
                } else {
                    echo "<div class='error'>Import failed: " . htmlspecialchars($conn->error) . "</div>";
                }
            }
        } else {
            echo "<div class='error'>Please select a valid SQL file to upload.</div>";
        }
    }
    ?>

    <h1>Import SQL File</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="import_file" accept=".sql,.txt" required>
        <input type="submit" name="import" value="Import">
    </form>

    <h1>Export / Dump Database</h1>
    <div class="actions">
        <a href="?export=1">Export Database (.sql)</a>
    </div>
</div>

</body>
</html>
<?php
$conn->close();
?>
