<?php
$tz = 'Asia/Jakarta';
$timestamp = time();
$dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
$dt->setTimestamp($timestamp); //adjust the object to correct timestamp

$database_path = realpath("C:/Program Files (x86)/ZKTeco/ZKAccess3.5/access.mdb");
if (!file_exists($database_path)) {
    die("Could not find database file.");
}

try {
    $db = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$database_path", "admin", "");
} catch (PDOException $e) {
    echo $e->getMessage();
}

$file = fopen('last_sync_at.txt', 'r') or die ('unable to open file');
if(filesize('last_sync_at.txt') > 0) {
    $last_sync_at = fread($file, filesize('last_sync_at.txt'));
    $sql  = "SELECT * FROM acc_monitor_log where acc_monitor_log.time > $last_sync_at";
}

$rs = odbc_exec($db, $sql);
if(odbc_num_rows($rs)) {
    $fp = fopen('last_sync_at.txt', 'w');
    $date = date('#m/d/Y h:i:s a#');
    fwrite($fp, $date);
    fclose($file);
    echo "<script>window.close();</script>";
    die('no data') ;
}
$arrSql = array();
while($arr = odbc_fetch_array($rs)) {
    $status = $arr['event_point_id'] == 2 ? 'in' : 'out';
    $arrSql[] = '('.$arr['pin'].', "'.$status.'", "'.$arr['time'].'", "'.$dt->format('Y-m-d H:i:s').'", "'.$dt->format('Y-m-d H:i:s').'")';
}

$conn = mysqli_connect("localhost","root","","muscle_lab");
// Check connection
if (mysqli_connect_errno())
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
$msqlQuery = 'INSERT INTO attendances  (member_id, status, log_date, created_at, updated_at) VALUES '.implode(',', $arrSql);
if ($conn->query($msqlQuery) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $msqlQuery . "<br>" . $conn->error;
}$conn->close();

$fp = fopen('last_sync_at.txt', 'w');
$date = date('#m/d/Y h:i:s a#');
fwrite($fp, $date);
fclose($file);

echo "<script>window.close();</script>";
?>