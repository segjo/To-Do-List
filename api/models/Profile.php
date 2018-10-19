<?php

class Profile {

    private $db;

    function __construct(PDO $dbh) {
        $this->db = $dbh;
    }

    public function create() {//TODO
        if(isset($_POST["userName"])&&isset($_POST["firstName"])&&isset($_POST["lastName"])&&isset($_POST["email"])&&isset($_POST["password"])){
            $userName=$_POST["userName"];
            $firstName=$_POST["firstName"];
            $lastName=$_POST["lastName"];
            $email=$_POST["email"];
            $password=$_POST["password"];   
        }          


        $sql = "SELECT * FROM User WHERE Email = '" . $email . "' OR UserName = '" . $userName . "'";

        $select = $this->db->prepare($sql);
        $select->execute();
        $count = $select->rowCount();

        if ($count > 0) {
            echo "Benutzername / E-Mail already exist";
            return false;
        }


        $salt = uniqid(mt_rand());

        $statement = $this->db->prepare("INSERT INTO User (UserId, Name, LastName, Email, EmailActivated, UserName, Image, EncryptedPassword, Salt, CreatedAt, UpdatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert = $statement->execute(array(NULL, $firstName, $lastName, $email, '0', $userName, NULL, hash_hmac("sha256", $password, $salt), $salt, '2018-10-17', NULL));
        if ($insert) {
            echo 'created Profile ';
            return true;
        } else {
            return false;
        }

        echo 'created Profile ' . $value;
    }

    public function delete() {//TODO
        echo 'delete Profile';
    }

    public function login() {//TODO
        if (isset($_POST["userName"]) && isset($_POST["password"])) {
            $username = htmlspecialchars($_POST["userName"]);
            $password = htmlspecialchars($_POST["password"]);


            $sql = "SELECT `UserName`,`Salt`,`EncryptedPassword` FROM `User` WHERE `UserName` = '" . $username . "' LIMIT 1";
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
                    echo "Login erfolgreich";
                    return true;
                } else {
                    echo "Passwort falsch";
                }
            }
        } else {
            echo "Login Benutername und Passwort eingeben";
            return false;
        }

        //if (hash_hmac("sha256", $_POST['password'], $saltFromDatabase) === $hashFromDatabase)
        //$login = true;
    }

}
