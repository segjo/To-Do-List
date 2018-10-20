<?php

session_start();

require_once('../../credentials.php');
//require_once('models/Items.php');
require_once('../models/Profile.php');
//---------------KONSTANTEN-----------------
define("URI_1", 3);  //profile, list, ..
define("URI_2", 4);  //create, Login
define("URI_REQ", $_SERVER[REQUEST_METHOD]);

ini_set('display_errors', 'on');
date_default_timezone_set("Europe/Zurich");



//$itemModel= new Item();
//$profileModel= new Profile();


$url = $_SERVER['REQUEST_URI'];

$values = parse_url($url);
$returnValue = array(
    'profile create' => array(
        'call' => 'POST',
        'path' => '/api/profile/create/',
        'param' => 'String userName, String firstName, String lastName, String email, String password',
    ),
    'profile login' => array(
        'call' => 'POST',
        'path' => '/api/profile/login/',
        'param' => 'String userName, String password',
    )
);
if (isLoggedIn()) {
    $returnValue = array(
        'profile delete' => array(
            'call' => 'DELETE',
            'path' => '/api/profile/{userId}',
            'param' => '',
        ),
    );
}

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
            if (isset($_POST["userName"]) && isset($_POST["firstName"]) && isset($_POST["lastName"]) && isset($_POST["email"]) && isset($_POST["password"])) {
                $returnValue = $profileModel->create($_POST["userName"], $_POST["firstName"], $_POST["lastName"], $_POST["email"], $_POST["password"]);
            } else {
                $returnValue = array('Response' => 400);
            }
        }
        if ($urlPaths[URI_2] == 'login' && URI_REQ == 'POST') {
            if (isset($_POST["userName"]) && isset($_POST["password"])) {
                $returnValue = $profileModel->login($_POST["userName"], $_POST["password"]);
            } else {
                $returnValue = array('Response' => 400);
            }
        }
        if (URI_REQ == 'DELETE') {
            if (isLoggedIn()) {
                if (isset($urlPaths[URI_2])) {
                    $userId = $urlPaths[URI_2];
                    $returnValue = $profileModel->delete($userId);
                } else {
                    $returnValue = array('Response' => 400);
                }
            } else {
                $returnValue = array('Response' => 401);
            }
        }


        break;
    case 'list':
        $this->add_user();
        break;
    case 'item':
        $this->update_user();
        break;
}

function isLoggedIn() {
    if (!isset($_SESSION['login'])) {
        return false;
    } else {
        return true;
    }
}

header('Content-Type: application/json');
echo json_encode($returnValue);
//header("HTTP/1.0 201 Resource created");



$dbh = null;



