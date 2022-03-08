<?php

$db_hostname = '198.71.227.98';
$db_database = "tasks";
$db_username = "kuhail";
$db_password = "Klinsman4";


$connection = mysqli_connect($db_hostname, $db_username, $db_password, $db_database);

if (!$connection)
    die("Unable to connect to MySQL: " . mysqli_connect_errno());


?>