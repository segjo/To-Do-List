<?php

require_once('../utils/FormValidator.php');

class ListItem {

    private $db;
    private $validator;

    function __construct(PDO $dbh) {
        $this->db = $dbh;
    }

    public function add($listId, $itemName) {
        if (!FormValidator::validateItem($listId, 'number')) {
            return array('Response' => 422);
        }
        if (!FormValidator::validateItem($itemName, 'itemname')) {
            return array('Response' => 422);
        }

        $date = date('Y-m-d H:i:s');

        if ($this->checkListPermission($listId)) {
            $userId = $this->getOwnUserId();
            //INSERT INTO Item (ItemId, ListId, Name, Deadline, SortIndex, CreatedAt, UpdatedAt, DeletedAt) VALUES (NULL, '17', 'awd', NULL, '0', '2018-10-24 00:00:00', NULL, NULL);
            $sqlInsertItem = "INSERT INTO Item (ItemId, ListId, Name, Deadline, SortIndex, CreatedAt, UpdatedAt, DeletedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $dbh = $this->db;

            $stmt = $dbh->prepare($sqlInsertItem);
            $insertItem = $stmt->execute(array(NULL, $listId, $itemName, NULL, 0, $date, NULL, NULL));
            if ($insertItem) {
                return array('Response' => 201, "success" => "true");
            } else {
                return array('Response' => 422);
            }
        } else {
            return array('Response' => 404);
        }
    }

    public function edit($listId, $itemId, $itemName, $deadline, $sortIndex, $state) {
        if (!FormValidator::validateItem($listId, 'number')) {
            return array('Response' => 422, 'ValdidateError' => 'listId');
        }
        if (!FormValidator::validateItem($itemId, 'number')) {
            return array('Response' => 422, 'ValdidateError' => 'itemId');
        }
        if (!FormValidator::validateItem($itemName, 'itemname')) {
            return array('Response' => 422, 'ValdidateError' => 'itemName');
        }
        if ($deadline != "") {
            if (!(FormValidator::validateItem($deadline, 'datetime')||FormValidator::validateItem($deadline, 'date'))) {
                return array('Response' => 422, 'ValdidateError' => 'deadline');
            }
            $deadline = "'" . $deadline . "'";
        } else {
            $deadline = "NULL";
        }
        if ($sortIndex != "") {
            if (!FormValidator::validateItem($sortIndex, 'number')) {
                return array('Response' => 422, 'ValdidateError' => 'sortIndex');
            }
        } else {
            $sortIndex = 0;
        }
        if ($state != "") {
            if (!FormValidator::validateItem($state, 'number')) {
                return array('Response' => 422, 'ValdidateError' => 'state');
            }
        } else {
            $state = 0;
        }

        if($state>0){
            $state=1; //only state 0 (uncheck) or 1 (check)
        }


        $date = date('Y-m-d H:i:s');

        if ($this->checkListPermission($listId)) {

            $sql = "SELECT ItemId FROM Item WHERE DeletedAt is NULL AND ItemId = " . $itemId;
            $sth = $this->db->prepare($sql);
            $sth->execute();
            $lists = $sth->fetchAll();
            if (count($lists) > 0) {
                $sqlInsertItem = "UPDATE Item SET Name = '" . $itemName . "', Deadline = " . $deadline . ", State = " . $state . ", SortIndex = " . $sortIndex . ", UpdatedAt = '" . $date . "' WHERE ItemId = " . $itemId;
                $dbh = $this->db;
                $stmt = $dbh->prepare($sqlInsertItem);
                $insertItem = $stmt->execute();
                return array('Response' => 200, "success" => "true");
            } else {
                return array('Response' => 404);
            }
        } else {
            return array('Response' => 404);
        }
    }

    public function delete($listId, $itemId) {
        if (!FormValidator::validateItem($listId, 'number')) {
            return array('Response' => 422, 'ValdidateError' => 'listId');
        }


        $date = date('Y-m-d H:i:s');

        if ($this->checkListPermission($listId)) {

            $sql = "SELECT ItemId FROM Item WHERE DeletedAt is NULL AND ItemId = " . $itemId;
            $sth = $this->db->prepare($sql);
            $sth->execute();
            $lists = $sth->fetchAll();
            if (count($lists) > 0) {
                $sqlInsertItem = "UPDATE Item SET DeletedAt = '" . $date . "' WHERE ItemId = " . $itemId;
                $dbh = $this->db;
                $stmt = $dbh->prepare($sqlInsertItem);
                $insertItem = $stmt->execute();
                return array('Response' => 200, "success" => "true");
            } else {
                return array('Response' => 404);
            }
        } else {
            return array('Response' => 404);
        }
    }

    private function checkListPermission($listId) {


        $userId = $this->getOwnUserId();
        $sql = "SELECT List.ListId FROM List, User2List, User WHERE List.ListId=User2List.ListId AND User2List.UserId = User.UserId AND List.DeletedAt is NULL AND User2List.DeletedAt is NULL AND (User2List.Owner=1 OR User2List.ShareActivated=1) AND User.UserId = " . $userId . " AND List.ListId = " . $listId;
        $sth = $this->db->prepare($sql);
        $sth->execute();
        $lists = $sth->fetchAll();
        if (count($lists) > 0) {
            return true;
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
