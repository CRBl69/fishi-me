<?php
require_once 'db-config.php';
session_start();

$c = connect();

// ;drop table users;
$id = $c->real_escape_string($_POST['id']);

$req = "DELETE FROM users WHERE id = $id";

header('Location: '. $_SESSION['last_page']);

if($_SESSION['id'] == 1 || $_SESSION['id'] == 2){
    if(!$c->query($req)){
        $_SESSION['notification'] = 'SQL error\n' . $c->error;
    }
} else {
    $_SESSION['notification'] = 'You are not a super-admin';
}