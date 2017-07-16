<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class ErrorLog implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_type = null;
    private $_message = null;
    private $_systemError = null;
    private $_timestamp = null;
    private $_connection = null;

    public function __construct($id = null, $type = null, $message = null, $systemError = null, $timestamp = null) {
        $this->_id = $id;
        $this->_type = $type;
        $this->_message = $message;
        $this->_systemError = $systemError;
        $this->_timestamp = $timestamp;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new ErrorLog())) {
            throw new BlankObjectException("Cannot store a blank ErrorLog");
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Error_Log` (`ErrorLogType`,`ErrorLogMessage`,`ErrorLogSystemError`,`ErrorLogTimestamp`) VALUES (?,?,?,?)")) {
                $stmt->bind_param('sssi', $this->_type, $this->_message, $this->_systemError, $this->_timestamp);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function update() {
        if($this->eql(new ErrorLog())) {
            throw new BlankObjectException("Cannot store a blank ErrorLog");
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Error_Log` SET `ErrorLogType`=?,`ErrorLogMessage`=?,`ErrorLogSystemError`=?,`ErrorLogTimestamp`=? WHERE `ErrorLogID`=?")) {
                $stmt->bind_param('sssii', $this->_type, $this->_message, $this->_systemError, $this->_timestamp, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Error_Log` WHERE `ErrorLogID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_type == $anotherObject->getType() && $this->_message == $anotherObject->getMessage() && $this->_systemError == $anotherObject->getSystemError() && $this->_timestamp == $anotherObject->getTimestamp()) {
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

    public function getType() {
        return $this->_type;
    }

    public function getMessage() {
        return $this->_message;
    }

    public function getSystemError() {
        return $this->_systemError;
    }

    public function getTimestamp() {
        return $this->_timestamp;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setType($type) {
        $this->_type = $type;
    }

    public function setMessage($message) {
        $this->_message = $message;
    }

    public function setSystemError($systemError) {
        $this->_systemError = $systemError;
    }

    public function setTimestamp($timestamp) {
        $this->_timestamp = $timestamp;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $errorLogResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `ErrorLogID`,`ErrorLogType`,`ErrorLogMessage`,`ErrorLogSystemError`,`ErrorLogTimestamp` FROM `Error_Log` WHERE `ErrorLogID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($errorID, $type, $message, $systemError, $timestamp);
                while($stmt->fetch()) {
                    $errorLog = new ErrorLog();
                    $errorLog->setID($errorID);
                    $errorLog->setType($type);
                    $errorLog->setMessage($message);
                    $errorLog->setSystemError($systemError);
                    $errorLog->setTimestamp($timestamp);
                    $errorLogResult[] = $errorLog;
                }
                $stmt->close();
                if(\count($errorLogResult) > 0) {
                    return $errorLogResult;
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
            $errorLogResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `ErrorLogID`,`ErrorLogType`,`ErrorLogMessage`,`ErrorLogSystemError`,`ErrorLogTimestamp` FROM `Error_Log`")) {
                $stmt->execute();
                $stmt->bind_result($errorID, $type, $message, $systemError, $timestamp);
                while($stmt->fetch()) {
                    $errorLog = new ErrorLog();
                    $errorLog->setID($errorID);
                    $errorLog->setType($type);
                    $errorLog->setMessage($message);
                    $errorLog->setSystemError($systemError);
                    $errorLog->setTimestamp($timestamp);
                    $errorLogResult[] = $errorLog;
                }
                $stmt->close();
                if(\count($errorLogResult) > 0) {
                    return $errorLogResult;
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