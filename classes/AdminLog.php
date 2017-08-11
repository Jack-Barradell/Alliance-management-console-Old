<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class AdminLog implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_adminID = null;
    private $_event = null;
    private $_timestamp = null;
    private $_connection = null;

    public function __construct($id = null, $adminID = null, $event = null, $timestamp = null) {
        $this->_id = $id;
        $this->_adminID = $adminID;
        $this->_event = $event;
        $this->_timestamp = $timestamp;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new AdminLog())) {
            throw new BlankObjectException("Cannot store a blank admin log");
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Admin_Log` (`AdminID`,`AdminLogEvent`,`AdminLogTimestamp`) VALUES (?,?,?)")) {
                $stmt->bind_param('isi', $this->_adminID, $this->_event, $this->_timestamp);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function update() {
        if($this->eql(new AdminLog())) {
            throw new BlankObjectException("Cannot store a blank admin log");
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Admin_Log` SET `AdminID`=?,`AdmingLogEvent`=?,`AdminLogTimestamp`=? WHERE `AdminLogID`=?")) {
                $stmt->bind_param('isii', $this->_adminID, $this->_event, $this->_timestamp, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Admin_Log` WHERE `AdminLogID`=?")) {
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
        if(\get_class($this) && \get_class($anotherObject)) {
            if($this->_id == $anotherObject->getID() && $this->_adminID == $anotherObject->getAdminID() && $this->_event == $anotherObject->getEvent() && $this->_timestamp == $anotherObject->getTimestamp()) {
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

    public function getAdminID() {
        return $this->_adminID;
    }

    public function getEvent() {
        return $this->_event;
    }

    public function getTimestamp() {
        return $this->_timestamp;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setAdminID($adminID) {
        $this->_adminID = $adminID;
    }

    public function setEvent($event) {
        $this->_event = $event;
    }

    public function setTimestamp($timestamp) {
        $this->_timestamp = $timestamp;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $adminLogResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `AdminLogID`,`AdminID`,`AdminLogEvent`,`AdminLogTimestamp` FROM `Admin_Log` WHERE `AdminLogID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($adminLogID, $adminID, $event, $timestamp);
                while($stmt->fetch()) {
                    $adminLog = new AdminLog();
                    $adminLog->setID($adminLogID);
                    $adminLog->setAdminID($adminID);
                    $adminLog->setEvent($event);
                    $adminLog->setTimestamp($timestamp);
                    $adminLogResult[] = $adminLog;
                }
                $stmt->close();
                if(\count($adminLogResult) > 0) {
                    return $adminLogResult;
                }
                else {
                    return null;
                }
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
        else if(\is_array($id) && \count($id) == 0 ) {
            $adminLogResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `AdminLogID`,`AdminID`,`AdminLogEvent`,`AdminLogTimestamp` FROM `Admin_Log`")) {
                $stmt->execute();
                $stmt->bind_result($adminLogID, $adminID, $event, $timestamp);
                while($stmt->fetch()) {
                    $adminLog = new AdminLog();
                    $adminLog->setID($adminLogID);
                    $adminLog->setAdminID($adminID);
                    $adminLog->setEvent($event);
                    $adminLog->setTimestamp($timestamp);
                    $adminLogResult[] = $adminLog;
                }
                $stmt->close();
                if(\count($adminLogResult) > 0) {
                    return $adminLogResult;
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

    public static function getByAdminID($adminID) {
        if($stmt = Database::getConnection()->prepare("SELECT `AdminLogID` FROM `Admin_Logs` WHERE `AdminID`=?")) {
            $stmt->bind_param('i', $adminID);
            $stmt->execute();
            $stmt->bind_result($adminLogID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $adminLogID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return AdminLog::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException("Failed to bind query");
        }
    }

}