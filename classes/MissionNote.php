<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class MissionNote implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_userID = null;
    private $_missionID = null;
    private $_body = null;
    private $_timestamp = null;
    private $_connection = null;

    public function __construct($id = null, $userID = null, $missionID = null, $body = null, $timestamp = null) {
        $this->_id = $id;
        $this->_userID = $userID;
        $this->_missionID = $missionID;
        $this->_body = $body;
        $this->_timestamp = $timestamp;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new MissionNote())) {
            throw new BlankObjectException('Cannot store a blank Mission Note.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Mission_Notes`(`UserID`,`MissionID`,`MissionNoteBody`,`MissionNoteTimestamp`) VALUES (?,?,?,?)")) {
                $stmt->bind_param('iisi', $this->_userID, $this->_missionID, $this->_body, $this->_timestamp);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new MissionNote())) {
            throw new BlankObjectException('Cannot store a blank Mission Note.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Mission_Notes` SET `UserID`=?,`MissionID`=?,`MissionNoteBody`=?,`MissionNoteTimestamp`=? WHERE `MissionNoteID`=?")) {
                $stmt->bind_param('iisii', $this->_userID, $this->_missionID, $this->_body, $this->_timestamp, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Mission_Notes` WHERE `MissionNoteID`=?")) {
            $stmt->bind_param('i', $this->_id);
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public function eql($anotherObject) {
        if(\get_class($this) == \get_class($anotherObject)) {
            if($this->_id == $anotherObject->getID() && $this->_userID == $anotherObject->getUserID() && $this->_missionID == $anotherObject->getMissionID() && $this->_body == $anotherObject->getBody() && $this->_timestamp == $anotherObject->getTimestamp()) {
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
        $this->_userID;
    }

    public function getMissionID() {
        return $this->_missionID;
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

    public function setUserID($userID) {
        $this->_userID = $userID;
    }

    public function setMissionID($missionID) {
        $this->_missionID = $missionID;
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
            $missionNoteResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `MissionNoteID`,`UserID`,`MissionID`,`MissionNoteBody`,`MissionNoteTimestamp` FROM `Mission_Notes` WHERE `MissionNoteID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($missionNoteID, $userID, $missionID, $body, $timestamp);
                while($stmt->fetch()) {
                    $missionNote = new MissionNote();
                    $missionNote->setID($missionNoteID);
                    $missionNote->setUserID($userID);
                    $missionNote->setMissionID($missionID);
                    $missionNote->setBody($body);
                    $missionNote->setTimestamp($timestamp);
                    $missionNoteResult[] = $missionNote;
                }
                $stmt->close();
                if(\count($missionNoteResult) > 0) {
                    return $missionNoteResult;
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
            $missionNoteResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `MissionNoteID`,`UserID`,`MissionID`,`MissionNoteBody`,`MissionNoteTimestamp` FROM `Mission_Notes`")) {
                $stmt->execute();
                $stmt->bind_result($missionNoteID, $userID, $missionID, $body, $timestamp);
                while($stmt->fetch()) {
                    $missionNote = new MissionNote();
                    $missionNote->setID($missionNoteID);
                    $missionNote->setUserID($userID);
                    $missionNote->setMissionID($missionID);
                    $missionNote->setBody($body);
                    $missionNote->setTimestamp($timestamp);
                    $missionNoteResult[] = $missionNote;
                }
                $stmt->close();
                if(\count($missionNoteResult) > 0) {
                    return $missionNoteResult;
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
        if($stmt = Database::getConnection()->prepare("SELECT `MissionNoteID` FROM `Mission_Notes` WHERE `UserID`=?")) {
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->bind_param($missionNoteID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $missionNoteID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return MissionNote::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function getByMissionID($missionID) {
        if($stmt = Database::getConnection()->prepare("SELECT `MissionNoteID` FROM `Mission_Notes` WHERE `MissionID`=?")) {
            $stmt->bind_param('i', $missionID);
            $stmt->execute();
            $stmt->bind_param($missionNoteID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $missionNoteID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return MissionNote::get($input);
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