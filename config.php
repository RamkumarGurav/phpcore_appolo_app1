<?php
//{--------------DB DETAILS--------------
$servername = "localhost";
$server_username = "root";
$server_password = "";
$dbname = "appolo_album_db";
//--------------------------------------------------}


$conn = new PDO("mysql:host=$servername;dbname=$dbname", $server_username, $server_password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
return $conn;