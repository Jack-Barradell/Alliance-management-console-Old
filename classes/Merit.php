<?php
//TODO: Add verify user id
//TODO: Add verify admin id

namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class Merit implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_userID = null;
    private $_adminID = null;
    private $_value = null;
    private $_reason = null;
    private $_timestamp = null;
    private $_connection = null;

    public function __construct($id = null, $userID = null, $adminID = null, $meritValue = null, $meritReason = null, $meritTimestamp = null) {
        $this->_id = $id;
        $this->_userID = $userID;
        $this->_adminID = $adminID;
        $this->_value = $meritValue;
        $this->_reason = $meritReason;
        $this->_timestamp = $meritTimestamp;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new Merit())) {
            throw new BlankObjectException('Cannot store a blank Merit.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Merits` (`UserID`,`AdminID`,`MeritValue`,`MeritReason`,`MeritTimestamp`) VALUES (?,?,?,?,?)")) {
                $stmt->bind_param('iiisi', $this->_userID, $this->_adminID, $this->_value, $this->_reason, $this->_timestamp);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new Merit())) {
            throw new BlankObjectException('Cannot store a blank Merit.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Merits` SET `UserID`=?,`AdminID`=?,`MeritValue`=?,`MeritReason`=?,`MeritTimestamp`=? WHERE `MeritID`=? ")) {
                $stmt->bind_param('iiisii', $this->_userID, $this->_adminID, $this->_value, $this->_reason, $this->_timestamp, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }


    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Merits` WHERE `MeritID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_userID == $anotherObject->getUserID() && $this->_adminID == $anotherObject->getAdminID() && $this->_value == $anotherObject->getMeritValue() && $this->_reason == $anotherObject->getMeritReason() && $this->_timestamp == $anotherObject->getMeritTimestamp) {
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

    public function getValue() {
        return $this->_value;
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

    public function setUserID($userID) {
        $this->_userID = $userID;
    }

    public function setAdminID($adminID) {
        $this->_adminID = $adminID;
    }

    public function setValue($meritValue) {
        $this->_value = $meritValue;
    }

    public function setReason($meritReason) {
        $this->_reason = $meritReason;
    }

    public function setTimestamp($meritTimestamp) {
        $this->_timestamp = $meritTimestamp;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $meritResult = [];
            $typeArray = [];
            $refs = [];
            $typeArray[0] = 'i';
            $questionString = '?';
            foreach($id as $key => $value) {
                $refs[$key] =& $id[$key];
            }
            for($i = 0; $i  < \count($id) - 1; $i++) {
                $typeArray[0] .= 'i';
                $questionString .= ',?';
            }
            $param = \array_merge($typeArray, $refs);
            if($stmt = Database::getConnection()->prepare("SELECT `MeritID`,`UserID`,`AdminID`,`MeritValue`,`MeritReason`,`MeritTimestamp` FROM `Merits` WHERE `MeritID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($meritID, $userID, $adminID, $meritValue, $meritReason, $meritTimestamp);
                while ($stmt->fetch()) {
                    $merit = new Merit();
                    $merit->setID($meritID);
                    $merit->setUserID($userID);
                    $merit->setAdminID($adminID);
                    $merit->setValue($meritValue);
                    $merit->setReason($meritReason);
                    $merit->setTimestamp($meritTimestamp);
                    $meritResult[] = $merit;
                }
                $stmt->close();
                if(\count($meritResult) > 0) {
                    return $meritResult;
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
            $meritResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `MeritID`,`UserID`,`AdminID`,`MeritValue`,`MeritReason`,`MeritTimestamp` FROM `Merits`")) {
                $stmt->execute();
                $stmt->bind_result($meritID, $userID, $adminID, $meritValue, $meritReason, $meritTimestamp);
                while ($stmt->fetch()) {
                    $merit = new Merit();
                    $merit->setID($meritID);
                    $merit->setUserID($userID);
                    $merit->setAdminID($adminID);
                    $merit->setValue($meritValue);
                    $merit->setReason($meritReason);
                    $merit->setTimestamp($meritTimestamp);
                    $meritResult[] = $merit;
                }
                $stmt->close();
                if(\count($meritResult) > 0) {
                    return $meritResult;
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
        if($stmt = Database::getConnection()->prepare("SELECT `MeritID` FROM `Merits` WHERE `MeritID`=?")) {
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->bind_result($meritID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $meritID;
            }
            $stmt->close();
            if(\count($input)) {
                return Merit::get($input);
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
        if($stmt = Database::getConnection()->prepare("SELECT `MeritID` FROM `Merits` WHERE `AdminID`=?")) {
            $stmt->bind_param('i', $adminID);
            $stmt->execute();
            $stmt->bind_result($meritID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $meritID;
            }
            $stmt->close();
            if(\count($input)) {
                return Merit::get($input);
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