<?php
session_start();
require_once 'db-config.php';

if(!isset($_SESSION['id'])){
    header('Location: /login');
    die();
}

$con = connect();

$follower = $con->real_escape_string($_POST['follower']);
$following = $con->real_escape_string($_POST['following']);

$req = "INSERT INTO follows (follower, following) VALUES ($follower, $following)";
session_start();
if($con->query($req)){
    $_SESSION['notification'] = "Followed !";
    header("Location: /profile?userid=" . $following);
}else{
    $_SESSION['notification'] = "Unknown error";
    header("Location: /profile?userid=" . $following);
}