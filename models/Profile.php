<?php

require_once('../utils/FormValidator.php');
require_once('../../credentials.php');

class Profile {

    private $db;
    private $validator;

    function __construct(PDO $dbh) {
        $this->db = $dbh;
    }
    
    
    public function getOwnProfile(){
        $userId=$this->getOwnUserId();
        $sql = "SELECT UserId, UserName, Name, LastName, Email, Image FROM `User` WHERE `UserId` = " . $userId;
        $sth = $this->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        if (count($result) > 0) {
            return array('Response' => 200, 
                'Content' => array(
                    'userId' => $result[0]['UserId'],
                    'userName' => $result[0]['UserName'],
                    'userFirstName' => $result[0]['Name'],
                    'userLastName' => $result[0]['LastName'],
                    'userEmail' => $result[0]['Email'],
                    'userAvatar' => "uploads/".$result[0]['Image'],));
        }
        
        
    }
    

    public function create($userName, $firstName, $lastName, $email, $password, $mailer) {


        if (!FormValidator::validateItem($userName, 'username')) {
            return array('Response' => 422, 'ValdidateError' => 'username');
        }
        if (!FormValidator::validateItem($firstName, 'name')) {
            return array('Response' => 422, 'ValdidateError' => 'firstname');
        }
        if (!FormValidator::validateItem($lastName, 'name')) {
            return array('Response' => 422, 'ValdidateError' => 'lastname');
        }
        if (!FormValidator::validateItem($email, 'email')) {
            return array('Response' => 422, 'ValdidateError' => 'email');
        }
        if (!FormValidator::validateItem($password, 'password')) {
            return array('Response' => 422, 'ValdidateError' => 'password');
        }




        $sql = "SELECT * FROM User WHERE (Email = '" . $email . "' OR UserName = '" . $userName . "') AND DeletedAt IS NULL";

        $select = $this->db->prepare($sql);
        $select->execute();
        $count = $select->rowCount();

        if ($count > 0) {
            return array('Response' => 409);
        }

        $date = date('Y-m-d H:i:s');
        $salt = uniqid(mt_rand());
        $actCode = uniqid(mt_rand());
        $sql = "INSERT INTO User (UserId, Name, LastName, Email, ActivateCode, EmailActivated, UserName, Image, EncryptedPassword, Salt, CreatedAt, UpdatedAt, DeletedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $statement = $this->db->prepare($sql);
        $insert = $statement->execute(array(NULL, $firstName, $lastName, $email, $actCode, '0', $userName, NULL, hash_hmac("sha256", $password, $salt), $salt, $date, NULL, NULL));
        if ($insert) {
            $mailer->sendActivationMail($email, $userName, $actCode);
            return array('Response' => 201, 'Content' => array('userId' => $this->getUserId($userName)));
        } else {
            return array('Response' => 422);
        }
    }

    public function activate($code) {
        if (!FormValidator::validateItem($code, 'activatecode')) {
            return array('Response' => 422, 'ValdidateError' => 'activatecode');
        }

        $sql = "SELECT UserId FROM User WHERE ActivateCode = '" . $code . "'";
        $sth = $this->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        if (count($result) > 0) {
            $date = date('Y-m-d H:i:s');
            $sql = "UPDATE User SET UpdatedAt = '" . $date . "', EmailActivated = 1 WHERE User.UserId = " . $result[0]['UserId'] . " AND DeletedAt IS NULL";
            $statement = $this->db->prepare($sql);
            $activate = $statement->execute();
            if ($activate) {
                session_destroy();
                return array('Response' => 200, 'Content' => array('activation' => 'successful'));
            } else {
                return array('Response' => 422, 'Content' => array('activation' => 'not successful'));
            }
        } else {
            return array('Response' => 422, 'Content' => array('activation' => 'not successful'));
        }
    }

    public function login($userName, $password) {
        if (!FormValidator::validateItem($userName, 'username')) {
            return array('Response' => 401);
        }
        if (!FormValidator::validateItem($password, 'password')) {
            return array('Response' => 401);
        }

        $sql = "SELECT `EmailActivated`,`UserName`,`Salt`,`EncryptedPassword`,`Image`  FROM `User` WHERE `UserName` = '" . $userName . "' AND DeletedAt IS NULL LIMIT 1";
        //return $sql;
        $sth = $this->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        if (count($result) > 0) {
            foreach ($result as $row) {
                $dbSalt = $row['Salt'];
                $dbHashedPassword = $row['EncryptedPassword'];
                $dbEmailActiavated = $row['EmailActivated'];
                if($row['Image']!=null){
                    $avatar="uploads/".$row['Image'];
                }else{
                    $avatar=null;
                }
            }
            if (hash_hmac("sha256", $password, $dbSalt) == $dbHashedPassword) {

                if ($dbEmailActiavated == 1) {
                    $_SESSION['login'] = true;
                    $_SESSION['userId'] = $this->getUserId($userName);
                    return array('Response' => 200, 'Content' => array('userId' => $this->getUserId($userName), 'userName' => $userName, 'userLocation' => $this->getUserLocation($this->getUserIP(), IPSTACK_ACCESSKEY), 'userAvatar' => $avatar, 'userSessionId' => session_id()));
                } else {
                    return array('Response' => 424, 'Content' => array('userId' => $this->getUserId($userName)));
                }
            } else {
                return array('Response' => 401);
            }
        } else {
            return array('Response' => 401);
        }
    }
    
    public function logout(){
        session_destroy();
        return array('Response' => 200, 'Content' => array('success' => true));
    }

    public function delete($password) {
        if (!FormValidator::validateItem($password, 'password')) {
            return array('Response' => 401);
        }
        $userId = $this->getOwnUserId();
        $userName = $this->getUserName($userId);
        $sql = "SELECT `EmailActivated`,`UserName`,`Salt`,`EncryptedPassword` FROM `User` WHERE `UserName` = '" . $userName . "' AND DeletedAt IS NULL LIMIT 1";
        //return $sql;
        $sth = $this->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        if (count($result) > 0) {
            foreach ($result as $row) {
                $dbSalt = $row['Salt'];
                $dbHashedPassword = $row['EncryptedPassword'];
                $dbEmailActiavated = $row['EmailActivated'];
            }
            if (hash_hmac("sha256", $password, $dbSalt) == $dbHashedPassword) {
                $date = date('Y-m-d H:i:s');
                $sql = "UPDATE User SET DeletedAt = '" . $date . "' WHERE User.UserId = " . $userId . "";
                $statement = $this->db->prepare($sql);
                $delete = $statement->execute();
                if ($delete) {
                    session_destroy();
                    return array('Response' => 200, 'Content' => array('success' => true));
                } else {
                    return array('Response' => 404, 'Content' => array('success' => false));
                }
            } else {
                return array('Response' => 401);
            }
        } else {
            return array('Response' => 401);
        }
    }

    public function getLists() {

        $userId = $this->getOwnUserId();
        $sql = "SELECT List.ListId, List.Name, List.SortIndex ,List.Priority FROM List, User2List, User WHERE List.ListId=User2List.ListId AND User2List.UserId = User.UserId AND List.DeletedAt is NULL AND User2List.DeletedAt is NULL AND User2List.Owner = 1 AND User.UserId = " . $userId . " ORDER BY `List`.`SortIndex` DESC, `List`.`ListId` DESC";
        $sth = $this->db->prepare($sql);
        $sth->execute();
        $lists = $sth->fetchAll(PDO::FETCH_OBJ);
        //echo var_dump($lists);
        return array('Response' => 200, 'lists' => $lists);
    }

    public function getSharedLists() {

        $userId = $this->getOwnUserId();
        $sql = "SELECT List.ListId, List.Name, List.SortIndex ,List.Priority FROM List, User2List, User WHERE List.ListId=User2List.ListId AND User2List.UserId = User.UserId AND List.DeletedAt is NULL AND User2List.DeletedAt is NULL AND User2List.ShareActivated = 1 AND User.UserId = " . $userId . " ORDER BY `List`.`ListId` DESC";
        $sth = $this->db->prepare($sql);
        $sth->execute();
        $lists = $sth->fetchAll(FETCH_OBJ);
        if ($lists == false) {
            $lists = null;
        }
        return array('Response' => 200, 'lists' => $lists);
    }

    public function uploadAvatar(array $file) {
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($file["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        // Check if image file is a actual image or fake image

        if ($file["tmp_name"] == "") {
            return array('Response' => 422, 'Content' => array('error' => 'no picture'));
        } else {

            echo $file["tmp_name"];

            $check = getimagesize($file["tmp_name"]);
            if ($check !== false) {
                echo "File is an image - " . $check["mime"] . ".";
            } else {
                return array('Response' => 422, 'Content' => array('error' => 'File is not an image'));
            }

// Check file size
            if ($file["size"] > 2000000) {
                return array('Response' => 422, 'Content' => array('error' => 'File is too large (>20mb)'));
            }
// Allow certain file formats
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                return array('Response' => 422, 'Content' => array('error' => 'only JPG, JPEG, PNG & GIF files are allowed'));
            }
// Check if $uploadOk is set to 0 by an error

            $oldAvatar= $this->getOwnProfile()['Content']['userAvatar'];
            $newfilename = uniqid(mt_rand()).".".$imageFileType;
            if (move_uploaded_file($file["tmp_name"], $target_dir . $newfilename)) {
                echo "The file " . basename($file["name"]) . " has been uploaded.";
                $date = date('Y-m-d H:i:s');
                $sql = "UPDATE User SET UpdatedAt = '" . $date . "', Image = '" . $newfilename . "' WHERE User.UserId = " . $this->getOwnUserId() . "";
                $statement = $this->db->prepare($sql);
                $result = $statement->execute();
                if ($result) {
                    if($oldAvatar!=null){
                        unlink("../".$oldAvatar);
                    }
                    return array('Response' => 200, 'Content' => array('upload' => 'successful'));
                } else {
                    return array('Response' => 422, 'Content' => array('upload' => 'not successful'));
                }
            } else {
                return array('Response' => 422, 'Content' => array('upload' => 'not successful'));
            }
        }
    }

    private function getUserId($userName) {
        $sql = "SELECT UserId FROM User WHERE UserName = '" . $userName . "' AND DeletedAt IS NULL";

        $sth = $this->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        if (count($result) > 0) {
            foreach ($result as $row) {
                return $row['UserId'];
            }
        } else {
            return false;
        }
    }

    private function getOwnUserId() {
        if (isset($_SESSION['userId'])) {
            return $_SESSION['userId'];
        } else {
            return false;
        }
    }

    private function getUserName($userId) {
        $sql = "SELECT UserName FROM User WHERE UserId = '" . $userId . "' AND DeletedAt IS NULL";

        $sth = $this->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        if (count($result) > 0) {
            foreach ($result as $row) {
                return $row['UserName'];
            }
        } else {
            return false;
        }
    }

    private function getUserIP() {
        if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') > 0) {
                $addr = explode(",", $_SERVER['HTTP_X_FORWARDED_FOR']);
                return trim($addr[0]);
            } else {
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    private function getUserLocation($ip, $access_key) {
        $request = 'http://api.ipstack.com/' . $ip . '?access_key=' . $access_key;

        $ch = curl_init($request);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);
        $api_result = json_decode($json, true);
        return $api_result['location']['capital'];
        ;
    }

}
