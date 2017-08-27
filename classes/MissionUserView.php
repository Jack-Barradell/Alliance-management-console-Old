<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class MissionUserView implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_userID = null;
    private $_missionID = null;
    private $_connection = null;

    public function __construct($id = null, $userID = null, $missionID = null) {
        $this->_id = $id;
        $this->_userID = $userID;
        $this->_missionID = $missionID;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new MissionUserView())) {
            throw new BlankObjectException('Cannot store a blank Mission User View.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Mission_User_Views` (`UserID`,`MissionID`) VALUES (?,?)")) {
                $stmt->bind_param('ii', $this->_userID, $this->_missionID);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new MissionUserView())) {
            throw new BlankObjectException('Cannot store a blank Mission User View.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Mission_User_Views` SET `UserID`=?,`MissionID`=? WHERE `MissionUserViewID`=?")) {
                $stmt->bind_param('iii', $this->_userID, $this->_missionID, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Mission_User_Views` WHERE `MissionUserViewID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_userID == $anotherObject->getUserID() && $this->_missionID == $anotherObject->getMissionID()) {
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

    public function getMissionID() {
        return $this->_missionID;
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

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $missionViewResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `MissionUserViewID`,`UserID`,`MissionID` FROM `Mission_User_Views` WHERE `MissionUserViewID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($missionUserViewID, $userID, $missionID);
                while($stmt->fetch()) {
                    $missionUserView = new MissionUserView();
                    $missionUserView->setID($missionUserViewID);
                    $missionUserView->setUserID($userID);
                    $missionUserView->setMissionID($missionID);
                    $missionViewResult[] = $missionUserView;
                }
                $stmt->close();
                if(\count($missionViewResult) > 0) {
                    return $missionViewResult;
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
            $missionViewResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `MissionUserViewID`,`UserID`,`MissionID` FROM `Mission_User_Views`")) {
                $stmt->execute();
                $stmt->bind_result($missionUserViewID, $userID, $missionID);
                while($stmt->fetch()) {
                    $missionUserView = new MissionUserView();
                    $missionUserView->setID($missionUserViewID);
                    $missionUserView->setUserID($userID);
                    $missionUserView->setMissionID($missionID);
                    $missionViewResult[] = $missionUserView;
                }
                $stmt->close();
                if(\count($missionViewResult) > 0) {
                    return $missionViewResult;
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
        if($stmt = Database::getConnection()->prepare("SELECT `MissionUserViewID` FROM `Mission_User_Views` WHERE `UserID`=?")) {
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->bind_result($missionViewID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $input;
            }
            if(\count($input) > 0) {
                return MissionUserView::get($input);
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
        if($stmt = Database::getConnection()->prepare("SELECT `MissionUserViewID` FROM `Mission_User_Views` WHERE `MissionID`=?")) {
            $stmt->bind_param('i', $missionID);
            $stmt->execute();
            $stmt->bind_result($missionViewID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $input;
            }
            if(\count($input) > 0) {
                return MissionUserView::get($input);
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