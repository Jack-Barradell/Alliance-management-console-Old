<?php
namespace AMC\Classes;


use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\IncorrectTypeException;
use AMC\Exceptions\QueryStatementException;
use AMC\Exceptions\NullGetException;

class Group implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_name = null;
    private $_hidden = null;
    private $_connection = null;

    public function __construct($id = null, $name = null, $hidden = null) {
        $this->_id = $id;
        $this->_name = $name;
        $this->_hidden = $hidden;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new Group())) {
            throw new BlankObjectException("Cannot store a blank group");
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Groups` (`GroupName`,`GroupHidden`) VALUES (?,?)")) {
                $stmt->bind_param('si', $this->_name, Database::toNumeric($this->_hidden));
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function update() {
        if($this->eql(new Group())) {
            throw new BlankObjectException("Cannot store a blank group");
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Groups` SET `GroupName`=?,`GroupHidden`=? WHERE `GroupID`=?")) {
                $stmt->bind_param('sii', $this->_name, Database::toNumeric($this->_hidden), $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Groups` WHERE `GroupID`=?")) {
            $stmt->bind_param('i', $this->_id);
            $stmt->execute();
            $stmt->close();
            $this->_id = null;
        }
        else {
            throw new QueryStatementException("Failed to bind query");
        }
    }

    public function eql($anotherObject) {
        if(\get_class($this) == \get_class($anotherObject)) {
            if($this->_id == $anotherObject->getID() && $this->_name == $anotherObject->getName() && $this->_hidden == $anotherObject->getHidden()) {
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

    public function hasGroupPrivilege($privilegeName) {
        $privilegeArray = Privilege::getByName($privilegeName);
        if(\is_null($privilegeArray)) {
            throw new NullGetException("No privilege found with name " . $privilegeName);
        }
        else {
            $privID = $privilegeArray[0]->getPrivilegeID();
            $groupPrivs = GroupPrivilege::getByGroupID($this->_id);
            foreach($groupPrivs as $groupPriv) {
                if($groupPriv->getPrivilegeID() == $privID) {
                    return true;
                }
            }
            return false;
        }
    }

    // Getters and setters

    public function getID() {
        return $this->_id;
    }

    public function getName() {
        return $this->_name;
    }

    public function getHidden() {
        return $this->_hidden;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setName($name) {
        $this->_name = $name;
    }

    public function setHidden($hidden) {
        $this->_hidden = $hidden;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $groupResult = [];
            $typeArray = [];
            $refs = [];
            $typeArray[0] = 'i';
            $questionString  = '?';
            foreach($id as $key => $value) {
                $refs[$key] =& $id[$key];
            }
            for($i = 0; $i < \count($id); $i++) {
                $typeArray[0] .= 'i';
                $questionString .= ',?';
            }
            $param = \array_merge($typeArray, $refs);
            if($stmt = Database::getConnection()->prepare("SELECT `GroupID`,`GroupName`,`GroupHidden` FROM `Groups` WHERE `GroupID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($groupID, $name, $hidden);
                while($stmt->fetch()) {
                    $group = new Group();
                    $group->setID($groupID);
                    $group->setName($name);
                    $group->setHidden(Database::toBoolean($hidden));
                    $groupResult[] = $group;
                }
                $stmt->close();
                if(\count($groupResult) > 0) {
                    return $groupResult;
                }
                else {
                    return null;
                }
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
        else if(\is_array($id) && \count($id) == 0){
            $groupResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `GroupID`,`GroupName`,`GroupHidden` FROM `Groups`")) {
                $stmt->execute();
                $stmt->bind_result($groupID, $name, $hidden);
                while($stmt->fetch()) {
                    $group = new Group();
                    $group->setID($groupID);
                    $group->setName($name);
                    $group->setHidden(Database::toBoolean($hidden));
                    $groupResult[] = $group;
                }
                $stmt->close();
                if(\count($groupResult) > 0) {
                    return $groupResult;
                }
                else {
                    return null;
                }
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
        else {
            return null;
        }
    }

    public static function groupExists($groupNameOrID, $returnGroupNameOrID = false) {
        if(\is_numeric($groupNameOrID)) {
            if($stmt = Database::getConnection()->prepare("SELECT `GroupName` FROM `Groups` WHERE `GroupID`=?")) {
                $stmt->bind_param('i', $groupNameOrID);
                $stmt->execute();
                $stmt->bind_result($name);
                if($stmt->num_rows == 1) {
                    $stmt->fetch();
                    $stmt->close();
                    if($returnGroupNameOrID) {
                        return $name;
                    }
                    else {
                        return true;
                    }
                }
                else {
                    return false;
                }
            }
            else {
                throw new QueryStatementException('Failed to bind query');
            }
        }
        else if(\is_string($groupNameOrID)) {
            if($stmt = Database::getConnection()->prepare("SELECT `GroupID` FROM `Groups` WHERE `GroupName`=?")) {
                $stmt->bind_param('s', $groupNameOrID);
                $stmt->execute();
                $stmt->bind_result($id);
                if($stmt->num_rows == 1) {
                    $stmt->fetch();
                    $stmt->close();
                    if($returnGroupNameOrID) {
                        return $id;
                    }
                    else {
                        return true;
                    }
                }
                else {
                    return false;
                }
            }
            else {
                throw new QueryStatementException('Failed to bind query');
            }
        }
        else {
            throw new IncorrectTypeException('Group exists must be passed an int or string, was given ' . $groupNameOrID);
        }
    }

}