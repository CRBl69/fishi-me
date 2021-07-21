<?php
session_start();

require_once '../../php/page.php';
require_once '../../php/db-config.php';
require_once '../../php/user.php';

if(!isset($_SESSION['id'])){
    header('Location: /login');
    exit;
}

$page = new Page("Edit profile");

$page->body('<div style="display: grid; grid-template-columns: 1fr 1fr; grid-gap: 50px; place-items: center">');
$page->readFile("/public/edit_profile/form.html");
$page->body('<form action="/php/edit_password.php" style="display: flex; flex-direction: column" method="POST">
<input type="password" name="old_password" placeholder="Old password">
<input type="password" name="new_password" placeholder="New password">
<input type="password" name="confirm_password" placeholder="Confirm new password">
<input type="submit" value="Let\'s go">
</form>');
$page->body('</div>');

$styles_dir = dir('./../../res/css/themes');

$styles = '<ul>';

while(false !== ($entry = $styles_dir->read())){
    if($entry != '.' && $entry != '..'){
        $styles .= '<li style="margin-left: 40px">
        <a href="/php/set_theme.php?theme='.urlencode($entry).'">'.$entry.'</a>
        </li>';
    }
}

$page->body('<p style="margin-left: 5px">Select a theme</p>');

$page->body($styles . '</ul>');

$page->render();