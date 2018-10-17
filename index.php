<!DOCTYPE html>
<?php
    session_start();
    if(!isset($_SESSION['login'])) {
        header('LOCATION:./tmpLogin.php'); die();
    }?>

<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
HELLO WORLD
    </body>
</html>
