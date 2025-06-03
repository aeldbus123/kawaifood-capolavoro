<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

ini_set("displays-error",1);
error_reporting(E_ALL);
require_once(__DIR__."/utils.php");

$conn=dbConnect();

$query="SELECT * FROM products";

$result=$conn->query($query);

$product=[];
if($result->num_rows>0){
    while($row=$result->fetch_assoc()){
        $product[]=$row;
    }
       
    
}

echo json_encode($product);

?>