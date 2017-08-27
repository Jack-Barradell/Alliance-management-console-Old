<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class IntelligenceType implements DataObject {
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
        if($this->eql(new IntelligenceType())) {
            throw new BlankObjectException('Cannot store a blank Intelligence Type.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Intelligence_Types`(`IntelligenceTypeName`) VALUES (?)")) {
                $stmt->bind_param('s', $this->_name);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new IntelligenceType())) {
            throw new BlankObjectException('Cannot store a blank Intelligence Type.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Intelligence_Types` SET `IntelligenceTypeName`=? WHERE `IntelligenceTypeID`=?")) {
                $stmt->bind_param('si', $this->_name, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Intelligence_Types` WHERE `IntelligenceTypeID`=?")) {
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

    // Setters and getters

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
            $intelligenceTypeResult = [];
            $refs = [];
            $typeArray = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceTypeID`,`IntelligenceTypeName` FROM `Intelligence_Types` WHERE `IntelligenceTypeID` IN ("  . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($intelligenceTypeID, $name);
                while($stmt->fetch()) {
                    $intelligenceType = new IntelligenceType();
                    $intelligenceType->setID($intelligenceTypeID);
                    $intelligenceType->setName($name);
                    $intelligenceTypeResult[] = $intelligenceType;
                }
                $stmt->close();
                if(\count($intelligenceTypeResult) > 0) {
                    return $intelligenceTypeResult;
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
            $intelligenceTypeResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceTypeID`,`IntelligenceTypeName` FROM `Intelligence_Types`")) {
                $stmt->execute();
                $stmt->bind_result($intelligenceTypeID, $name);
                while($stmt->fetch()) {
                    $intelligenceType = new IntelligenceType();
                    $intelligenceType->setID($intelligenceTypeID);
                    $intelligenceType->setName($name);
                    $intelligenceTypeResult[] = $intelligenceType;
                }
                $stmt->close();
                if(\count($intelligenceTypeResult) > 0) {
                    return $intelligenceTypeResult;
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
}