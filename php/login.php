<?php
session_start();

require_once 'db-config.php';

$con = connect();

$email = $con->real_escape_string($_POST['email']);
$password = $con->real_escape_string($_POST['password']);

$req = "SELECT username, id, password, moderator FROM users WHERE email = '$email'";

if($result = $con->query($req)->fetch_assoc()){
    if(password_verify($password, $result['password'])){
        $_SESSION['user'] = $email;
        $_SESSION['id'] = $result['id'];
        $_SESSION['moderator'] = $result['moderator'];
        $_SESSION['username'] = $result['username'];
        header('Location: /home');
    }else{
        $_SESSION['notification'] = 'Wrong passwords';
        header('Location: /login');
    }
}else{
    $_SESSION['notification'] = 'Email not found';
    header('Location: /login');
}