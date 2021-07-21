<?php
session_start();

require_once '../../php/notif.php';
require_once '../../php/page.php';


$page = new Page("Login", false);

// If user is connected, redirect to home page
if(!empty($_SESSION['id'])){
    header('Location: /home');
}

$page->meta("og:image", $_SERVER["REQUEST_SCHEME"]."://".$_SERVER['HTTP_HOST']."/res/fishy512x512.png");
$page->meta("og:title", 'Login - Fishi');
$page->meta("og:site_name", 'Fishi');
$page->meta("og:description", 'Welcome to Fishi ! The bestest social network');

$page->readFile('/public/login/login.html', '<!-- WEBSITE -->', $_SERVER['HTTP_HOST']);

$page->render();