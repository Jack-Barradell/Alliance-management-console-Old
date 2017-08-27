<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class UserGroup implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_userID = null;
    private $_groupID = null;
    private $_admin = null;
    private $_connection = null;

    public function __construct($id = null, $userID = null, $groupID = null, $admin = null) {
        $this->_id = $id;
        $this->_userID = $userID;
        $this->_groupID = $groupID;
        $this->_admin = $admin;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new UserGroup())) {
            throw new BlankObjectException('Cannot store a blank User Group.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `User_Groups` (`UserID`,`GroupID`,`UserGroupAdmin`) VALUES (?,?,?)")) {
                $stmt->bind_param('iii', $this->_userID, $this->_groupID, Database::toNumeric($this->_admin));
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new UserGroup())) {
            throw new BlankObjectException('Cannot store a blank User Group.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `User_Groups` SET `UserID`=?,`GroupID`=?,`UserGroupAdmin`=? WHERE `UserGroupID`=?")) {
                $stmt->bind_param('iiii', $this->_userID, $this->_groupID, Database::toNumeric($this->_admin), $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `User_Groups` WHERE `UserGroupID`=?")) {
            $stmt->bind_param('i', $this->_id);
            $stmt->execute();
            $stmt->close();
            $this->_id = null;
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public function eql($anotherObject) {
        if(\get_class($this) == \get_class($anotherObject)) {
            if($this->_id == $anotherObject->getID() && $this->_userID == $anotherObject->getUserID() && $this->_groupID == $anotherObject->getGroupID() && $this->_admin == $anotherObject->getAdmin()) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    // Getters and setters

    public function getID() {
        return $this->_id;
    }

    public function getUserID() {
        return $this->_userID;
    }

    public function getGroupID() {
        return $this->_groupID;
    }

    public function getAdmin() {
        return $this->_admin;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setUserID($userID) {
        $this->_userID = $userID;
    }

    public function setGroupID($groupID) {
        $this->_groupID = $groupID;
    }

    public function setAdmin($admin) {
        $this->_admin = $admin;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $userGroupResult = [];
            $typeArray = [];
            $refs = [];
            $typeArray[0] = 'i';
            $questionString = '?';
            foreach($id as $key => $value) {
                $refs[$key] =& $id[$key];
            }
            for($i = 0; $i < \count($id) - 1; $i++) {
                $typeArray[0] .= 'i';
                $questionString .= ',?';
            }
            $param = \array_merge($typeArray, $refs);
            if($stmt = Database::getConnection()->prepare("SELECT `UserGroupID`,`UserID`,`GroupID`,`UserGroupAdmin` FROM `User_Groups` WHERE `UserGroupID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($userGroupID, $userID, $groupID, $admin);
                while($stmt->fetch()) {
                    $userGroup = new UserGroup();
                    $userGroup->setID($userGroupID);
                    $userGroup->setUserID($userID);
                    $userGroup->setGroupID($groupID);
                    $userGroup->setAdmin(Database::toBoolean($admin));
                    $userGroupResult[] = $userGroup;
                }
                $stmt->close();
                if(\count($userGroupResult) > 0) {
                    return $userGroupResult;
                }
                else {
                    return null;
                }
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
        else if(\is_array($id) && \count($id) == 0) {
            $userGroupResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `UserGroupID`,`UserID`,`GroupID`,`UserGroupAdmin` FROM `User_Groups`")) {
                $stmt->execute();
                $stmt->bind_result($userGroupID, $userID, $groupID, $admin);
                while($stmt->fetch()) {
                    $userGroup = new UserGroup();
                    $userGroup->setID($userGroupID);
                    $userGroup->setUserID($userID);
                    $userGroup->setGroupID($groupID);
                    $userGroup->setAdmin(Database::toBoolean($admin));
                    $userGroupResult[] = $userGroup;
                }
                $stmt->close();
                if(\count($userGroupResult) > 0) {
                    return $userGroupResult;
                }
                else {
                    return null;
                }
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
        else {
            return null;
        }
    }

    public static function getByUserID($userID) {
        if($stmt = Database::getConnection()->prepare("SELECT `UserGroupID` FROM `User_Groups` WHERE `UserID`=?")) {
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->bind_result($userGroupID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $userGroupID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return UserGroup::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function getByGroupID($groupID) {
        if($stmt = Database::getConnection()->prepare("SELECT `UserGroupID` FROM `User_Groups` WHERE `GroupID`=?")) {
            $stmt->bind_param('i', $groupID);
            $stmt->execute();
            $stmt->bind_result($userGroupID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $userGroupID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return UserGroup::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

}