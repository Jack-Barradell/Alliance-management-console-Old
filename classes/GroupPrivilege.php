<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidGroupException;
use AMC\Exceptions\InvalidPrivilegeException;
use AMC\Exceptions\QueryStatementException;

class GroupPrivilege implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_groupID = null;
    private $_privilegeID = null;
    private $_connection = null;

    public function __construct($id = null, $groupID = null, $privID = null) {
        $this->_id = $id;
        $this->_groupID = $groupID;
        $this->_privilegeID = $privID;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new GroupPrivilege())) {
            throw new BlankObjectException('Cannot store blank Group Privilege.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Group_Privileges` (`GroupID`,`PrivilegeID`) VALUES (?,?)")) {
                $stmt->bind_param('ii', $this->_groupID, $this->_privilegeID);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new GroupPrivilege())) {
            throw new BlankObjectException('Cannot store blank Group Privilege.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Group_Privileges` SET `GroupID`=?,`PrivilegeID`=? WHERE `GroupPrivilegeID`=?")) {
                $stmt->bind_param('iii', $this->_groupID, $this->_privilegeID, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Group_Privileges` WHERE `GroupPrivilegeID`=?")) {
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
            if($this->_id = $anotherObject->getID() && $this->_groupID == $anotherObject->getGroupID() && $this->_privilegeID == $anotherObject->getPrivilegeID()) {
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

    public function getGroupID() {
        return $this->_groupID;
    }

    public function getPrivilegeID() {
        return $this->_privilegeID;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setGroupID($groupID, $verify = false) {
        if($verify) {
            if(Group::groupExists($groupID)) {
                $this->_groupID = $groupID;
            }
            else {
                throw new InvalidGroupException('No group exists with id ' . $groupID);
            }
        }
        else {
            $this->_groupID = $groupID;
        }
    }

    public function setPrivilegeID($privID, $verify = false) {
        if($verify) {
            if(Privilege::privilegeExists($privID)) {
                $this->_privilegeID = $privID;
            }
            else {
                throw new InvalidPrivilegeException('No privilege exists with id ' . $privID);
            }
        }
        else {
            $this->_privilegeID = $privID;
        }
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $groupPrivResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `GroupPrivilegeID`,`GroupID`,`PrivilegeID` FROM `Group_Privileges` WHERE `GroupPrivilegeID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($groupPrivID, $groupID, $privID);
                while($stmt->fetch()) {
                    $groupPriv = new GroupPrivilege();
                    $groupPriv->setID($groupPrivID);
                    $groupPriv->setGroupID($groupID);
                    $groupPriv->setPrivilegeID($privID);
                    $groupPrivResult[] = $groupPriv;
                }
                $stmt->close();
                if(\count($$groupPrivResult) > 0) {
                    return $groupPrivResult;
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
            $groupPrivResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `GroupPrivilegeID`,`GroupID`,`PrivilegeID` FROM `Group_Privileges`")) {
                $stmt->execute();
                $stmt->bind_result($groupPrivID, $groupID, $privID);
                while($stmt->fetch()) {
                    $groupPriv = new GroupPrivilege();
                    $groupPriv->setID($groupPrivID);
                    $groupPriv->setGroupID($groupID);
                    $groupPriv->setPrivilegeID($privID);
                    $groupPrivResult[] = $groupPriv;
                }
                $stmt->close();
                if(\count($$groupPrivResult) > 0) {
                    return $groupPrivResult;
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

    public static function getByGroupID($groupID) {
        if($stmt = Database::getConnection()->prepare("SELECT `GroupPrivilegeID` FROM `Group_Privileges` WHERE `GroupID`=?")) {
            $stmt->bind_param('i', $groupID);
            $stmt->execute();
            $stmt->bind_result($groupPrivID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $groupPrivID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return GroupPrivilege::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function getByPrivilegeID($privID) {
        if($stmt = Database::getConnection()->prepare("SELECT `GroupPrivilegeID` FROM `Group_Privileges` WHERE `PrivilegeID`=?")) {
            $stmt->bind_param('i', $privID);
            $stmt->execute();
            $stmt->bind_result($groupPrivID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $groupPrivID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return GroupPrivilege::get($input);
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