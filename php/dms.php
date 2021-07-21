<?php
session_start();

require_once 'db-config.php';
require_once 'parser.php';

if(!isset($_SESSION['id'])){
    die();
}

$con = connect();


$userid = $_SESSION['id'];
if(isset($_GET['userid'])) $user2id = $con->real_escape_string($_GET['userid']);
else $user2id = 0;
$last = $con->real_escape_string($_GET['last']);

if($last != 0){
    $req = "SELECT dms.id AS dm_id, content, username, users.id, date, seen FROM dms INNER JOIN users ON users.id = dms.from_user WHERE ((from_user = $userid AND to_user = $user2id) OR (from_user = $user2id AND to_user = $userid)) AND dms.id > $last ORDER BY date";

}
else{
    $req = "SELECT dms.id AS dm_id, content, username, users.id, date, seen FROM dms INNER JOIN users ON users.id = dms.from_user WHERE from_user = $userid AND to_user = $user2id OR from_user = $user2id AND to_user = $userid ORDER BY date";
}


$msgs = [];

if($result = $con->query($req)){
    $parser = new Parser();
    while($msg = $result->fetch_assoc()){
        $msg['content'] = htmlspecialchars($msg['content']);
        $msg['content'] = $parser->at($msg['content']);
        $msg['content'] = $parser->markdown($msg['content']);
        $msg['content'] = $parser->links($msg['content']);
        $msg['content'] = $parser->images($msg['content']);
        array_push($msgs, $msg);
    }
}else{
    echo json_encode($con->error);
}
$req = "UPDATE dms SET seen = 1 WHERE from_user = $user2id AND to_user = $userid";
$con->query($req);
echo json_encode($msgs);