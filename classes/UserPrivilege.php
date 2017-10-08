<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidPrivilegeException;
use AMC\Exceptions\InvalidUserException;
use AMC\Exceptions\QueryStatementException;

class UserPrivilege implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_userID = null;
    private $_privilegeID = null;
    private $_connection = null;

    public function __construct($id = null, $userID = null, $privilegeID = null) {
        $this->_id = $id;
        $this->_userID = $userID;
        $this->_privilegeID = $privilegeID;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new UserPrivilege())) {
            throw new BlankObjectException('Cannot store a blank User Privilege.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `User_Privileges` (`UserID`,`PrivilegeID`) VALUES (?,?)")) {
                $stmt->bind_param('ii', $this->_userID, $this->_privilegeID);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new UserPrivilege())) {
            throw new BlankObjectException('Cannot store a blank User Privilege.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `User_Privileges` SET `UserID`=?,`PrivilegeID`=? WHERE `UserPrivilegeID`=?")) {
                $stmt->bind_param('iii', $this->_userID, $this->_privilegeID, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `User_Privileges` WHERE `UserPrivilegeID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_userID == $anotherObject->getUserID() && $this->_privilegeID == $anotherObject->getPrivilegeID()) {
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

    public function getPrivilegeID() {
        return $this->_privilegeID;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setUserID($userID, $verify = false) {
        if($verify) {
            if(User::userExists($userID)) {
                $this->_userID = $userID;
            }
            else {
                throw new InvalidUserException('No user exists with id ' . $userID);
            }
        }
        else {
            $this->_userID = $userID;
        }
    }

    public function setPrivilegeID($privilegeID, $verify = false) {
        if($verify) {
            if(Privilege::privilegeExists($privilegeID)) {
                $this->_privilegeID = $privilegeID;
            }
            else {
                throw new InvalidPrivilegeException('No privilege exists with id ' . $privilegeID);
            }
        }
        else {
            $this->_privilegeID = $privilegeID;
        }
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $userPrivResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `UserPrivilegeID`,`UserID`,`PrivilegeID` FROM `User_Privileges` WHERE `UserPrivilegeID` IN (" . $questionString . ")")) {
                \call_user_func_array(arrat($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($userPrivID, $userID, $privID);
                while($stmt->fetch()) {
                    $userPriv = new UserPrivilege();
                    $userPriv->setID($userPrivID);
                    $userPriv->setUserID($userID);
                    $userPriv->setPrivilegeID($privID);
                    $userPrivResult[] = $userPriv;
                }
                $stmt->close();
                if(\count($userPrivResult) > 0) {
                    return $userPrivResult;
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
            $userPrivResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `UserPrivilegeID`,`UserID`,`PrivilegeID` FROM `User_Privileges`")) {
                $stmt->execute();
                $stmt->bind_result($userPrivID, $userID, $privID);
                while($stmt->fetch()) {
                    $userPriv = new UserPrivilege();
                    $userPriv->setID($userPrivID);
                    $userPriv->setUserID($userID);
                    $userPriv->setPrivilegeID($privID);
                    $userPrivResult[] = $userPriv;
                }
                $stmt->close();
                if(\count($userPrivResult) > 0) {
                    return $userPrivResult;
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
        if($stmt = Database::getConnection()->prepare("SELECT `UserPrivilegeID` FROM `User_Privileges` WHERE `UserID`=?")) {
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->bind_result($privID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $privID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return UserPrivilege::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function getByPrivilegeID($privilegeID) {
        if($stmt = Database::getConnection()->prepare("SELECT `UserPrivilegeID` FROM `User_Privileges` WHERE `PrivilegeID`=?")) {
            $stmt->bind_param('i', $privilegeID);
            $stmt->execute();
            $stmt->bind_result($privID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $privID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return UserPrivilege::get($input);
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