<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidGroupException;
use AMC\Exceptions\InvalidIntelligenceException;
use AMC\Exceptions\QueryStatementException;

class IntelligenceGroupView implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_groupID = null;
    private $_intelligenceID = null;
    private $_connection = null;

    public function __construct($id = null, $groupID = null, $intelligenceID = null) {
        $this->_id = $id;
        $this->_groupID = $groupID;
        $this->_intelligenceID = $intelligenceID;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new IntelligenceGroupView())) {
            throw new BlankObjectException('Cannot store a blank Intelligence Group View.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Intelligence_Group_Views`(`GroupID`,`IntelligenceID`) VALUES (?,?)")) {
                $stmt->bind_param('ii', $this->_groupID, $this->_intelligenceID);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new IntelligenceGroupView())) {
            throw new BlankObjectException('Cannot store a blank Intelligence Group View.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Intelligence_Group_Views` SET `GroupID`=?,`IntelligenceID`=? WHERE `IntelligenceGroupViewID`=?")) {
                $stmt->bind_param('iii', $this->_groupID, $this->_intelligenceID, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Intelligence_Group_Views` WHERE `IntelligenceGroupViewID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_groupID == $anotherObject->getGroupID() && $this->_intelligenceID == $anotherObject->getIntelligenceID()) {
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

    // Setters and getters

    public function getID() {
        return $this->_id;
    }

    public function getGroupID() {
        return $this->_groupID;
    }

    public function getIntelligenceID() {
        return $this->_intelligenceID;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setGroupID($groupID, $verify = false) {
        if($verify) {
            if(Group::groupExists($groupID)) {
                $this->_groupID = $groupID;
            }
            else {
                throw new InvalidGroupException('No group exists with id ' . $groupID);
            }
        }
        else {
            $this->_groupID = $groupID;
        }
    }

    public function setIntelligenceID($intelligenceID, $verify = false) {
        if($verify) {
            if(Intelligence::intelligenceExists($intelligenceID)) {
                $this->_intelligenceID = $intelligenceID;
            }
            else {
                throw new InvalidIntelligenceException('No intelligence exists with id ' . $intelligenceID);
            }
        }
        else {
            $this->_intelligenceID = $intelligenceID;
        }
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $intelligenceGroupViewResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceGroupViewID`,`GroupID`,`IntelligenceID` FROM `Intelligence_Group_Views` WHERE `IntelligencGroupViewID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($intelligenceGroupViewID, $groupID, $intelligenceID);
                while($stmt->fetch()) {
                    $intelligenceGroupView = new IntelligenceGroupView();
                    $intelligenceGroupView->setID($intelligenceGroupViewID);
                    $intelligenceGroupView->setGroupID($groupID);
                    $intelligenceGroupView->setIntelligenceID($intelligenceID);
                    $intelligenceGroupViewResult[] = $intelligenceGroupView;
                }
                $stmt->close();
                if(\count($intelligenceGroupViewResult) > 0) {
                    return $intelligenceGroupViewResult;
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
            $intelligenceGroupViewResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceGroupViewID`,`GroupID`,`IntelligenceID` FROM `Intelligence_Group_Views`")) {
                $stmt->execute();
                $stmt->bind_result($intelligenceGroupViewID, $groupID, $intelligenceID);
                while($stmt->fetch()) {
                    $intelligenceGroupView = new IntelligenceGroupView();
                    $intelligenceGroupView->setID($intelligenceGroupViewID);
                    $intelligenceGroupView->setGroupID($groupID);
                    $intelligenceGroupView->setIntelligenceID($intelligenceID);
                    $intelligenceGroupViewResult[] = $intelligenceGroupView;
                }
                $stmt->close();
                if(\count($intelligenceGroupViewResult) > 0) {
                    return $intelligenceGroupViewResult;
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
        if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceGroupViewID` FROM `Intelligence_Group_Views` WHERE `GroupID`=?")) {
            $stmt->bind_param('i', $groupID);
            $stmt->execute();
            $stmt->bind_result($intelligenceGroupViewID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $intelligenceGroupViewID;
            }
            if(\count($input) > 0) {
                return IntelligenceGroupView::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function getByIntelligenceID($intelligenceID) {
        if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceGroupViewID` FROM `Intelligence_Group_Views` WHERE `IntelligenceID`=?")) {
            $stmt->bind_param('i', $intelligenceID);
            $stmt->execute();
            $stmt->bind_result($intelligenceGroupViewID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $intelligenceGroupViewID;
            }
            if(\count($input) > 0) {
                return IntelligenceGroupView::get($input);
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