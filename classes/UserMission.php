<?php
// TODO: Add role
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidMissionException;
use AMC\Exceptions\InvalidUserException;
use AMC\Exceptions\QueryStatementException;

class UserMission implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_userID = null;
    private $_missionID = null;
    private $_role = null;
    private $_connection = null;

    public function __construct($id = null, $userID = null, $missionID = null, $role = null) {
        $this->_id = $id;
        $this->_userID = $userID;
        $this->_missionID = $missionID;
        $this->_role = $role;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new UserMission())) {
            throw new BlankObjectException('Cannot store a blank User Mission.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `User_Missions` (`UserID`,`MissionID`,`Role`) VALUES (?,?,?)")) {
                $stmt->bind_param('iis', $this->_userID, $this->_missionID, $this->_role);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new UserMission())) {
            throw new BlankObjectException('Cannot store a blank User Mission.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `User_Missions` SET `UserID`=?,`MissionID`=?,`Role`=? WHERE `UserMissionID`=?")) {
                $stmt->bind_param('iisi', $this->_userID, $this->_missionID, $this->_role, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `User_Missions` WHERE `UserMissionID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_userID == $anotherObject->getUserID() && $this->_missionID == $anotherObject->getMissionID() && $this->_role == $anotherObject->getRole()) {
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

    public function getRole() {
        return $this->_role;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setRole($role) {
        $this->_role = $role;
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

    public function setMissionID($missionID, $verify = false) {
        if($verify) {
            if(Mission::missionExists($missionID)) {
                $this->_missionID = $missionID;
            }
            else {
                throw new InvalidMissionException('No mission exists with id ' . $missionID);

            }
        }
        else {
            $this->_missionID = $missionID;
        }
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $userMissionResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `UserMissionID`,`UserID`,`MissionID`,`Role` FROM `User_Missions` WHERE `UserMissionID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($userMissionID, $userID, $missionID, $role);
                while($stmt->fetch()) {
                    $userMission = new UserMission();
                    $userMission->setID($userMissionID);
                    $userMission->setUserID($userID);
                    $userMission->setMissionID($missionID);
                    $userMission->setRole($role);
                    $userMissionResult[] = $userMission;
                }
                $stmt->close();
                if(\count($userMissionResult) > 0) {
                    return $userMissionResult;
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
            $userMissionResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `UserMissionID`,`UserID`,`MissionID`,`Role` FROM `User_Missions`")) {
                $stmt->execute();
                $stmt->bind_result($userMissionID, $userID, $missionID, $role);
                while($stmt->fetch()) {
                    $userMission = new UserMission();
                    $userMission->setID($userMissionID);
                    $userMission->setUserID($userID);
                    $userMission->setMissionID($missionID);
                    $userMission->setRole($role);
                    $userMissionResult[] = $userMission;
                }
                $stmt->close();
                if(\count($userMissionResult) > 0) {
                    return $userMissionResult;
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
        if($stmt = Database::getConnection()->prepare("SELECT `UserMissionID` FROM `User_Missions` WHERE `UserID`=?")){
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->bind_results($userMissionID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $userMissionID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return UserMission::get($input);
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
        if($stmt = Database::getConnection()->prepare("SELECT `UserMissionID` FROM `User_Missions` WHERE `MissionID`=?")){
            $stmt->bind_param('i', $missionID);
            $stmt->execute();
            $stmt->bind_results($userMissionID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $userMissionID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return UserMission::get($input);
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