<?php

    session_start();
    if(!isset($_SESSION['login'])) {
        header('LOCATION:./tmpLogin.php'); die();
    }
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


// Use in the “Post-Receive URLs” section of your GitHub repo.

echo ('<a  href="tmpGitPull.php">Git pull</a>:<br />'."\n");
echo shell_exec( "cd /var/www/html/To-Do-List && git reset HEAD --hard && git pull" );


?>