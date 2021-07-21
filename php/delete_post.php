<?php
session_start();

require_once 'db-config.php';

if(!isset($_SESSION['id'])){
    header('Location: /login');
    die();
}

$data = json_decode(file_get_contents('php://input'));

$con = connect();

$postid = $con->real_escape_string($data->{'postid'});

$req = "DELETE FROM posts WHERE id = $postid AND userid = {$_SESSION['id']}";

if($_SESSION['moderator'] == 1){
    $req = "DELETE FROM posts WHERE id = $postid";
}

if($con->query($req)){
    echo '1';
}else{
    echo '0';
}