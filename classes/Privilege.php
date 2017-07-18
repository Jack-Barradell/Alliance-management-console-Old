<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class Privilege implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_name = null;
    private $_connection = null;

    public function __construct($id = null, $name = null) {
        $this->_id = $id;
        $this->_name = $name;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new Privilege())) {
            throw new BlankObjectException("Cannot store a blank privilege");
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Privileges` (`PrivilegeName`) VALUES (?)")) {
                $stmt->bind_param('s', $this->_name);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function update() {
        if($this->eql(new Privilege())) {
            throw new BlankObjectException("Cannot store a blank privilege");
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Privileges` SET `PrivilegeName`=? WHERE `PrivilegeID`=?")) {
                $stmt->bind_param('si', $this->_name, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Privileges` WHERE `PrivilegeID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_name == $anotherObject->getName()) {
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

    // Getters and setter

    public function getID() {
        return $this->_id;
    }

    public function getName() {
        return $this->_name;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setName($name) {
        $this->_name = $name;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $privilegeResult = [];
            $typeArray = [];
            $refs = [];
            $typeArray[0] = 'i';
            $questionString = '?';
            foreach($id as $key => $value) {
                $refs[$key] =& $id[$key];
            }
            for($i = 0; $i < \count($id); $i++) {
                $typeArray[0] .= 'i';
                $questionString .= ',?';
            }
            $param = \array_merge($typeArray, $refs);
            if($stmt = Database::getConnection()->prepare("SELECT `PrivilegeID`,`PrivilegeName` FROM `Privileges` WHERE `PrivilegeID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($privID, $name);
                while($stmt->fetch()) {
                    $privilege = new Privilege();
                    $privilege->setID($privID);
                    $privilege->setName($name);
                    $privilegeResult[] = $privilege;
                }
                $stmt->close();
                if(\count($privilegeResult) > 0) {
                    return $privilegeResult;
                }
                else {
                    return null;
                }
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
        else if(\is_array($id) && \count($id) == 0) {
            $privilegeResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `PrivilegeID`,`PrivilegeName` FROM `Privileges`")) {
                $stmt->execute();
                $stmt->bind_result($privID, $name);
                while($stmt->fetch()) {
                    $privilege = new Privilege();
                    $privilege->setID($privID);
                    $privilege->setName($name);
                    $privilegeResult[] = $privilege;
                }
                $stmt->close();
                if(\count($privilegeResult) > 0) {
                    return $privilegeResult;
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

}