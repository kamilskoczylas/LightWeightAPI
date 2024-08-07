<?php

if (!defined('MAIN_FILE_LOADED'))
    die('Direct access not allowed');

$servername = "127.0.0.1";
$username = "root";
$password = "";
$database = "mydatabase";
$port = "3306";

global $PDO;
$PDO = new PDO("mysql:host=$servername;dbname=$database;charset=utf8mb4", $username, $password); 

$PDO->exec("SET NAMES utf8mb4");

// set the PDO error mode to exception
$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
   
