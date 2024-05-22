<?php
include __DIR__."/../secret.php";

$data = '';
header('Content-Type: application/json; charset=utf-8');

if(!isset($_POST['Temp']) && !isset($_POST['TempAPIPassword'])){
    echo json_encode(400); return; // 403 is de code voor incorrect response
}
if ($_POST['TempAPIPassword'] != 'Vlaai123456789!!!!!!!!!!!!!!!') {
    echo json_encode(401); return; // 401 is de code voor incorrecte wachtwoord
}

//then connect to NerdygadgetsDB
$Connection = null;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Set MySQLi to throw exceptions
try {
    $Connection = mysqli_connect("localhost", "root", getSQLPassword(), "nerdygadgets");
    mysqli_set_charset($Connection, 'latin1');
    $DatabaseAvailable = true;
} catch (mysqli_sql_exception $e) {
    $DatabaseAvailable = false;
}
if (!$DatabaseAvailable) {
    echo json_encode(500); // 500 is de code voor server error
    die();
}

$timeStamp = date("Y-m-d H:i:s");

// Set old temperature in the archive
$Query = "
    INSERT INTO coldroomtemperatures_archive
    SELECT * FROM coldroomtemperatures 
    WHERE ColdRoomSensorNumber = 5;
";
$statement = mysqli_prepare($Connection, $Query);
mysqli_stmt_execute($statement);
$R2 = mysqli_stmt_get_result($statement);

// Update old temperature with new data
$Query = "
    UPDATE coldroomtemperatures
    SET ColdRoomTemperatureID = ColdRoomTemperatureID +1,RecordedWhen = TIMESTAMP(?),Temperature = ?,ValidFrom =TIMESTAMP(?),ValidTo =TIMESTAMP('9999-12-31','23:59:59')
    WHERE ColdRoomSensorNumber = 5; 
";
$tempInt = floatval($_POST['Temp']);

$statement = mysqli_prepare($Connection, $Query);
mysqli_stmt_bind_param($statement, 'sds', $timeStamp, $tempInt, $timeStamp);
mysqli_stmt_execute($statement);
$R2 = mysqli_stmt_get_result($statement);


echo json_encode(200); 
die();
?>