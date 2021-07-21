<?php

require_once 'db-config.php';

if(!isset($_SESSION['id'])){
    header('Location: /login');
    die();
}

$con = connect();

$follower = $con->real_escape_string($_POST['follower']);
$following = $con->real_escape_string($_POST['following']);

$req = "DELETE FROM follows WHERE follower = $follower AND following = $following";

session_start();
if($con->query($req)){
    $_SESSION['notification'] = "Unfollowed !";
    header("Location: /profile?userid=" . $following);
}else{
    $_SESSION['notification'] = "Unknown error";
    header("Location: /profile?userid=" . $following);
}