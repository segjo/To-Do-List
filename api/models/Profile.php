<?php

class Profile {

    private $db;

    function __construct(PDO $dbh) {
        $this->db = $dbh;
    }

    public function create() {//TODO
        $email = "dw";

        $sql = "SELECT * FROM User WHERE Email = '" . $email . "'";

        $select = $this->db->prepare($sql);
        $select->execute();
        $count = $select->rowCount();

        if ($count > 0) {
            //Email existiert bereits
            return false;
        }




        $statement = $this->db->prepare("INSERT INTO User (UserId, Name, LastName, Email, EmailActivated, UserName, EncryptedPassword, Salt, CreatedAt, UpdatedAt) VALUES (?, ?, ?, ?, ?,?, ?, ?, ?, ?)");
        $insert = $statement->execute(array(NULL, 'adawd', 'awdaw', 'adwd2w@adwaw', '0', 'awdawd', 'fefe', 'efef', '2018-10-17', NULL));
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
        foreach ($this->db->query('SELECT * from User') as $row) {
            var_dump($row);
        }
    }

}
