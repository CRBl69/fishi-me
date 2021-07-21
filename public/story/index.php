<?php

session_start();

require_once '../../php/db-config.php';
require_once '../../php/page.php';

if(!isset($_SESSION['id'])){
    header('Location: /home');
    die();
}

$page = new Page("Add Story");

$page->readFile("/public/story/story.html");

$page->render();