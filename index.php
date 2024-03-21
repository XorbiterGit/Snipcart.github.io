<?php
session_start();

//include functions and connect to the database using PDO MySQL
include 'functions.php';
$pdo = pdo_connect_mysql();

$page = isset($_GET['page']) && file_exists($_GET['page'] . '.php') ? $_GET['page'] : 'home';
//Include and show requested page
include $page . '.php';
