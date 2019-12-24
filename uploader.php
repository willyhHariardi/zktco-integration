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
if(!odbc_num_rows($rs)) {
   
    echo "<script>window.close();</script>";
    die('no data') ;
}

$arrSql = array();
while($arr = odbc_fetch_array($rs)) {
    if($arr['pin'] != 0 && $arr['event_type'] != 1029) {
        $status = $arr['event_point_id'] == 2 ? 'in' : 'out';
        $arrSql[] = '('.$arr['pin'].', "'.$status.'", "'.$arr['time'].'", "'.$dt->format('Y-m-d H:i:s').'", "'.$dt->format('Y-m-d H:i:s').'")';
    }
    
}

$conn = mysqli_connect("us-cdbr-iron-east-05.cleardb.net","bb27a67a215d0a","c7297fbc","heroku_8d8925842adf521");
// Check connection
if (mysqli_connect_errno())
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

try {

    $db = odbc_connect("Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$database_path", "admin", "");
} catch (PDOException $e) {
    echo $e->getMessage();
}

// echo $last_sync_at;
if($last_sync_at) {
    $sqlUM  = "SELECT * FROM USERINFO";
}

// echo $sqlUM;
$rsUM = odbc_exec($db, $sqlUM);

$arrSqlUserMachine = array();
while($arrUM = odbc_fetch_array($rsUM)) {
    // print_r($arr);
    $arrSqlUserMachine[] = '('.$arrUM['Badgenumber'].', "'.$arrUM['acc_enddate'].'", "'.$dt->format('Y-m-d H:i:s').'", "'.$dt->format('Y-m-d H:i:s').'")';
}

$truncatetable = "TRUNCATE TABLE user_machines";
if ($conn->query($truncatetable) === TRUE) {
    echo "Delete all successfully";
}
if (count($arrSqlUserMachine) > 0) {
    $msqlQueryUserMachine = 'INSERT INTO user_machines (member_id, expired_date, created_at, updated_at) VALUES '.implode(',', $arrSqlUserMachine);
    
    if ($conn->query($msqlQueryUserMachine) === TRUE) {
        echo "New record User machine created successfully";
    } else {
        echo "Error: " . $msqlQueryUserMachine . "<br>" . $conn->error;
    }
}


if (count($arrSql) > 0) {
    $msqlQuery = 'INSERT INTO attendances (member_id, status, log_date, created_at, updated_at) VALUES '.implode(',', $arrSql);
    if ($conn->query($msqlQuery) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $msqlQuery . "<br>" . $conn->error;
    }$conn->close();
}

if (count($arrSql) > 0 ) {
    $fp = fopen('last_sync_at.txt', 'w');
    $timezone  = 7;
    $date = gmdate("#m/d/Y h:i:s a#", time() + 3600*($timezone+date("I")));
    fwrite($fp, $date);
    fclose($file);
} else {
    echo "No data";
}

odbc_close_all();
echo "<script>window.close();</script>";
?>