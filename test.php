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
    echo $last_sync_at;
    $sqlUM  = "SELECT * FROM USERINFO where USERINFO.HIREDDAY < $last_sync_at";
}

echo $sqlUM;
$rsUM = odbc_exec($db, $sqlUM);
$tz = 'Asia/Jakarta';
$timestamp = time();
$dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
$dt->setTimestamp($timestamp); 


$conn = mysqli_connect("localhost","root","","muscle_lab");
// Check connection
if (mysqli_connect_errno())
{
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

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
    }$conn->close();
}
