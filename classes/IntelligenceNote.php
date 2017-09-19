<?php
//TODO: add verify user id
//TODO: add verify intelligence id
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class IntelligenceNote implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_intelligenceID = null;
    private $_userID = null;
    private $_body = null;
    private $_timestamp = null;
    private $_connection = null;

    public function __construct($id = null, $intelligenceID = null, $userID = null, $body = null, $timestamp = null) {
        $this->_id = $id;
        $this->_intelligenceID = $intelligenceID;
        $this->_userID = $userID;
        $this->_body = $body;
        $this->_timestamp = $timestamp;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new IntelligenceNote())) {
            throw new BlankObjectException('Cannot store a blank Intelligence Note.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Intelligence_Notes`(`IntelligenceID`,`UserID`,`IntelligenceNoteBody`,`IntelligenceNoteTimestamp`) VALUES (?,?,?,?)")) {
                $stmt->bind_param('iisi', $this->_intelligenceID, $this->_userID, $this->_body, $this->_timestamp);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new IntelligenceNote())) {
            throw new BlankObjectException('Cannot store a blank Intelligence Note.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Intelligence_Notes` SET `IntelligenceID`=?,`UserID`=?,`IntelligenceNoteBody`=?,`IntelligenceNoteTimestamp`=? WHERE `IntelligenceNoteID`=?")) {
                $stmt->bind_param('iisii', $this->_intelligenceID, $this->_userID, $this->_body, $this->_timestamp, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Intelligence_Notes` WHERE `IntelligenceNoteID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_intelligenceID == $anotherObject->getIntelligenceID() && $this->_userID == $anotherObject->getUserID() && $this->_body == $anotherObject->getBody() && $this->_timestamp == $anotherObject->getTimestamp()) {
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

    public function getIntelligenceID() {
        return $this->_intelligenceID;
    }

    public function getUserID() {
        return $this->_userID;
    }

    public function getBody() {
        return $this->_body;
    }

    public function getTimestamp() {
        return $this->_timestamp;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setIntelligenceID($intelligenceID) {
        $this->_intelligenceID = $intelligenceID;
    }

    public function setUserID($userID) {
        $this->_userID = $userID;
    }

    public function setBody($body) {
        $this->_body = $body;
    }

    public function setTimestamp($timestamp) {
        $this->_timestamp = $timestamp;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $intelligenceNoteResult  = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceNoteID`,`IntelligenceID`,`UserID`,`IntelligenceNoteBody`,`IntelligenceNoteTimestamp` FROM `Intelligence_Notes` WHERE `IntelligenceNoteID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($intelligenceNoteID, $intelligenceID, $userID, $body, $timestamp);
                while($stmt->fetch()) {
                    $intelligenceNote = new IntelligenceNote();
                    $intelligenceNote->setID($intelligenceNoteID);
                    $intelligenceNote->setIntelligenceID($intelligenceID);
                    $intelligenceNote->setUserID($userID);
                    $intelligenceNote->setBody($body);
                    $intelligenceNote->setTimestamp($timestamp);
                    $intelligenceNoteResult[] = $intelligenceNote;
                }
                if(\count($intelligenceNoteResult) > 0) {
                    return $intelligenceNoteResult;
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
            $intelligenceNoteResult  = [];
            if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceNoteID`,`IntelligenceID`,`UserID`,`IntelligenceNoteBody`,`IntelligenceNoteTimestamp` FROM `Intelligence_Notes`")) {
                $stmt->execute();
                $stmt->bind_result($intelligenceNoteID, $intelligenceID, $userID, $body, $timestamp);
                while($stmt->fetch()) {
                    $intelligenceNote = new IntelligenceNote();
                    $intelligenceNote->setID($intelligenceNoteID);
                    $intelligenceNote->setIntelligenceID($intelligenceID);
                    $intelligenceNote->setUserID($userID);
                    $intelligenceNote->setBody($body);
                    $intelligenceNote->setTimestamp($timestamp);
                    $intelligenceNoteResult[] = $intelligenceNote;
                }
                if(\count($intelligenceNoteResult) > 0) {
                    return $intelligenceNoteResult;
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

    public static function getByIntelligenceID($intelligenceID) {
        if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceNoteID` FROM `Intelligence_Notes` WHERE `IntelligenceID`=?")) {
            $stmt->bind_param('i', $intelligenceID);
            $stmt->execute();
            $stmt->bind_result($intelligenceNoteID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $intelligenceNoteID;
            }
            if(\count($input) > 0) {
                return IntelligenceNote::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function getByUserID($userID) {
        if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceNoteID` FROM `Intelligence_Notes` WHERE `UserID`=?")) {
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->bind_result($intelligenceNoteID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $intelligenceNoteID;
            }
            if(\count($input) > 0) {
                return IntelligenceNote::get($input);
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