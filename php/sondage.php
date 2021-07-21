<?php
session_start();

require_once 'db-config.php';

if(!isset($_SESSION['id'])){
    header('Location: /login');
    die();
}
$con = connect();

$postid = $con->real_escape_string($_POST['postid']);
$vote_val = $con->real_escape_string($_POST[$_POST['postid']]);


$req = "SELECT * FROM sondages WHERE postid = $postid AND voter = {$_SESSION['id']}";

header('Location: '.$_SESSION['last_page']);

if(!$r = $con->query($req)->fetch_assoc()){
    $req = "INSERT INTO sondages (postid, voter, vote_value) VALUES ($postid, {$_SESSION['id']}, '$vote_val')";
    if(!$con->query($req)){
        $_SESSION['notification'] = "SQL Error: ".$con->error;
    }
}else{
    $req = "DELETE FROM sondages WHERE postid = $postid AND voter = {$_SESSION['id']}";
    if($con->query($req)){
        $req = $req = "INSERT INTO sondages (postid, voter, vote_value) VALUES ($postid, {$_SESSION['id']}, '$vote_val')";
        if(!$con->query($req)){
            $_SESSION['notification'] = "SQL Error: ".$con->error;
        }
    }else{
        $_SESSION['notification'] = "SQL Error: ".$con->error;
    }
}