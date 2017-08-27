<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class MissionGroupView implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_groupID = null;
    private $_missionID = null;
    private $_connection = null;

    public function __construct($id = null, $groupID = null, $missionID = null) {
        $this->_id = $id;
        $this->_groupID = $groupID;
        $this->_missionID = $missionID;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new MissionGroupView())) {
            throw new BlankObjectException('Cannot store a blank Mission Group View.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Mission_Group_Views` (`GroupID`,`MissionID`) VALUES (?,?)")) {
                $stmt->bind_param('ii', $this->_groupID, $this->_missionID);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new MissionGroupView())) {
            throw new BlankObjectException('Cannot store a blank Mission Group View.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Mission_Group_Views` SET `GroupID`=?,`MissionID`=? WHERE `MissionGroupViewID`=?")) {
                $stmt->bind_param('iii', $this->_groupID, $this->_missionID, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Mission_Group_Views` WHERE `MissionGroupViewID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_groupID == $anotherObject->getGroupID() && $this->_missionID == $anotherObject->getMissionID()) {
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

    public function getGroupID() {
        return $this->_groupID;
    }

    public function getMissionID() {
        return $this->_missionID;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setGroupID($groupID) {
        $this->_groupID = $groupID;
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
            if($stmt = Database::getConnection()->prepare("SELECT `MissionGroupViewID`,`GroupID`,`MissionID` FROM `Mission_Group_Views` WHERE `MissionGroupViewID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($missionGroupViewID, $groupID, $missionID);
                while($stmt->fetch()) {
                    $missionGroupView = new MissionGroupView();
                    $missionGroupView->setID($missionGroupViewID);
                    $missionGroupView->setGroupID($groupID);
                    $missionGroupView->setMissionID($missionID);
                    $missionViewResult[] = $missionGroupView;
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
            if($stmt = Database::getConnection()->prepare("SELECT `MissionGroupViewID`,`GroupID`,`MissionID` FROM `Mission_Group_Views`")) {
                $stmt->execute();
                $stmt->bind_result($missionGroupViewID, $groupID, $missionID);
                while($stmt->fetch()) {
                    $missionGroupView = new MissionGroupView();
                    $missionGroupView->setID($missionGroupViewID);
                    $missionGroupView->setGroupID($groupID);
                    $missionGroupView->setMissionID($missionID);
                    $missionViewResult[] = $missionGroupView;
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

    public static function getByGroupID($groupID) {
        if($stmt = Database::getConnection()->prepare("SELECT `MissionGroupViewID` FROM `Mission_Group_Views` WHERE `GroupID`=?")) {
            $stmt->bind_param('i', $groupID);
            $stmt->execute();
            $stmt->bind_result($missionViewID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $input;
            }
            if(\count($input) > 0) {
                return MissionGroupView::get($input);
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
        if($stmt = Database::getConnection()->prepare("SELECT `MissionGroupViewID` FROM `Mission_Group_Views` WHERE `MissionID`=?")) {
            $stmt->bind_param('i', $missionID);
            $stmt->execute();
            $stmt->bind_result($missionViewID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $input;
            }
            if(\count($input) > 0) {
                return MissionGroupView::get($input);
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