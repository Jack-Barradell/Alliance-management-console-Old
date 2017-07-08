<?php

namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class FactionType implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_factionTypeName = null;
    private $_connection = null;

    public function __construct($id = null, $factionTypeName = null) {
        $this->_id = $id;
        $this->_factionTypeName = $factionTypeName;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new FactionType())) {
            throw new BlankObjectException("Cannot store blank faction type");
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `FactionTypes`(`FactionTypeName`) VALUES (?)")) {
                $stmt->bind_param('s', $this->_factionTypeName);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function update() {
        if($this->eql(new FactionType())) {
            throw new BlankObjectException("Cannot store blank faction type");
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `FactionTypes` SET `FactionTypeName`=? WHERE `FactionTypeID`=?")) {
                $stmt->bind_param('si', $this->_factionTypeName, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `FactionTypes` WHERE `FactionTypeID`=?")) {
            $stmt->bind_params('i', $this->_id);
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
            if($this->_id == $anotherObject->getID() && $this->_factionTypeName == $anotherObject->getFactionTypeName()) {
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

    public function getFactionTypeName() {
        return $this->_factionTypeName;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setFactionTypeName($name) {
        $this->_factionTypeName = $name;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $factionTypeResult = [];
            $typeArray = [];
            $refs = [];
            $typeArray[0] = 'i';
            $questionString = '?';
            foreach($id as $key => $value) {
                $refs[$key] =& $id[$key];
            }
            for($i = 0; $i < \count($id); $i++) {
                $questionString .= ',?';
                $typeArray[0] .= 'i';
            }
            $param = \array_merge($typeArray, $refs);
            if($stmt = Database::getConnection()->prepare("SELECT `FactionTypeID`,`FactionTypeName` FROM `FactionTypes` WHERE `FactionTypeID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($factionTypeID, $factionTypeName);
                while($stmt->fetch()) {
                    $factionType = new FactionType();
                    $factionType->setID($factionTypeID);
                    $factionType->setFactionTypeName($factionTypeName);
                    $factionTypeResult[] = $factionType;
                }
                $stmt->close();
                if(\count($factionTypeResult) > 0) {
                    return $factionTypeResult;
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
            $factionTypeResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `FactionTypeID`,`FactionTypeName` FROM `FactionTypes`")) {
                $stmt->execute();
                $stmt->bind_result($factionTypeID, $factionTypeName);
                while($stmt->fetch()) {
                    $factionType = new FactionType();
                    $factionType->setID($factionTypeID);
                    $factionType->setFactionTypeName($factionTypeName);
                    $factionTypeResult[] = $factionType;
                }
                $stmt->close();
                if(\count($factionTypeResult) > 0) {
                    return $factionTypeResult;
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