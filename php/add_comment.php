<?php
session_start();

require_once 'db-config.php';
require_once 'post.php';

if(!isset($_SESSION['id'])){
    header('Location: /login');
    die();
}

header("Location: {$_SESSION['last_page']}");

$con = connect();

$post_id = $con->real_escape_string($_POST['post_id']);
$text = $con->real_escape_string($_POST['content']);

if(empty($text)){
    $_SESSION['notification'] = "No content";
    die();
}

$insert_new_comment = "INSERT INTO comments (user_id, post_id, content, date) VALUES ('{$_SESSION['id']}', '$post_id', '$text', NOW())";

if(!$con->query($insert_new_comment)){
    $_SESSION['notification'] = "SQL error: " . $con->error;
}