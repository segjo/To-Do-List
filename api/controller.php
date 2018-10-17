<?php
    session_start();
    if(!isset($_SESSION['login'])) {
        header('LOCATION:../tmpLogin.php'); die();
    }
echo "controller";



/*
        switch($_SERVER['REQUEST_METHOD']){
            case 'GET':
                $this->get_user();
             break;
            case 'POST':
                 $this->add_user();
             break;
            case 'PUT':
                 $this->update_user();
             break;
            case 'DELETE':
                 $this->delete_user();
             break;
        }*/
           
    


