<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

ini_set("displays-error",1);
error_reporting(E_ALL);

function dbConnect(){
    $servername="localhost";
    $username="root";
    $password="";
    $database="kawaii_food";

    $conn = new mysqli($servername,$username,$password,$database);

    if($conn->connect_error){
        die("Conneciron failed " . $conn->connect_error);
    }

   

    return $conn;

}
?>