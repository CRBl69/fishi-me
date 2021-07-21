<?php

session_start();

if(isset($_SESSION['id'])){
    echo json_encode([$_SESSION['id'], $_SESSION['username']]);
}else{
    echo json_encode([0, '']);
}