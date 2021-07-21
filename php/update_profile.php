<?php

session_start();
require_once 'db-config.php';

if(!isset($_SESSION['id'])){
    header('Location: /login');
    die();
}

header('Location: '. $_SESSION['last_page']);

if(isset($_POST['description']) && !empty($_POST['description'])){
    $con = connect();

    $description = $con->real_escape_string($_POST['description']);

    $req = "UPDATE users SET description = '$description' WHERE id = {$_SESSION['id']}";

    if(!$con->query($req)){
        $_SESSION['notification'] = 'Could not update description';
    }
}

if($_FILES["profile_picture"]['size'] > 0){
    $target_dir = "/var/www/html/data/";

    $target_file = $target_dir . $_SESSION['id'].'.jpg';

    $uploadOk = 1;

    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($_FILES['profile_picture']['tmp_name']);

    if($check['mime'] == 'image/png') png2jpg($_FILES['profile_picture']['tmp_name'], $_FILES['profile_picture']['tmp_name']);

    resize_image($_FILES["profile_picture"]["tmp_name"]);

    if($check && ($check['mime'] == 'image/png' || $check['mime'] == 'image/jpeg')){
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $_SESSION['notification'] .= "\nImage uploaded";
        } else {
            $_SESSION['notification'] .= "\nCould not upload image";
        }
    }else{
        header('Location: '.$_SESSION['last_page']);
        $_SESSION['notification'] .= "\nFile is not an image or supported image type (png | jpeg)";
    }
}

// Resises the image to 256x256 and centers it
function resize_image($file) {
    list($width, $height) = getimagesize($file);
    $x_offset = 0;
    $y_offset = 0;
    if($width > $height){
        $x_offset = ($width - $height) / 2;
        $width = $height;
    }else{
        $y_offset = ($height - $width) / 2;
        $height = $width;
    }
    $src = imagecreatefromjpeg($file);
    $dst = imagecreatetruecolor(256, 256);
    imagecopyresampled($dst, $src, 0, 0, $x_offset, $y_offset, 256, 256, $width, $height);
    imagejpeg($dst, $file, 50);
}

// Converts a png image to a jpg one
function png2jpg($originalFile, $outputFile) {
    $image = imagecreatefrompng($originalFile);
    imagejpeg($image, $outputFile, 50);
    imagedestroy($image);
}