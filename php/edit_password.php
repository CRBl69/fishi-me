<?php

require_once 'db-config.php';

// Checks if the username is valid
function validate_username($username){
    if(strlen($username) < 4){
        return false;
    }
    if(!isset($username)){
        return false;
    }
    if(empty($username)){
        return false;
    }
    if(preg_match("/[^A-Za-z0-9_]/", $username)){
        return false;
    }
    if(!preg_match("/[a-zA-Z]/", $username)) return false;
    return true;
}

// Uses regex to verify that the given email has a valid format
function validate_email($email){
    $exp = "/^[^@]+@[^@]*\.[^@]*$/";
    if(!isset($email)){
        return false;
    }
    if(preg_match($exp, $email)){
        return true;
    }
    return false;
}

// Uses regex to verify if the password contains at least an uppercase letter, a lowercase letter, a number and another special symbol
function validate_password($password){
    $number = "/[0-9]/";
    $letter = "/[a-z]/";
    $LETTER = "/[A-Z]/";
    $special = "/[^0-9a-zA-Z]/";

    if(!isset($password)){
        return false;
    }
    if(!preg_match($number, $password)){
        return false;
    }
    if(!preg_match($letter, $password)){
        return false;
    }
    if(!preg_match($LETTER, $password)){
        return false;
    }
    if(!preg_match($special, $password)){
        return false;
    }
    if(strlen($password) < 10){
        return false;
    }

    return true;
}

session_start();

$c = connect();

$old_pw = $c->real_escape_string($_POST['old_password']);
$new_pw = $c->real_escape_string($_POST['new_password']);
$confirm_pw = $c->real_escape_string($_POST['confirm_password']);

$req = "SELECT * FROM users WHERE id = {$_SESSION['id']}";

header('Location: '. $_SESSION['last_page']);

if($res = $c->query($req)){
    $user = $res->fetch_assoc();
    if(password_verify($old_pw, $user['password']) && $new_pw == $confirm_pw &&
    validate_username($_POST['username']) && validate_email($_POST['email']) && validate_password($_POST['password'])) {
        $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);
        $req = "UPDATE users SET password = '$new_hash' WHERE id = {$_SESSION['id']}";
        if(!$c->query($req)){
            $_SESSION['notification'] = "SQL error\n".$c->error;
        }
    } else {
        $_SESSION['notification'] = "Wrong password or new passwords do not match or password does not meet security requirements\nThe password must contain the following: at least 10 characters, a lowercase character, an uppercase character, a number and a special character";
    }
} else {
    $_SESSION['notification'] = "SQL error\n".$c->error;
}