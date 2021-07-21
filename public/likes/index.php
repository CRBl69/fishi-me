<?php
session_start();

require_once '../../php/page.php';
require_once '../../php/post.php';
require_once '../../php/notif.php';
require_once '../../php/db-config.php';
require_once '../../php/story.php';

if (isset($_SESSION['id'])) {
    $page = new Page("My likes - " . $_SESSION['username']);

    $con = connect();

    if(isset($_GET['page']) && is_numeric($_GET['page'])){
        $page_number = $_GET['page'];
    }else $page_number = 1;

    $posts_number = $page_number * 20;

    $html = "<div style=\"margin-top: 20px\">";

    // Posts
    $req = "SELECT id FROM posts
    WHERE id IN (SELECT postid FROM likes WHERE userid = {$_SESSION['id']})
    ORDER BY date DESC LIMIT $posts_number OFFSET " . ($posts_number - 20);

    if ($result = $con->query($req)) {
        while ($post = $result->fetch_assoc()) {
                $p = new Post($post['id']);
                $html .= $p->html();
        }
    }

    $user = new User($_SESSION['id']);

    $page->body("<div id=\"home-container\">");

    // The left part of the page
    $page->body("<div id=\"left\">");
    $page->body("<div class=\"sticky-element main-page-friends\">");
    foreach($user->get_followers() as $follower){
        $page->body('<div style="display: flex; align-items: center; margin: 3px">');
        $page->body('<img width="32" style="border-radius:100%; margin-right: 5px" src="'.$follower->get_profile_picture().'">');
        $page->body($follower->link_profile());
        $page->body("</div>");
    }
    $page->body("</div>");
    $page->body("</div>");

    // The middle part of the page
    $page->body('<div id="middle">');
    $page->body($html . '</div>');
    $page->body('</div>');

    // The right part of the page
    $page->body("<div id=\"right\">");
    $page->body("<div class=\"sticky-element main-page-profile\">");
    $page->body($user->html());
    $page->readFile('/public/profile/profile.html', "<!-- USERNAME -->", $_SESSION['username']);
    $page->body("</div>");
    $page->body("</div>");

    $page->body("</div>");

    // Bottom next and previous page buttons
    if($page_number - 1 > 0) $page->body('<button style="position: fixed;bottom: 1vh;left: 1vh;" onclick="location.href=\'/likes?page='.($page_number - 1).'\'">Previous page</button>');
    $page->body('<button style="position: fixed;bottom: 1vh;right: 1vh;" onclick="location.href=\'/likes?page='.($page_number + 1).'\'">Next page</button>');

    // Scripts
    $page->script('/res/js/copyLink.js');

    $page->render();
} else {
    header('Location: /login');
}
