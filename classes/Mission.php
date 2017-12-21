<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\DuplicateEntryException;
use AMC\Exceptions\IncorrectTypeException;
use AMC\Exceptions\InvalidGroupException;
use AMC\Exceptions\InvalidUserException;
use AMC\Exceptions\MissingPrerequisiteException;
use AMC\Exceptions\QueryStatementException;

class Mission implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_title = null;
    private $_description = null;
    private $_status = null;
    private $_payment = null;
    private $_connection = null;

    // Mission status constants
    const MISSION_UNASSIGNED = 0;
    const MISSION_ASSIGNED = 1;
    const MISSION_ABORTED = 2;
    const MISSION_IN_PROGRESS = 3;
    const MISSION_FAILED = 4;
    const MISSION_COMPLETED_UNPAID = 5;
    const MISSION_COMPLETED_PAID = 6;
    const MISSION_AWAITING_UPDATE = 7;
    const MISSION_UNDER_REVIEW = 8;



    public function __construct($id = null, $title = null, $description = null, $status = null, $payment = null) {
        $this->_id = $id;
        $this->_title = $title;
        $this->_description = $description;
        $this->_status = $status;
        $this->_payment = $payment;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new Mission())) {
            throw new BlankObjectException('Cannot store a blank Mission.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Missions` (`MissionTitle`,`MissionDescription`,`MissionStatus`,`MissionPayment`) VALUES (?,?,?,?)")) {
                $stmt->bind_param('ssii', $this->_title, $this->_description, $this->_status, $this->_payment);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new Mission())) {
            throw new BlankObjectException('Cannot store a blank Mission.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Missions` SET `MissionTitle`=?,`MissionDescription`=?,`MissionStatus`=?,`MissionPayment`=? WHERE `MissionID`=?")) {
                $stmt->bind_param('ssiii', $this->_title, $this->_description, $this->_status, $this->_payment, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Missions` WHERE `MissionID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_title == $anotherObject->getTitle() && $this->_description == $anotherObject->getDescription() && $this->_status == $anotherObject->getStatus() && $this->_payment == $anotherObject->getPayment()) {
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

    public function issueToUser($userID, $role = null) {
        if($this->userIsAssigned($userID)) {
            throw new DuplicateEntryException('Attempted to assign user with id ' . $userID . ' to mission mission with id ' . $this->_id . ' but they were already issued.');
        }
        else if(User::userExists($userID)) {
            $userMission = new UserMission();
            $userMission->setUserID($userID);
            $userMission->setMissionID($this->_id);
            $userMission->setRole($role);
            $userMission->commit();
        }
        else {
            throw new InvalidUserException('There is no user with user id ' . $userID);
        }
    }

    public function removeFromUser($userID) {
        if($this->userIsAssigned($userID)) {
            $userMissions = UserMission::getByUserID($userID);
            foreach($userMissions as $userMission) {
                if($userMission->getMissionID() == $this->_id) {
                    $userMission->toggleDelete();
                    $userMission->commit();
                }
            }
        }
        else {
            throw new MissingPrerequisiteException('Tried to unassign user with id ' . $userID . ' from mission with id ' . $this->_id . ' but they were not assigned.');
        }
    }

    public function userIsAssigned($userID) {
        $userMissions = UserMission::getByUserID($userID);
        if(\is_null($userMissions)) {
            return false;
        }
        else {
            foreach($userMissions as $userMission) {
                if($userMission->getMissionID() == $this->_id) {
                    return true;
                }
            }
            return false;
        }
    }

    public function showToUser($userID, $role = null) {
        if($this->userCanSee($userID)) {
            throw new DuplicateEntryException('User with id ' . $userID . ' was given access to mission with id ' . $this->_id . ' when they already had it.');
        }
        else if(InvalidUserException::class) {
            $missionUserView = new MissionUserView();
            $missionUserView->setUserID($userID);
            $missionUserView->setMissionID($this->_id);
            $missionUserView->setRole($role);
            $missionUserView->commit();
        }
        else {
            throw new InvalidUserException('There is no user with user id ' . $userID);
        }
    }

    public function hideFromUser($userID) {
        if($this->userCanSee($userID)) {
            $missionUserViews = MissionUserView::getByUserID($userID);
            foreach($missionUserViews as $missionUserView) {
                if($missionUserView->getMissionID() == $this->_id) {
                    $missionUserView->toggleDelete();
                    $missionUserView->commit();
                }
            }
        }
        else {
            throw new MissingPrerequisiteException('User with id ' . $userID . ' was removed from the access list of mission with id ' . $this->_id . ' but they did not have access.');
        }
    }

    public function userCanSee($userID) {
        if($this->userIsAssigned($userID)) {
            return true;
        }
        $missionUserViews = MissionUserView::getByUserID($userID);
        if(\is_null($missionUserViews)) {
            return false;
        }
        else {
            foreach($missionUserViews as $missionUserView) {
                if($missionUserView->getMissionID() == $this->_id) {
                    return true;
                }
            }
            return false;
        }
    }

    public function showToGroup($groupID, $role = null) {
        if($this->groupCanSee($groupID)) {
            throw new DuplicateEntryException('Group with id ' . $groupID . ' was given access to mission with id ' . $this->_id . ' but they are already had it.');
        }
        else if(InvalidGroupException::class) {
            $missionGroupView = new MissionGroupView();
            $missionGroupView->setGroupID($groupID);
            $missionGroupView->setMissionID($this->_id);
            $missionGroupView->setRole($role);
            $missionGroupView->commit();
        }
        else {
            throw new InvalidGroupException('There is no group with group id ' . $groupID);
        }
    }

    public function hideFromGroup($groupID) {
        if($this->groupCanSee($groupID)) {
            $missionGroupViews = MissionGroupView::getByGroupID($groupID);
            foreach($missionGroupViews as $missionGroupView) {
                if($missionGroupView->getMissionID() == $this->_id) {
                    $missionGroupView->toggleDelete();
                    $missionGroupView->commit();
                }
            }
        }
        else {
            throw new MissingPrerequisiteException('Group with id ' . $groupID . ' was removed from the access list of mission with id ' . $this->_id . ' but they did not have access.');
        }
    }

    public function groupCanSee($groupID) {
        $missionGroupViews = MissionGroupView::getByGroupID($groupID);
        if(\is_null($missionGroupViews)) {
            return false;
        }
        else {
            foreach ($missionGroupViews as $missionGroupView) {
                if($missionGroupView->getMissionID() == $this->_id) {
                    return true;
                }
            }
            return false;
        }
    }

    public function userHasAccess($userID) {
        if($this->userCanSee($userID)) {
            return true;
        }
        else {
            $user = User::get($userID);
            $groups = $user->getGroups();
            foreach($groups as $group) {
                if($this->groupCanSee($group->getID())) {
                    return true;
                }
            }
            return false;
        }
    }

    // Getters and setters

    public function getID() {
        return $this->_id;
    }

    public function getTitle() {
        return $this->_title;
    }

    public function getDescription() {
        return $this->_description;
    }

    public function getStatus() {
        return $this->_status;
    }

    public function getPayment() {
        return $this->_payment;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setTitle($title) {
        $this->_title = $title;
    }

    public function setDescription($description) {
        $this->_description = $description;
    }

    public function setStatus($status) {
        $this->_status = $status;
    }

    public function setPayment($newPayment) {
        $this->_payment = $newPayment;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $missionResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `MissionID`,`MissionTitle`,`MissionDescription`,`MissionStatus`,`MissionPayment` FROM `Missions` WHERE `MissionID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($missionID, $title, $description, $status, $payment);
                while($stmt->fetch()) {
                    $mission = new Mission();
                    $mission->setID($missionID);
                    $mission->setTitle($title);
                    $mission->setDescription($description);
                    $mission->setStatus($status);
                    $mission->setPayment($payment);
                    $missionResult[] = $mission;
                }
                $stmt->close();
                if(\count($missionResult) > 0) {
                    return $missionResult;
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
            $missionResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `MissionID`,`MissionTitle`,`MissionDescription`,`MissionStatus`,`MissionPayment` FROM `Missions`")) {
                $stmt->execute();
                $stmt->bind_result($missionID, $title, $description, $status, $payment);
                while($stmt->fetch()) {
                    $mission = new Mission();
                    $mission->setID($missionID);
                    $mission->setTitle($title);
                    $mission->setDescription($description);
                    $mission->setStatus($status);
                    $mission->setPayment($payment);
                    $missionResult[] = $mission;
                }
                $stmt->close();
                if(\count($missionResult) > 0) {
                    return $missionResult;
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

    public static function getByStatus($status) {
        if($stmt = Database::getConnection()->prepare("SELECT `MissionID` FROM `Missions` WHERE `Status`=?")) {
            $stmt->bind_param('s', $status);
            $stmt->execute();
            $stmt->bind_result($missionID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $missionID;
            }
            if(\count($input) > 0) {
                return Mission::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function missionExists($missionID) {
        if(\is_numeric($missionID)) {
            if($stmt = Database::getConnection()->prepare("SELECT `MissionID` FROM `Missions` WHERE `MissionID`=?")) {
                $stmt->bind_params('i', $missionID);
                $stmt->execute();
                if($stmt->num_rows == 1) {
                    $stmt->close();
                    return true;
                }
                else {
                    $stmt->close();
                    return false;
                }
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
        else {
            throw new IncorrectTypeException('Mission exists must be passed an int, was given a ' . \gettype($missionID));
        }
    }

}