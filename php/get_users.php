<?php
require_once 'db-config.php';

$con = connect();

$searchString = $con->real_escape_string($_GET['search']);

$req = "SELECT username, id FROM users WHERE locate('$searchString', username)>0 ORDER BY locate('$searchString', username)";

$users = [];

$c = 0;

if($result = $con->query($req)){
    while($c < 5 && ($user = $result->fetch_assoc())){
        $users[$c] = $user;
        $c++;
    }
}

echo json_encode($users);
