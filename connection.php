<?php
$host = "localhost";
$username = "root";
$password = "toor";
$my_db = "cropping";
$connection = new mysqli($host, $username, $password, $my_db);

if (!$connection) {
    echo "An error occured";
}
?>