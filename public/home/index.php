<?php
session_start();

require_once '../../php/page.php';
require_once '../../php/post.php';
require_once '../../php/notif.php';
require_once '../../php/db-config.php';
require_once '../../php/story.php';

if (isset($_SESSION['id'])) {
    $page = new Page("Home - " . $_SESSION['username']);

    $con = connect();

    // Deleting stories older than 5 minutes
    $five_mins_ago = date_sub(new DateTime(), new DateInterval("PT5M"))->format('Y-m-d H:i:s');

    $con->query("DELETE FROM stories WHERE date < '$five_mins_ago'");

    // Stories
    $html = '<div class="stories" style="margin-top: 20px">
        <div class="story story-plus" onclick="location.href=\'/story\'">
            <span class="material-icons">add</span>
        </div>';

    $req = "SELECT id FROM stories
        WHERE user_id IN (SELECT following FROM follows WHERE follower = {$_SESSION['id']}) OR user_id = {$_SESSION['id']}
        ORDER BY date";

    if($stories = $con->query($req)){
        while($story = $stories->fetch_assoc()){
            $s = new Story($story['id']);
            $html .= $s->html();
        }
    }
    $html .= "</div>";

    // Posts
    if(isset($_GET['page']) && is_numeric($_GET['page'])){
        $page_number = $_GET['page'];
    }else $page_number = 1;

    $posts_number = $page_number * 20;

    $req = "SELECT id FROM posts
        WHERE userid IN (SELECT following FROM follows WHERE follower = {$_SESSION['id']}) OR userid = {$_SESSION['id']}
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
    foreach($user->get_following() as $follower){
        $page->body('<div style="display: flex; align-items: center; margin: 3px">');
        $page->body('<img width="32" style="border-radius:100%; margin-right: 5px" src="'.$follower->get_profile_picture().'">');
        $page->body($follower->link_profile());
        $page->body("</div>");
    }
    $page->body("</div>");
    $page->body("</div>");

    // The middle part of the page
    $page->body('<div id="middle">');
    $page->body($html);
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
    if($page_number - 1 > 0) $page->body('<button style="position: fixed;bottom: 1vh;left: 1vh;" onclick="location.href=\'/home?page='.($page_number - 1).'\'">Previous page</button>');
    $page->body('<button style="position: fixed;bottom: 1vh;right: 1vh;" onclick="location.href=\'/home?page='.($page_number + 1).'\'">Next page</button>');

    // Scripts
    $page->script('/res/js/copyLink.js');
    $page->script('/res/js/story.js');

    $page->render();
} else {
    header('Location: /login');
}
