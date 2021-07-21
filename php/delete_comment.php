<?php
require_once 'db-config.php';
session_start();

$con = connect();

$id = $con->real_escape_string($_POST['id']);


header("Location: {$_SESSION['last_page']}");

if($_SESSION['moderator'] == 1){
    if(!$con->query("DELETE FROM comments WHERE id = $id")){
        $_SESSION['notification'] = 'SQL error\n' . $con->error;
    }
} else {
    if(!$con->query("DELETE FROM comments WHERE id = $id AND userid = '{$_SESSION['id']}'")){
        $_SESSION['notification'] = 'SQL error\n' . $con->error;
    }
}