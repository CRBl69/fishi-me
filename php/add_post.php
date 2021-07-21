<?php
session_start();

require_once 'db-config.php';

if(!isset($_SESSION['id'])){
    header('Location: /login');
    die();
}

if(empty($_POST['title']) || empty($_POST['contenu'])){
    $_SESSION['notification'] = "No title or content";
    header('Location: ' . $_SESSION['last_page']);
    die();
}

$content = $_POST['contenu'];

if($_FILES["profile_picture"]['size'] > 0){
    $image = file_get_contents($_FILES["img"]['tmp_name']);

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.imgur.com/3/image',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('image' => base64_encode($image), 'type' => "base64"),
        CURLOPT_HTTPHEADER => array('Authorization: Client-ID none of your business'),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode($response);


    $link = $response->{"data"}->{"link"};
    $content .= "\n(img:$link)";
}

header('Location: ' . $_SESSION['last_page']);

$con = connect();

$title = $con->real_escape_string($_POST['title']);
$text = $con->real_escape_string($content);
$date = new DateTime();

$insert_new_post = "INSERT INTO posts (title, text, userid, date) VALUES ('$title', '$text', '{$_SESSION['id']}', NOW())";

if(!$con->query($insert_new_post)){
    $_SESSION['notification'] = "SQL error: " . $con->error;
}
