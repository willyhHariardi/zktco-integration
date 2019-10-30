<?php
$database_path = realpath("C:/Program Files (x86)/ZKTeco/ZKAccess3.5/access.mdb");
if (!file_exists($database_path)) {
    die("Could not find database file.");
}
// $db = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb)}; DBQ=$dbName; Uid=; Pwd=;");
// $db = odbc_connect("DRIVER={Microsoft Access Driver (*.mdb, *.accdb)}; Dbq=$database_path", "", "");
// $db = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$database_path", "admin", "");
try {
    $db = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$database_path", "admin", "");
} catch (PDOException $e) {
    echo $e->getMessage();
}

$file = fopen('last_sync_at.txt', 'r') or die ('unable to open file');
if(filesize('last_sync_at.txt') > 0) {
    $last_sync_at = fread($file, filesize('last_sync_at.txt'));
    // $date = date('#m/d/Y h:i:s a#');
    $sql  = "SELECT * FROM acc_monitor_log where acc_monitor_log.time > $last_sync_at";
}

$rs = odbc_exec($db, $sql);

while($arr = odbc_fetch_array($rs)) {
    print_r($arr);
    echo "\n";
}

fclose($file);
