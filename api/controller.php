<?php

session_start();

require_once('../../credentials.php');
require_once('../models/Profile.php');
require_once('../models/ToDoList.php');
require_once('../models/ListItem.php');
require_once('../utils/Mailer.php');
require_once './description.php';
//---------------CONSTANTS-----------------
define("URI_1", 3);  //profile, list, ..
define("URI_2", 4);  //create, Login
define("URI_3", 5);  //items
define("URI_4", 6);  //items functions
define("URI_REQ", $_SERVER['REQUEST_METHOD']);

ini_set('display_errors', 'on');
date_default_timezone_set("Europe/Zurich");

$mailer = new Mailer();
$returnValue['Response'] = getDescriptions();



$url = $_SERVER['REQUEST_URI'];

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
            if (isset($_POST["userName"]) && isset($_POST["firstName"]) && isset($_POST["lastName"]) && isset($_POST["email"]) && isset($_POST["password"])) {
                $returnValue = $profileModel->create($_POST["userName"], $_POST["firstName"], $_POST["lastName"], $_POST["email"], $_POST["password"], $mailer);
            } else {
                $returnValue = array('Response' => 400, 'Method' => "Profile create");
            }
        }
        if ($urlPaths[URI_2] == 'login' && URI_REQ == 'POST') {
            if (isset($_POST["userName"]) && isset($_POST["password"])) {
                $returnValue = $profileModel->login($_POST["userName"], $_POST["password"]);
            } else {
                $returnValue = array('Response' => 400, 'Method' => "Profile Login");
            }
        }

        if ($urlPaths[URI_2] == 'logout' && URI_REQ == 'POST') {
            if (isLoggedIn()) {
                $returnValue = $profileModel->logout();
            } else {
                $returnValue = array('Response' => 401);
            }
        }

        if ($urlPaths[URI_2] == 'uploadAvatar' && URI_REQ == 'POST') {
            if (isLoggedIn()) {
                if (file_exists($_FILES['image']['tmp_name'])) {
                    $returnValue = $profileModel->uploadAvatar($_FILES['image']);
                } else {
                    $returnValue = array('Response' => 400, 'Method' => "Profile upload avatar");
                }
            } else {
                $returnValue = array('Response' => 401);
            }
        }
        if ($urlPaths[URI_2] == 'info' && URI_REQ == 'GET') {
            if (isLoggedIn()) {
                $returnValue = $profileModel->getOwnProfile();
            } else {
                $returnValue = array('Response' => 401);
            }
        }
        if ($urlPaths[URI_2] == 'activate' && URI_REQ == 'GET') {
            if (isset($_GET["activateCode"])) {
                $returnValue = $profileModel->activate($_GET["activateCode"]);
            } else {
                $returnValue = array('Response' => 400, 'Method' => "Profile activate");
            }
        }
        if ($urlPaths[URI_2] == 'delete' && URI_REQ == 'POST') {
            if (isLoggedIn()) {
                if (isset($_POST["password"])) {
                    $password = $_POST["password"];
                    $returnValue = $profileModel->delete($password);
                } else {
                    $returnValue = array('Response' => 400, 'Method' => "Profile delete");
                }
            } else {
                $returnValue = array('Response' => 401);
            }
        }
        if ($urlPaths[URI_2] == 'lists' && URI_REQ == 'GET') {
            if (isLoggedIn()) {

                $returnValue = $profileModel->getLists();
            } else {
                $returnValue = array('Response' => 401);
            }
        }
        if ($urlPaths[URI_2] == 'sharedlists' && URI_REQ == 'GET') {
            if (isLoggedIn()) {

                $returnValue = $profileModel->getSharedLists();
            } else {
                $returnValue = array('Response' => 401);
            }
        }


        break;
    case 'todolist':
        $listModel = new ToDoList($dbh);


        if (isLoggedIn()) {

            if ($urlPaths[URI_2] == 'create' && URI_REQ == 'POST') {
                if (isset($_POST["listName"])) {
                    $returnValue = $listModel->create($_POST["listName"]);
                } else {
                    $returnValue = array('Response' => 400, 'Method' => "Todolist create");
                }
            }

            if (isset($urlPaths[URI_3])) {
                if ($urlPaths[URI_3] == 'delete' && URI_REQ == 'POST') {
                    if (isset($urlPaths[URI_2])) {
                        $listId = $urlPaths[URI_2];
                        $returnValue = $listModel->delete($listId);
                    } else {
                        $returnValue = array('Response' => 400, 'Method' => "Todolist delete");
                    }
                }

                if ($urlPaths[URI_3] == 'edit' && URI_REQ == 'POST') {
                    if (isset($urlPaths[URI_2]) && isset($_POST["listName"]) && isset($_POST["priority"]) && isset($_POST["sortIndex"])) {
                        $listId = $urlPaths[URI_2];
                        $returnValue = $listModel->edit($listId, $_POST["listName"], $_POST["priority"], $_POST["sortIndex"]);
                    } else {
                        $returnValue = array('Response' => 400, 'Method' => "Todolist edit");
                    }
                }

                if ($urlPaths[URI_3] == 'items' && !isset($urlPaths[URI_4]) && URI_REQ == 'GET') {
                    if (isset($urlPaths[URI_2])) {
                        $listId = $urlPaths[URI_2];
                        if (isset($_GET["lastCall"])) {
                            $lastCall = $_GET["lastCall"];
                        } else {
                            $lastCall = null;
                        }
                        $returnValue = $listModel->getListItems($listId, $lastCall);
                    } else {
                        $returnValue = array('Response' => 400, 'Method' => "Todolist get Items");
                    }
                }

                if ($urlPaths[URI_3] == 'share' && URI_REQ == 'POST') {
                    if (isset($urlPaths[URI_2]) && isset($_POST["userName"])) {
                        $listId = $urlPaths[URI_2];
                        $userName = $_POST["userName"];
                        $returnValue = $listModel->share($listId, $userName, $mailer);
                    } else {
                        $returnValue = array('Response' => 400, 'Method' => "Todolist share");
                    }
                }
            }


            //----------------ITEMS---------------------
            if (isset($urlPaths[URI_3])) {
                if ($urlPaths[URI_3] == 'items') {
                    $listItemModel = new ListItem($dbh);
                    if (isset($urlPaths[URI_4])) {
                        if ($urlPaths[URI_4] == 'add' && URI_REQ == 'POST') {
                            if (isset($urlPaths[URI_2]) && isset($_POST["itemName"])) {
                                $listId = $urlPaths[URI_2];
                                $itemName = $_POST["itemName"];
                                $returnValue = $listItemModel->add($listId, $itemName);
                            } else {
                                $returnValue = array('Response' => 400, 'Method' => "Item add");
                            }
                        }
                        if ($urlPaths[URI_4] != 'add' && $urlPaths[URI_4] != 'delete' && URI_REQ == 'POST') {
                            if (isset($_POST["itemName"]) && isset($_POST["deadline"]) && isset($_POST["sortIndex"]) && isset($_POST["state"])) {
                                $listId = $urlPaths[URI_2];
                                $itemId = $urlPaths[URI_4];
                                $itemName = $_POST["itemName"];
                                $deadline = $_POST["deadline"];
                                $sortIndex = $_POST["sortIndex"];
                                $state = $_POST["state"];
                                $returnValue = $listItemModel->edit($listId, $itemId, $itemName, $deadline, $sortIndex, $state);
                            } else {
                                $returnValue = array('Response' => 400, 'Method' => "Item edit");
                            }
                        }
                        if ($urlPaths[URI_4] == 'delete' && URI_REQ == 'POST') {
                            if (isset($_POST["itemId"])) {
                                $listId = $urlPaths[URI_2];
                                $itemId = $_POST["itemId"];
                                $returnValue = $listItemModel->delete($listId, $itemId);
                            } else {
                                $returnValue = array('Response' => 400, 'Method' => "Item delete");
                            }
                        }
                    }
                }
            }
        }

        if ($urlPaths[URI_2] == 'activate' && URI_REQ == 'GET') {
            if (isset($_GET["activateCode"])) {
                $shareCode = $_GET["activateCode"];
                $returnValue = $listModel->activate($shareCode);
            } else {
                $returnValue = array('Response' => 400, 'Method' => "Todolist activate");
            }
        } else {
            $returnValue = array('Response' => 401);
        }




        break;





    case 'item':
        //$this->update_user();
        break;
}

function isLoggedIn() {
    if (!isset($_SESSION['login'])) {
        return false;
    } else {
        return true;
    }
}

//echo var_dump($returnValue);


header('Content-Type: application/json');
echo json_encode($returnValue);

http_response_code($returnValue['Response']);



$dbh = null;



