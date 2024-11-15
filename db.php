<?php
$hostname = "localhost";
$username = "root";
$password = "december12";
$dbname = "welearn";


$conn = mysqli_connect($hostname, $username, $password, $dbname);


if (!$conn) {
    die ("Connection failed: " . mysqli_connect_error());
}
?>