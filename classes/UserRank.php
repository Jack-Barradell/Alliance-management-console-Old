<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class UserRank implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_userID = null;
    private $_rankID = null;
    private $_connection =  null;

    public function __construct($id = null, $userID = null, $rankID = null) {
        $this->_id = null;
        $this->_userID = null;
        $this->_rankID = null;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new UserRank())) {
            throw new BlankObjectException("Cannot store a blank user rank");
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `User_Ranks` (`UserID`,`RankID`) VALUES (?,?)")) {
                $stmt->bind_param('ii', $this->_userID, $this->_rankID);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function update() {
        if($this->eql(new UserRank())) {
            throw new BlankObjectException("Cannot store a blank user rank");
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `User_Ranks` SET `UserID`=?,`RankID`=? WHERE `UserRankID`=?")) {
                $stmt->bind_param('iii', $this->_userID, $this->_rankID, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `User_Ranks` WHERE `UserRankID`=?")) {
            $stmt->bind_param('i', $this->_id);
            $stmt->execute();
            $stmt->close();
        }
        else {
            throw new QueryStatementException("Failed to bind query");
        }
    }

    public function eql($anotherObject) {
        if(\get_class($this) && \get_class($anotherObject)) {
            if($this->_id == $anotherObject->getID() && $this->_userID == $anotherObject->getUserID() && $this->_rankID == $anotherObject->getRankID()) {
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

    public function getRankID() {
        return $this->_rankID;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setUserID($userID) {
        $this->_userID = $userID;
    }

    public function setRankID($rankID) {
        $this->_rankID = $rankID;
    }

    // Statics

    public static function select($id) {
        if (\is_array($id) && \count($id) > 0) {
            $userRankResult = [];
            $typeArray = [];
            $refs = [];
            $typeArray[0] = 'i';
            $questionString = '?';
            foreach ($id as $key => $value) {
                $refs[$key] =& $id[$key];
            }
            for ($i = 0; $i < \count($id); $i++) {
                $questionString .= ',?';
                $typeArray[0] .= 'i';
            }
            $param = \array_merge($typeArray, $refs);
            if ($stmt = Database::getConnection()->prepare("SELECT `UserRankID`,`UserID`,`RankID` FROM `User_Ranks` WHERE `UserRankID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($userRankID, $userID, $rankID);
                while ($stmt->fetch()) {
                    $userRank = new UserRank();
                    $userRank->setID($userRankID);
                    $userRank->setUserID($userID);
                    $userRank->setRankID($rankID);
                    $userRankResult[] = $userRank;
                }
                $stmt->close();
                if (\count($userRankResult) > 0) {
                    return $userRankResult;
                }
                else {
                    return null;
                }
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
        else if (\is_array($id) && \count($id) == 0) {
            if ($stmt = Database::getConnection()->prepare("SELECT `UserRankID`,`UserID`,`RankID` FROM `User_Ranks`")) {
                $stmt->execute();
                $stmt->bind_result($userRankID, $userID, $rankID);
                while ($stmt->fetch()) {
                    $userRank = new UserRank();
                    $userRank->setID($userRankID);
                    $userRank->setUserID($userID);
                    $userRank->setRankID($rankID);
                    $userRankResult[] = $userRank;
                }
                $stmt->close();
                if (\count($userRankResult) > 0) {
                    return $userRankResult;
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