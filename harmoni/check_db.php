<?php
try {
    $c = new PDO('mysql:host=127.0.0.1', 'root', '');
    $dbs = $c->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Databases: " . implode(", ", $dbs) . "\n";
    
    if (in_array('db_harmoni', $dbs)) {
        echo "Found db_harmoni!\n";
        $c->exec('USE db_harmoni');
        $tables = $c->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        echo "Tables: " . implode(", ", $tables) . "\n";
        foreach ($tables as $t) {
            echo "Columns in $t: \n";
            $cols = $c->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $col) {
                echo " - " . $col['Field'] . " (" . $col['Type'] . ")\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
