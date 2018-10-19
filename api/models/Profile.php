<?php

class Profile {

    private $db;

    function __construct(PDO $dbh) {
        $this->db = $dbh;
    }

    public function create() {//TODO
        $email = "dw";
        $username = "test";

        $sql = "SELECT * FROM User WHERE Email = '" . $email . "' OR UserName = '" . $username . "'";

        $select = $this->db->prepare($sql);
        $select->execute();
        $count = $select->rowCount();

        if ($count > 0) {
            //Email existiert bereits
            return false;
        }


        $salt = uniqid(mt_rand());

        $statement = $this->db->prepare("INSERT INTO User (UserId, Name, LastName, Email, EmailActivated, UserName, EncryptedPassword, Salt, CreatedAt, UpdatedAt) VALUES (?, ?, ?, ?, ?,?, ?, ?, ?, ?)");
        $insert = $statement->execute(array(NULL, 'adawd', 'awdaw', 'adwd2w2@adwaw', '0', 'awdawd', hash_hmac("sha256", 'Passwort', $salt), $salt, '2018-10-17', NULL));
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


            $sql = "SELECT TOP 1 `UserName`,`Salt`,`EncryptedPassword` FROM `User` WHERE `UserName` = '" . $username . "' LIMIT 1";
            $select = $this->db->query($sql);

            if (count($select) > 0) {
                foreach ($pdo->query($select) as $row) {
                    $dbSalt = $row['Salt'];
                    $dbHashedPassword = $row['EncryptedPassword'];
                }
                if (hash_hmac("sha256", $password, $dbSalt) == $dbHashedPassword) {
                    $_SESSION["LOGIN"] = true;
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
