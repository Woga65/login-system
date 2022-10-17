<?php

$serverName = "localhost";
$dBUserName = "your-user-name";
$dBPassword = "your-password";
$dBName = "your-database-name";

$conn = mysqli_connect($serverName, $dBUserName, $dBPassword, $dBName);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}