<?php

require_once './db-config.php';

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


if(validate_username($_POST['username']) && validate_email($_POST['email']) && validate_password($_POST['password'])){
    $con = connect();

    $username = $con->real_escape_string($_POST['username']);
    $password = $con->real_escape_string(password_hash($_POST['password'], PASSWORD_DEFAULT));
    $email = $con->real_escape_string($_POST['email']);

    $verify_email = "SELECT id FROM users WHERE LOWER(email) = LOWER('$email') OR  LOWER(username) = LOWER('$username')";

    session_start();

    if($result = $con->query($verify_email)){
        if($result->{'num_rows'} == 0){
            $insert_new_user = "INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')";

            if($con->query($insert_new_user)){
                $_SESSION['notification'] = "Account created";
                header("Location: /login");

                // Follows the creators by default *wink*
                $insert_id = $con->insert_id;
                $req = "INSERT INTO follows (following, follower) VALUES (1, $insert_id)";
                $con->query($req);
                $req = "INSERT INTO follows (following, follower) VALUES (2, $insert_id)";
                $con->query($req);
            }else{
                $_SESSION['notification'] = "SQL error: " . $con->error;
                header("Location: /signup");
            }
        } else {
            $_SESSION['notification'] = "Email or username already used";
            header("Location: /signup");
        }
    }else{
        $_SESSION['notification'] = "Unknown error";
        header("Location: /signup");
    }
}else{
    session_start();
    $notif = "";
    if(!validate_username($_POST['username'])){
        $notif .= "Username is too short or contains a space\n";
    }
    if(!validate_email($_POST['email'])){
        $notif .= "Email is not valid\n";
    }
    if(!validate_password($_POST['password'])){
        $notif .= "The password must contain the following: at least 10 characters, a lowercase character, an uppercase character, a number and a special character\n";
    }
    $_SESSION['notification'] = $notif;
    header("Location: /signup");
}

?>