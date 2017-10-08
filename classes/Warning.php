<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidUserException;
use AMC\Exceptions\QueryStatementException;

class Warning implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_userID = null;
    private $_adminID = null;
    private $_reason = null;
    private $_timestamp = null;
    private $_connection = null;

    public function __construct($id = null, $userID = null, $adminID = null, $warningReason = null, $warningTimestamp = null) {
        $this->_id = $id;
        $this->_userID = $userID;
        $this->_adminID = $adminID;
        $this->_reason = $warningReason;
        $this->_timestamp = $warningTimestamp;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new Warning())) {
            throw new BlankObjectException('Cannot store a blank Warning.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Warnings`(`UserID`,`AdminID`,`WarningReason`,`WarningTimestamp`) VALUES (?,?,?,?)")) {
                $stmt->bind_param('iisi', $this->_userID, $this->_adminID, $this->_reason, $this->_timestamp);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new Warning())) {
            throw new BlankObjectException('Cannot store a blank Warning.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Warnings` SET `UserID`=?,`AdminID`=?,`WarningReason`=?,`WarningTimestamp`=? WHERE `WarningID`=?")) {
                $stmt->bind_param('iisii', $this->_userID, $this->_adminID, $this->_reason, $this->_timestamp, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Warnings` WHERE `WarningID`=?")) {
            $stmt->bind_param('i', $this->_id);
            $this->execute();
            $this->close();
            $this->_id = null;
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public function eql($anotherObject) {
        if(\get_class($this) == \get_class($anotherObject)) {
            if($this->_id == $anotherObject->getID() && $this->_userID == $anotherObject->getUserID() && $this->_adminID == $anotherObject->getAdminID() && $this->_reason == $anotherObject->getWarningReason() && $this->_timestamp == $anotherObject->getWarningReason()) {
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
        return $this->_userID;
    }

    public function getAdminID() {
        return $this->_adminID;
    }

    public function getReason() {
        return $this->_reason;
    }

    public function getTimestamp() {
        return $this->_timestamp;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setUserID($userID, $verify = false) {
        if($verify) {
            if(User::userExists($userID)) {
                $this->_userID = $userID;
            }
            else {
                throw new InvalidUserException('No user exists with id ' . $userID);
            }
        }
        else {
            $this->_userID = $userID;
        }
    }

    public function setAdminID($adminID, $verify = false) {
        if($verify) {
            if(User::userExists($adminID)) {
                $this->_adminID = $adminID;
            }
            else {
                throw new InvalidUserException('No user exists with id ' . $userID);
            }
        }
        else {
            $this->_adminID = $adminID;
        }
    }

    public function setReason($reason) {
        $this->_reason = $reason;
    }

    public function setTimestamp($timestamp) {
        $this->_timestamp = $timestamp;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $warningResult = [];
            $typeArray = [];
            $refs = [];
            $questionString = '?';
            $typeArray[0] = 'i';
            foreach($id as $key => $value) {
                $refs[$key] =& $id[$key];
            }
            for($i = 0; $i < \count($id) - 1; $i++) {
                $typeArray[0] .= 'i';
                $questionString .= ',?';
            }
            $param = \array_merge($typeArray, $refs);
            if($stmt = Database::getConnection()->prepare("SELECT `WarningID`,`UserID`,`AdminID`,`WarningReason`,`WarningTimestamp` FROM `Warnings` WHERE `WarningID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($warningID, $userID, $adminID, $warningReason, $warningTime);
                while($stmt->fetch()) {
                    $warning = new Warning();
                    $warning->setID($warningID);
                    $warning->setUserID($userID);
                    $warning->setAdminID($adminID);
                    $warning->setReason($warningReason);
                    $warning->setTimestamp($warningTime);
                    $warningResult[] = $warning;
                }
                $stmt->close();
                if(\count($warningResult) > 0) {
                    return $warningResult;
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
            $warningResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `WarningID`,`UserID`,`AdminID`,`WarningReason`,`WarningTimestamp` FROM `Warnings`")) {
                $stmt->execute();
                $stmt->bind_result($warningID, $userID, $adminID, $warningReason, $warningTime);
                while($stmt->fetch()) {
                    $warning = new Warning();
                    $warning->setID($warningID);
                    $warning->setUserID($userID);
                    $warning->setAdminID($adminID);
                    $warning->setReason($warningReason);
                    $warning->setTimestamp($warningTime);
                    $warningResult[] = $warning;
                }
                $stmt->close();
                if(\count($warningResult) > 0) {
                    return $warningResult;
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
        if($stmt = Database::getConnection()->prepare("SELECT `WarningID` FROM `Warnings` WHERE `UserID`=?")) {
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->bind_result($warningID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $warningID;
            }
            if(\count($input) > 0) {
                return Warning::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function getByAdminID($adminID) {
        if($stmt = Database::getConnection()->prepare("SELECT `WarningID` FROM `Warnings` WHERE `AdminID`=?")) {
            $stmt->bind_param('i', $adminID);
            $stmt->execute();
            $stmt->bind_result($warningID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $warningID;
            }
            if(\count($input) > 0) {
                return Warning::get($input);
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