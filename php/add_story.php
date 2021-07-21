<?php

require_once 'db-config.php';
session_start();

$con = connect();

if(!isset($_POST['img']) || !isset($_SESSION['id']) || !isset($_FILES["img_file"])){
    header("Location: " . $_SESSION['last_page']);
    die();
}
if(!empty($_POST['img'])){
    if(preg_match('/^https?:\/\/[^"]+$/', $_POST['img']) == 0){
        $_SESSION['notification'] = "Not a link";
        header("Location: " . $_SESSION['last_page']);
        die();
    }

    $link = $con->real_escape_string($_POST['img']);

    $req = "INSERT INTO stories (user_id, content) VALUES ({$_SESSION['id']}, '$link')";

    $con->query($req);
} else {
    $image = file_get_contents($_FILES["img_file"]['tmp_name']);

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


    $link = $con->real_escape_string($response->{"data"}->{"link"});

    $req = "INSERT INTO stories (user_id, content) VALUES ({$_SESSION['id']}, '$link')";

    $con->query($req);
}

header("Location: /home");
