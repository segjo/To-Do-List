<!DOCTYPE html>
<?php
    session_start();
    if(!isset($_SESSION['login'])) {
        header('LOCATION:./login.html'); die();
    }else{
        header('LOCATION:./main.html');
    }?>
