<?php
session_start();

require_once '../../php/notif.php';
require_once '../../php/page.php';


$page = new Page("Sign in", false);

$page->readFile('/public/signup/signup.html', '<!-- WEBSITE -->', $_SERVER['HTTP_HOST']);

$page->render();