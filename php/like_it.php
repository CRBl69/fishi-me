<?php
session_start();

require_once 'db-config.php';

if(!isset($_SESSION['id'])){
    header('Location: ' . $_SESSION['last_page']);
    $_SESSION['notification'] = "You are not logged in !";
    die();
}

$con = connect();

$postid = $con->real_escape_string($_GET['postid']);

$req = "SELECT userid, postid FROM likes WHERE userid = {$_SESSION['id']} AND postid = $postid";
if($result = $con->query($req)){
    if($res = $result->fetch_assoc()){
        $con->query("DELETE FROM likes WHERE userid = {$_SESSION['id']} AND postid = $postid");
        echo '1';
    } else {
        $insert_like = "INSERT INTO likes (userid, postid) VALUES ('{$_SESSION['id']}', $postid)";
        if (!$con->query($insert_like)) {
            $_SESSION['notification'] = "SQL error: " . $con->error;
        }else{
            echo '1';
        }
    }
} else {
    $_SESSION['notification'] = "SQL error: $postid " . $con->error;
}
