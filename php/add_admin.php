<?php
require_once 'db-config.php';
session_start();

$con = connect();

$id = $con->real_escape_string($_POST['id']);

$req = "UPDATE users SET moderator = 1 WHERE id = $id";

header("Location: {$_SESSION['last_page']}");

if($_SESSION['id'] == 1 || $_SESSION['id'] == 2){
    if(!$con->query($req)){
        $_SESSION['notification'] = 'SQL error\n' . $con->error;
    }
} else {
    $_SESSION['notification'] = "You are not a super-admin";
}