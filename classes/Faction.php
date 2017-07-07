<?php

namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class Faction implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_factionTypeID = null;
    private $_factionName = null;
    private $_connection = null;

    public function __construct($id = null, $factionTypeID = null, $factionName = null) {
        $this->_id = $id;
        $this->_factionTypeID = $factionTypeID;
        $this->_factionName = $factionName;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new Faction())) {
            throw new BlankObjectException("Cannot store blank faction");
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Factions` (`FactionTypeID`, `FactionName`) VALUES (?,?)")) {
                $stmt->bind_param('is', $this->_factionTypeID, $this->_factionName);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function update() {
        if($this->eql(new Faction())) {
            throw new BlankObjectException("Cannot store blank faction");
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Factions` SET `FactionTypeID`=?,`FactionName`=? WHERE `FactionID`=?")) {
                $stmt->bind_param('isi', $this->_factionTypeID, $this->_factionName, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Factions` WHERE `FactionID`=?")) {
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
        if(\get_class($anotherObject) == \get_class($this)) {
            if($this->_id == $anotherObject->getID() && $this->_factionTypeID == $anotherObject->getFactionTypeID() && $this->_factionName == $anotherObject->getFactionName()) {
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

    public function getFactionTypeID() {
        return $this->_factionTypeID;
    }

    public function getFactionName() {
        return $this->_factionName;
    }

    public function setID($newID) {
        $this->_id = $newID;
    }

    public function setFactionTypeID($factionTypeID) {
        $this->_factionTypeID = $factionTypeID;
    }

    public function setFactionName($name) {
        $this->_factionName = $name;
    }

    // STATICS

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $factionResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `FactionID`,`FactionTypeID`,`FactionName` FROM `Factions` WHERE `FactionID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($factionID, $factionTypeID, $factionName);
                while($stmt->fetch()) {
                    $faction = new Faction();
                    $faction->setID($factionID);
                    $faction->setFactionTypeID($factionTypeID);
                    $faction->setFactionName($factionName);
                    $factionResult[] = $faction;
                }
                $stmt->close();
                if(\count($factionResult) > 0) {
                    return $factionResult;
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
            $factionResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `FactionID`,`FactionTypeID`,`FactionName` FROM `Factions`")) {
                $stmt->execute();
                $stmt->bind_result($factionID, $factionTypeID, $factionName);
                while($stmt->fetch()) {
                    $faction = new Faction();
                    $faction->setID($factionID);
                    $faction->setFactionTypeID($factionTypeID);
                    $faction->setFactionName($factionName);
                    $factionResult[] = $faction;
                }
                $stmt->close();
                if(\count($factionResult) > 0) {
                    return $factionResult;
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