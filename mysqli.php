<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$json_credentials = file_get_contents('../keys/db_credentials.json');
$json_data = json_decode($json_credentials, true);
if($json_data == null)
{
    alert("failed to pull in db credentials");
}
// connect to database
$mysqli = new mysqli(
    $json_data["host"],
    $json_data["user"],
    $json_data["password"],
    $json_data["database"]
);
$mysqli->set_charset('utf8mb4');
printf("Success... %s\n", $mysqli->host_info);

/*
$mysqli = new mysqli(
    'localhost', $json_data["host"],
    'www-data', $json_data["user"],
    null, $json_data["password"],
    'employees' $json_data["database"]
);
*/