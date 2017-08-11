<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class UserAward implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_userID = null;
    private $_issuerID = null;
    private $_awardID = null;
    private $_timestamp = null;
    private $_connection = null;

    public function __construct($id = null, $userID = null, $issuerID = null, $awardID = null, $timestamp = null) {
        $this->_id = $id;
        $this->_userID = $userID;
        $this->_issuerID = $issuerID;
        $this->_awardID = $awardID;
        $this->_timestamp = $timestamp;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new UserAward())) {
            throw new BlankObjectException("Cannot store blank user award");
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `User_Awards`(`UserID`,`IssuerID`,`AwardID`,`UserAwardTimestamp`) VALUES (?,?,?,?)")) {
                $stmt->bind_param('iiii', $this->_userID, $this->_issuerID, $this->_awardID, $this->_timestamp);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function update() {
        if($this->eql(new UserAward())) {
            throw new BlankObjectException("Cannot store a blank user object");
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `User_Awards` SET `UserID`=?,`IssuerID`=?,`AwardID`=?,`UserAwardTimestamp`=? WHERE `UserAwardID`=?")) {
                $stmt->bind_param('iiiii', $this->_userID, $this->_issuerID, $this->_awardID, $this->_timestamp, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `User_Awards` WHERE `UserAwardID`=?")) {
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
        if(\get_class($this) == \get_class($anotherObject)) {
            if($this->_id == $anotherObject->getID() && $this->_userID == $anotherObject->getUserID() && $this->_issuerID == $anotherObject->getIssuerID() && $this->_awardID == $anotherObject->getAwardID() && $this->_timestamp == $anotherObject->getTimestamp()) {
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

    public function getIssuerID() {
        return $this->_issuerID;
    }

    public function getAwardID() {
        return $this->_awardID;
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

    public function setIssuerID($issuerID) {
        $this->_issuerID = $issuerID;
    }

    public function setAwardID($awardID) {
        $this->_awardID = $awardID;
    }

    public function setTimestamp($timestamp) {
        $this->_timestamp = $timestamp;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $userAwardResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `UserAwardID`,`UserID`,`IssuerID`,`AwardID`,`UserAwardTimestamp` FROM `User_Awards` WHERE `UserAwardID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($userAwardID, $userID, $issuerID, $awardID, $timestamp);
                while($stmt->fetch()) {
                    $userAward = new UserAward();
                    $userAward->setID($userAwardID);
                    $userAward->setUserID($userID);
                    $userAward->setIssuerID($issuerID);
                    $userAward->setAwardID($awardID);
                    $userAward->setTimestamp($timestamp);
                    $userAwardResult[] = $userAward;
                }
                $stmt->close();
                if(\count($userAwardResult) > 0) {
                    return $userAwardResult;
                }
                else {
                    return null;
                }
            }
            else {
                throw new QueryStatementException("Failed tp bind query");
            }
        }
        else if(\is_array($id) && \count($id) == 0) {
            $userAwardResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `UserAwardID`,`UserID`,`IssuerID`,`AwardID`,`UserAwardTimestamp` FROM `User_Awards`")) {
                $stmt->execute();
                $stmt->bind_result($userAwardID, $userID, $issuerID, $awardID, $timestamp);
                while($stmt->fetch()) {
                    $userAward = new UserAward();
                    $userAward->setID($userAwardID);
                    $userAward->setUserID($userID);
                    $userAward->setIssuerID($issuerID);
                    $userAward->setAwardID($awardID);
                    $userAward->setTimestamp($timestamp);
                    $userAwardResult[] = $userAward;
                }
                $stmt->close();
                if(\count($userAwardResult) > 0) {
                    return $userAwardResult;
                }
                else {
                    return null;
                }
            }
            else {
                throw new QueryStatementException("Failed tp bind query");
            }
        }
        else {
            return null;
        }
    }

    public static function getByUserID($userID) {
        if($stmt = Database::getConnection()->prepare("SELECT `UserAwardID` FROM `User_Awards` WHERE `UserID`=?")) {
            $stmt->bind_param('i', $userID);
            $input = [];
            $stmt->execute();
            $stmt->bind_result($userAwardID);
            while($stmt->fetch()) {
                $input[] = $userAwardID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return UserAward::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException("Failed to bind query");
        }
    }

    public static function getByIssuerID($issuerID) {
        if($stmt = Database::getConnection()->prepare("SELECT `UserAwardID` FROM `User_Awards` WHERE `IssuerID`=?")) {
            $stmt->bind_param('i', $issuerID);
            $input = [];
            $stmt->execute();
            $stmt->bind_result($userAwardID);
            while($stmt->fetch()) {
                $input[] = $userAwardID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return UserAward::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException("Failed to bind query");
        }
    }

    public static function getByAwardID($awardID) {
        if($stmt = Database::getConnection()->prepare("SELECT `UserAwardID` FROM `User_Awards` WHERE `AwardID`=?")) {
            $stmt->bind_param('i', $awardID);
            $input = [];
            $stmt->execute();
            $stmt->bind_result($userAwardID);
            while($stmt->fetch()) {
                $input[] = $userAwardID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return UserAward::get($input);
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