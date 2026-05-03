<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'smart_farm';


$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if(!$conn){ 
    die('DB Connection error: '.mysqli_connect_error());
 }
?>