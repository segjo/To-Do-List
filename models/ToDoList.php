<?php

require_once('../utils/FormValidator.php');

class ToDoList {

    private $db;
    private $validator;

    function __construct(PDO $dbh) {
        $this->db = $dbh;
    }

    public function create($listName) {
        if (!FormValidator::validateItem($listName, 'listname')) {
            return array('Response' => 422);
        }

        $date = date('Y-m-d H:i:s');
        $userId = $this->getOwnUserId();

        $sqlInsertList = "INSERT List (ListId, Name, Priority, SortIndex, CreatedAt, UpdatedAt, DeletedAt) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $sqlInsertUser2List = "INSERT INTO User2List (UserListId, UserId, ListId, Owner, ShareCode, ShareActivated, DeletedAt) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $dbh = $this->db;

        try {
            $dbh->beginTransaction();
            $stmt = $dbh->prepare($sqlInsertList);
            $insertList = $stmt->execute(array(NULL, $listName, 0, 0, $date, NULL, NULL));
            $insertFail = true;
            if ($insertList) {
                $insertedListId = $dbh->lastInsertId();
                $stmt = $dbh->prepare($sqlInsertUser2List);
                $insertLink = $stmt->execute(array(NULL, $userId, $insertedListId, 1, NULL, 0, NULL));
                $dbh->commit();
                if ($insertLink) {
                    $insertFail = false;
                    return array('Response' => 201, "success" => "true");
                }
            }

            if ($insertFail) {
                $dbh->commit();
                return array('Response' => 422);
            }
        } catch (PDOExecption $e) {
            $dbh->rollback();
            return array('Response' => 422, 'Error' => $e->getMessage());
        }
    }

    public function share($listId, $userName, $mailer) {
        if (!FormValidator::validateItem($listId, 'number')) {
            return array('Response' => 422);
        }
        if (!FormValidator::validateItem($userName, 'username')) {
            return array('Response' => 422);
        }
        if ($this->checkListPermission($listId)) {
            $sql = "SELECT UserId, Email FROM User WHERE UserName = '" . $userName . "' AND DeletedAt IS NULL";
            $sth = $this->db->prepare($sql);
            $sth->execute();
            $result = $sth->fetchAll();
            if (count($result) > 0) {
                $userId = $result[0]['UserId'];
                $email = $result[0]['Email'];
                $shareCode = uniqid(mt_rand());
                $senderUserId = $this->getOwnUserId();

                $sql = "INSERT INTO `User2List`(`UserListId`, `UserId`, `ListId`, `Owner`, `ShareCode`, `ShareActivated`, `DeletedAt`) " .
                        "VALUES (NULL," . $userId . "," . $listId . ",0,'" . $shareCode . "',0,NULL)";

                $sth = $this->db->prepare($sql);
                $insert = $sth->execute();
                if ($insert) {
                    $mailer->sendListShare($email, $this->getUserName($userId), $this->getUserName($senderUserId), $shareCode);
                    return array('Response' => 201, 'Content' => array('sucess' => true, 'shareCode' => $shareCode));
                } else {
                    return array('Response' => 422);
                }
            } else {
                return array('Response' => 404);
            }
        } else {
            return array('Response' => 404);
        }
    }

    public function activate($code) {
        if (!FormValidator::validateItem($code, 'activatecode')) {
            return array('Response' => 422, 'ValdidateError' => 'activatecode');
        }

        $sql = "SELECT UserListId FROM User2List WHERE ShareCode = '" . $code . "'";
        $sth = $this->db->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        if (count($result) > 0) {
            $sql = "UPDATE User2List SET ShareCode = NULL, ShareActivated = 1 WHERE UserListId = " . $result[0]['UserListId'] . " AND DeletedAt IS NULL";
            $statement = $this->db->prepare($sql);
            $activate = $statement->execute();
            if ($activate) {
                return array('Response' => 200, 'Content' => array('activation' => 'successful'));
            } else {
                return array('Response' => 422, 'Content' => array('activation' => 'not successful'));
            }
        } else {
            return array('Response' => 422, 'Content' => array('activation' => 'not successful'));
        }
    }

    public function delete($listId) {
        if (!FormValidator::validateItem($listId, 'number')) {
            return array('Response' => 422);
        }

        $date = date('Y-m-d H:i:s');
        $userId = $this->getOwnUserId();
        $sql = "SELECT List.ListId FROM List, User2List, User WHERE List.ListId=User2List.ListId AND User2List.UserId = User.UserId AND List.DeletedAt is NULL AND User2List.DeletedAt is NULL AND User2List.Owner=1 AND User.UserId = " . $userId . " AND List.ListId = " . $listId;
        $sth = $this->db->prepare($sql);
        $sth->execute();
        $lists = $sth->fetchAll();

        if (count($lists) > 0) {
            $sql = "UPDATE List SET List.DeletedAt='" . $date . "' WHERE ListId = " . $listId . "; " .
                    "UPDATE User2List SET DeletedAt='" . $date . "' WHERE ListId = " . $listId;
            $sth = $this->db->prepare($sql);
            $sth->execute();
            return array('Response' => 200, 'success' => true);
        } else {
            return array('Response' => 404);
        }
    }

    public function getListItems($listId) {
        if (!FormValidator::validateItem($listId, 'number')) {
            return array('Response' => 422);
        }

        $userId = $this->getOwnUserId();
        $sql = "SELECT Item.ItemId, Item.SortIndex, Item.Name, Item.Deadline FROM List, User2List, User, Item WHERE List.ListId=User2List.ListId AND User2List.UserId = User.UserId AND Item.ListId = List.ListId AND (User2List.Owner=1 OR User2List.ShareActivated=1) AND List.DeletedAt is NULL AND User2List.DeletedAt is NULL AND Item.DeletedAt is NULL AND User.UserId = " . $userId . " AND List.ListId = " . $listId . " ORDER BY Item.SortIndex DESC, Item.ItemId ASC";
        $sth = $this->db->prepare($sql);
        $sth->execute();
        $items = $sth->fetchAll();
        return array('Response' => 200, 'entries' => $items);
    }

    private function checkListPermission($listId, $ownerOnly) {


        $userId = $this->getOwnUserId();
        if($ownerOnly){
        $sql = "SELECT List.ListId FROM List, User2List, User WHERE List.ListId=User2List.ListId AND User2List.UserId = User.UserId AND List.DeletedAt is NULL AND User2List.DeletedAt is NULL AND User2List.Owner=1 AND User.UserId = " . $userId . " AND List.ListId = " . $listId;
        }else{
                  $sql = "SELECT List.ListId FROM List, User2List, User WHERE List.ListId=User2List.ListId AND User2List.UserId = User.UserId AND List.DeletedAt is NULL AND User2List.DeletedAt is NULL AND (User2List.Owner=1 OR User2List.ShareActivated=1) AND User.UserId = " . $userId . " AND List.ListId = " . $listId;  
        }
        
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

}
