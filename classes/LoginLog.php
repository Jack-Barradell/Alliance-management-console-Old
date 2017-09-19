<?php
namespace AMC\Classes;
//TODO: Add verify user id
use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class LoginLog implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_userID = null;
    private $_result = null;
    private $_ip = null;
    private $_timestamp = null;
    private $_connection = null;

    public function __construct($id = null, $userID = null, $result = null, $ip = null, $timestamp = null) {
        $this->_id = $id;
        $this->_userID = $userID;
        $this->_result = $result;
        $this->_ip = $ip;
        $this->_timestamp = $timestamp;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new LoginLog())) {
            throw new BlankObjectException('Cannot store blank Login Log.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Login_Log` (`UserID`,`LoginLogResult`,`LoginLogIP`,`LoginLogTimestamp`) VALUES (?,?,?,?)")) {
                $stmt->bind_param('issi', $this->_userID, $this->_result, $this->_ip, $this->_timestamp);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new LoginLog())) {
            throw new BlankObjectException('Cannot store blank Login Log.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Login_Log` SET `UserID`=?,`LoginLogResult`=?,`LoginLogIP`=?,`LoginLogTimestamp`=? WHERE `LoginLogID`=?")) {
                $stmt->bind_param('issii', $this->_userID, $this->_result, $this->_ip, $this->_timestamp, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Login_Log` WHERE `LoginLogID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_userID == $anotherObject->getUserID() && $this->_result == $anotherObject->getResult() && $this->_ip == $anotherObject->getIP() && $this->_timestamp == $anotherObject->getTimestamp()) {
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

    // Getters and Setters

    public function getID() {
        return $this->_id;
    }

    public function getUserID() {
        return $this->_userID;
    }

    public function getResult() {
        return $this->_result;
    }

    public function getIP() {
        return $this->_ip;
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

    public function setResult($result) {
        $this->_result = $result;
    }

    public function setIP($ip) {
        $this->_ip = $ip;
    }

    public function setTimestamp($timestamp) {
        $this->_timestamp = $timestamp;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $loginLogResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `LoginLogID`,`UserID`,`LoginLogResult`,`LoginLogIP`,`LoginLogTimestamp` FROM `Login_Log` WHERE `LoginLogID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($loginLogID, $userID, $loginResult, $loginIP, $loginTimestamp);
                while($stmt->fetch()){
                    $loginLog = new LoginLog();
                    $loginLog->setID($loginLogID);
                    $loginLog->setUserID($userID);
                    $loginLog->setResult($loginResult);
                    $loginLog->setIP($loginIP);
                    $loginLog->setTimestamp($loginTimestamp);
                    $loginLogResult[] = $loginLog;
                }
                $stmt->close();
                if(\count($loginLogResult) > 0) {
                    return $loginLogResult;
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
            if($stmt = Database::getConnection()->prepare("SELECT `LoginLogID`,`UserID`,`LoginLogResult`,`LoginLogIP`,`LoginLogTimestamp` FROM `Login_Log`")) {
                $stmt->execute();
                $stmt->bind_result($loginLogID, $userID, $loginResult, $loginIP, $loginTimestamp);
                while($stmt->fetch()){
                    $loginLog = new LoginLog();
                    $loginLog->setID($loginLogID);
                    $loginLog->setUserID($userID);
                    $loginLog->setResult($loginResult);
                    $loginLog->setIP($loginIP);
                    $loginLog->setTimestamp($loginTimestamp);
                    $loginLogResult[] = $loginLog;
                }
                $stmt->close();
                if(\count($loginLogResult) > 0) {
                    return $loginLogResult;
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
        if($stmt = Database::getConnection()->prepare("SELECT `LoginLogID` FROM `Login_Log` WHERE `UserID`=?")) {
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->bind_result($loginLogID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $loginLogID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return LoginLog::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function getByIP($ip) {
        if($stmt = Database::getConnection()->prepare("SELECT `LoginLogID` FROM `Login_Log` WHERE `LoginLogIP`=?")) {
            $stmt->bind_param('s', $ip);
            $stmt->execute();
            $stmt->bind_result($loginLogID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $loginLogID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return LoginLog::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function getByTimestamp($timestamp) {
        if($stmt = Database::getConnection()->prepare("SELECT `LoginLogID` FROM `LoginLog` WHERE `LoginLogTimestamp`=?")) {
            $stmt->bind_param('s', $timestamp);
            $stmt->execute();
            $stmt->bind_result($loginLogID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $loginLogID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return LoginLog::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function getByResult($result) {
        if($stmt = Database::getConnection()->prepare("SELECT `LoginLogID` FROM `Login_Log` WHERE `Result`=?")) {
            $stmt->bind_param('s', $result);
            $stmt->execute();
            $stmt->bind_result($loginLogID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $loginLogID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return LoginLog::get($input);
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