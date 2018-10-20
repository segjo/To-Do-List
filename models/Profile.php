<?php

class Profile {

    private $db;
    
    
    

    function __construct(PDO $dbh) {
        $this->db = $dbh;
    }

    public function create($userName, $firstName, $lastName, $email, $password) {
        $sql = "SELECT * FROM User WHERE Email = '" . $email . "' OR UserName = '" . $userName . "'";

        $select = $this->db->prepare($sql);
        $select->execute();
        $count = $select->rowCount();

        if ($count > 0) {
             return array('Response' => 409);
        }


        $salt = uniqid(mt_rand());

        $statement = $this->db->prepare("INSERT INTO User (UserId, Name, LastName, Email, EmailActivated, UserName, Image, EncryptedPassword, Salt, CreatedAt, UpdatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert = $statement->execute(array(NULL, $firstName, $lastName, $email, '0', $userName, NULL, hash_hmac("sha256", $password, $salt), $salt, '2018-10-17', NULL));
        if ($insert) {
            return array('Response' => 201, 'Content' => array('userId' => $this->getUserId($userName)));
        } else {
            return array('Response' => 422);
        }
    }

    public function delete() {//TODO
        echo 'delete Profile';
    }

    public function login($userName, $password) {//TODO
        $sql = "SELECT `UserName`,`Salt`,`EncryptedPassword` FROM `User` WHERE `UserName` = '" . $userName . "' LIMIT 1";
        $sth = $this->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        if (count($result) > 0) {
            foreach ($result as $row) {
                $dbSalt = $row['Salt'];
                $dbHashedPassword = $row['EncryptedPassword'];
            }
            if (hash_hmac("sha256", $password, $dbSalt) == $dbHashedPassword) {
                $_SESSION['Login'] = true;
            return array('Response' => 200, 'Content' => array('userId' => $this->getUserId($userName)));

            } else {
            return array('Response' => 401, 'Content' => array('userId' => $this->getUserId($userName)));
            }
        }

        //if (hash_hmac("sha256", $_POST['password'], $saltFromDatabase) === $hashFromDatabase)
        //$login = true;
    }

    private function getUserId($userName){
        $sql = "SELECT UserId FROM User WHERE UserName = '" . $userName . "'";

        $sth = $this->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        if (count($result) > 0) {
        foreach ($result as $row) {
            return $row['UserId'];
            
        }}else{
            return false;
        }
    }
}
