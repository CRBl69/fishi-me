<?php

require_once 'db-config.php';

$con = connect();

$postid = $con->real_escape_string($_GET['postid']);

$req = "SELECT * FROM sondages WHERE postid = $postid";

if($res = $con->query($req)){
    $echo = [];
    $c = 0;
    while($r = $res->fetch_assoc()){
        $echo[$c++] = $r;
    }
    echo json_encode($echo);
}