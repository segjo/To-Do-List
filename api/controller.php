<?php

session_start();
if (!isset($_SESSION['login'])) {
    header('LOCATION:../tmpLogin.php');
    die();
}
require_once('../../credentials.php');
//require_once('models/Items.php');
require_once('models/Profile.php');
//---------------KONSTANTEN-----------------
define("URI_1", 3);
define("URI_2", 4);
define("URI_REQ", $_SERVER[REQUEST_METHOD]);

ini_set('display_errors', 'on');



//$itemModel= new Item();
//$profileModel= new Profile();


$url = $_SERVER[REQUEST_URI];
$values = parse_url($url);
$urlPaths = $host = explode('/', $values['path']);

try {

    $dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USERNAME, DB_PASSWORD);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

switch ($urlPaths[URI_1]) {
    case 'profile':

        $profileModel = new Profile($dbh);
        if ($urlPaths[URI_2] == 'create' && URI_REQ == 'POST') {
            $returnValue = $profileModel->create();
        }
        if ($urlPaths[URI_2] == 'login' && URI_REQ == 'POST') {
            $returnValue = $profileModel->login();
        }
        if (URI_REQ == 'DELETE') {
            $returnValue = $profileModel->delete();
        }
        echo $profileModel->create();


        break;
    case 'list':
        $this->add_user();
        break;
    case 'item':
        $this->update_user();
        break;
}

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
  } */


$dbh = null;



