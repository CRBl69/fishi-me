<?php

if(isset($_SESSION['notification']) && !empty($_SESSION['notification'])){
    echo "
    <script>
        alert(`{$_SESSION['notification']}`);
    </script>
    ";
    unset($_SESSION['notification']);
}