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

        $date = date('Y-m-d H:i:s');
        $salt = uniqid(mt_rand());
        $sql = "INSERT INTO User (UserId, Name, LastName, Email, EmailActivated, UserName, Image, EncryptedPassword, Salt, CreatedAt, UpdatedAt, DeletedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $statement = $this->db->prepare($sql);
        $insert = $statement->execute(array(NULL, $firstName, $lastName, $email, '0', $userName, NULL, hash_hmac("sha256", $password, $salt), $salt, $date, NULL, NULL));
        if ($insert) {
            return array('Response' => 201, 'Content' => array('userId' => $this->getUserId($userName)));
        } else {
            return array('Response' => 422);
        }
    }

    public function login($userName, $password) {//TODO
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

                if ($dbEmailActiavated) {
                    $_SESSION['login'] = true;
                    $_SESSION['userId'] = $this->getUserId($userName);
                    return array('Response' => 200, 'Content' => array('userId' => $this->getUserId($userName)));
                } else {
                    return array('Response' => 401, 'Content' => array('userId' => $this->getUserId($userName)));
                }
            } else {
                return array('Response' => 401, 'Content' => array('userId' => $this->getUserId($userName)));
            }
        } else {
            return array('Response' => 401, 'Content' => array('userId' => $this->getUserId($userName)));
        }

    }

    public function delete($userId) {
        if ($this->getOwnUserId() == $userId) {
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
            return array('Response' => 401, 'Content' => array('success' => false));
        }
    }

    private function getUserId($userName) {
        $sql = "SELECT UserId FROM User WHERE UserName = '" . $userName . "'";

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

}
