<?php
session_start();
if(isset($_GET['theme'])){
    setcookie('theme', $_GET['theme'], time()+60*60*24*30, '/');
}

header('Location: '. $_SESSION['last_page']);