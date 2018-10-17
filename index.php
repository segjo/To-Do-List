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

<a  href="tmpGitPull.php">Git pull</a>
    </body>
</html>
