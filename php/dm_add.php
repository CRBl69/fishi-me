<?php
session_start();

require_once 'db-config.php';

if(!isset($_SESSION['id'])){
    die();
}

$con = connect();

$userid = $_SESSION['id'];


$json = json_decode(file_get_contents("php://input"));
$user2id = $con->real_escape_string($json->{'userid'});
$content = $con->real_escape_string($json->{'content'});

if(empty($content)){
    echo '0';
    die();
}

$req = "INSERT INTO dms (from_user, to_user, content, date) VALUES ($userid, $user2id, '$content', NOW())";

if($con->query($req)){
    echo '1';
}else{
    echo '0';
}