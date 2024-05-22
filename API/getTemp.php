<?php
include __DIR__."/../secret.php";

header('Content-Type: application/json; charset=utf-8');

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

function getTemperature($Connection) {
    $Query = "
        SELECT Temperature FROM coldroomtemperatures WHERE ColdRoomSensorNumber = 5;
    ";
    $statement = mysqli_prepare($Connection, $Query);
    mysqli_stmt_execute($statement);
    $R = mysqli_stmt_get_result($statement);
    $R = mysqli_fetch_all($R, MYSQLI_ASSOC);
    return $R[0];
}

echo json_encode(getTemperature($Connection));