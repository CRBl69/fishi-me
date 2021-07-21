<?php
session_start();

require_once '../../php/page.php';
require_once '../../php/user.php';
require_once '../../php/db-config.php';

if(!isset($_SESSION['id'])){
    header('Location: /login');
    die();
}

$con = connect();

if(isset($_GET['userid'])){
    $userid = $con->real_escape_string($_GET['userid']);
}
else $userid = 0;

$user = new User($userid);

$page = new Page("DMs - ".$user->get_username());

// $page->body("DMs with " . $user->link_profile());

$contacts = "";

$self = new User($_SESSION['id']);

$contacts .= '<input type="text" id="search-dm" placeholder="press c to seach here" style="margin-left: 10px;width: 15vw"><div style="margin-top: 10px">';


foreach($self->get_dm_users() as $follow){
    $is_unread = false;

    $req = "SELECT * FROM dms WHERE from_user = ". $follow->get_id() ." AND to_user = ". $self->get_id() ." AND seen = 0";
    if($result = $con->query($req)){
        while($unread_dm = $result->fetch_assoc()){
            $is_unread = true;
        }
    }
    if($user->get_id() == $follow->get_id()){
        $contacts .= '<div class="contact" style="margin-left: 50px">';
    }else if($is_unread){
        $contacts .= '<div class="contact" style="border: 1px solid red">';
    }else{
        $contacts .= '<div class="contact">';
    }
    $contacts .= $follow->link_dm();
    $contacts .= '</div>';
}
$contacts .= '</div>';

$page->readFile('/public/dm/dm.html', '<!-- CONTACTS -->', $contacts, '<!-- USERNAME -->', $user->get_username(), '<!-- WEBSITE -->', $_SERVER['HTTP_HOST']);
$page->script('/res/js/dm.js');
$page->script('/res/js/copyLink.js');

$page->render();