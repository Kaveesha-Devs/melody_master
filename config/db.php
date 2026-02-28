<?php
$conn = new mysqli("localhost","root","","melody_master");

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}
?>