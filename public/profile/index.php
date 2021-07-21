<?php
session_start();

require_once '../../php/notif.php';
require_once '../../php/page.php';
require_once '../../php/db-config.php';
require_once '../../php/user.php';
$_SESSION['last_page'] = $_SERVER['SCRIPT_NAME'];

$con = connect();

if(($_SERVER['SCRIPT_URL'] == '/profile/' || $_SERVER['SCRIPT_URL'] == '/profile') && isset($_SESSION['username'])){
    header('Location: /profile/' . $_SESSION['username']);
    die();
}

$user = new User(str_replace("/profile/", "", $_SERVER['SCRIPT_URL']));

if ($user->valid()) {
    $userid = $user->get_id();

    $page = new Page("Profile - " . $user->get_username());

    $page->meta("og:title", 'Profile - ' . $user->get_username());
    $page->meta("og:site_name", 'Fishi');
    if($profile_picture = $user->get_profile_picture()){
        $page->meta("og:image", $profile_picture);
    }else{
        $page->meta("og:image", $_SERVER["REQUEST_SCHEME"]."://".$_SERVER['HTTP_HOST']."/res/fishy512x512.png");
    }
    $page->twitter_meta();

    $page->body('<div class="profile-grid">');


    $page->body('<div class="profile-user sticky-element">');

    $page->body('<div>');
    $page->body($user->html());
    $page->body('</div>');

    $page->body('<div style="height: 20px"></div>');
    $nb_followers = 0;
    $followers_html = "";
    foreach($user->get_followers() as $follower){
        $nb_followers++;
        $followers_html .= $follower->link_profile() . " ";
    }
    $page->body("<div id=\"div-followers\"><p>Followers: $nb_followers</p>$followers_html</div>");

    $nb_following = 0;
    $following_html = "";
    foreach($user->get_following() as $following){
        $nb_following++;
        $following_html .= $following->link_profile() . " ";
    }
    $page->body("<div id=\"div-following\"><p>Following: $nb_following</p>$following_html</div>");

    $page->meta("og:description", htmlspecialchars($user->get_description()) . "\nFollowing: $nb_following, Followers: $nb_followers");

    $get_posts = "SELECT id FROM posts WHERE userid = $userid";
    $html = "";

    foreach($user->get_posts() as $p){
        $html .= $p->html();
    }
    $page->body("</div>");


    $page->body('<div id="middle-feed">');

    $page->body('<div style="height: 20px"></div>');
    $page->body($html);
    $page->body("</div>");


    $page->body("<div class=\"post-from-container sticky-element\">");
    if(isset($_SESSION['id']) && $_SESSION['id'] == $userid){
        $page->readFile("/public/profile/profile.html", "<!-- USERNAME -->", $_SESSION['username']);
    }
    $page->body("</div>");

    $page->body('</div>');
} else {
    $page = new Page("Profile - ERROR");
    $page->body("<h1>ERROR</h1><br><h2>No profile found with given id</h2>");
}
$page->script('/res/js/copyLink.js');
$page->render();
