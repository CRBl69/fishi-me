<?php
session_start();

require_once '../../php/notif.php';
require_once '../../php/page.php';
require_once '../../php/post.php';
require_once '../../php/db-config.php';


if(isset($_GET['postid']) && is_numeric($_GET['postid'])){
    $html = "";

    $p = new Post($_GET['postid']);

    $page = new Page($p->get_title() . ' - ' . $p->get_author());
    $html .= $p->html(false);

    $page->meta("og:title", $p->get_title() . ' - ' . $p->get_author());
    $page->meta("og:site_name", 'Fishi');
    $image = $p->get_image();
    if($image){
        $page->meta("og:image", $image);
        $regex = "/\(img:(https?:\/\/[^)]+)\)/";
        $text = preg_replace(
            $regex,
            '',
            $p->get_content()
        );
        $page->meta("og:description", $p->sondage($text));
        $page->twitter_meta();
    }else{
        $page->meta("og:image", $_SERVER["REQUEST_SCHEME"]."://".$_SERVER['HTTP_HOST']."/res/logo_v2.png");
        $page->meta("og:description", $p->sondage($p->get_content()));
    }

    $page->body('<div style="display: grid; place-items: center; margin-top: 8vh;grid-template-columns: auto auto" class="post-page">');

    $page->body($html);

    $page->body('<div>');

    $page->body('<div class="comment-box post">');
    $comments = $p->get_comments();
    foreach($comments as $comment){
        $page->body($comment->html(false));
    }
    $page->body('</div>');

    $page->readFile('/public/post/comment_form.html', '<!-- POST_ID -->', $p->get_id());

    $page->body('</div>');
    $page->body('</div>');
    $page->script('/res/js/copyLink.js');
    $page->render();
}else{
    echo 'no post with given postid';
}
