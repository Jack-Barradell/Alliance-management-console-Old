<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class IntelligenceUserView implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_userID = null;
    private $_intelligenceID = null;
    private $_connection = null;

    public function __construct($id = null, $userID = null, $intelligenceID = null) {
        $this->_id = $id;
        $this->_userID = $userID;
        $this->_intelligenceID = $intelligenceID;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new IntelligenceUserView())) {
            throw new BlankObjectException('Cannot store a blank Intelligence User View.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Intelligence_User_Views`(`UserID`,`IntelligenceID`) VALUES (?,?)")) {
                $stmt->bind_param('ii', $this->_userID, $this->_intelligenceID);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new IntelligenceUserView())) {
            throw new BlankObjectException('Cannot store a blank Intelligence User View.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Intelligence_User_Views` SET `UserID`=?,`IntelligenceID`=? WHERE `IntelligenceUserViewID`=?")) {
                $stmt->bind_param('iii', $this->_userID, $this->_intelligenceID, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Intelligence_User_Views` WHERE `IntelligenceUserViewID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_userID == $anotherObject->getUserID() && $this->_intelligenceID == $anotherObject->getIntelligenceID()) {
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

    public function getUserID() {
        return $this->_userID;
    }

    public function getIntelligenceID() {
        return $this->_intelligenceID;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setUserID($userID) {
        $this->_userID = $userID;
    }

    public function setIntelligenceID($intelligenceID) {
        $this->_intelligenceID = $intelligenceID;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $intelligenceUserViewResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceUserViewID`,`UserID`,`IntelligenceID` FROM `Intelligence_User_Views` WHERE `IntelligenceUserViewID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($intelligenceUserViewID, $userID, $intelligenceID);
                while($stmt->fetch()) {
                    $intelligenceUserView = new IntelligenceUserView();
                    $intelligenceUserView->setID($intelligenceUserViewID);
                    $intelligenceUserView->setUserID($userID);
                    $intelligenceUserView->setIntelligenceID($intelligenceID);
                    $intelligenceUserViewResult[] = $intelligenceUserView;
                }
                $stmt->close();
                if(\count($intelligenceUserViewResult) > 0) {
                    return $intelligenceUserViewResult;
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
            $intelligenceUserViewResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceUserViewID`,`UserID`,`IntelligenceID` FROM `Intelligence_User_Views`")) {
                $stmt->execute();
                $stmt->bind_result($intelligenceUserViewID, $userID, $intelligenceID);
                while($stmt->fetch()) {
                    $intelligenceUserView = new IntelligenceUserView();
                    $intelligenceUserView->setID($intelligenceUserViewID);
                    $intelligenceUserView->setUserID($userID);
                    $intelligenceUserView->setIntelligenceID($intelligenceID);
                    $intelligenceUserViewResult[] = $intelligenceUserView;
                }
                $stmt->close();
                if(\count($intelligenceUserViewResult) > 0) {
                    return $intelligenceUserViewResult;
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
        if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceUserViewID` FROM `Intelligence_User_Views` WHERE `UserID`=?")) {
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->bind_result($intelligenceUserViewID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $intelligenceUserViewID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return IntelligenceUserView::get($input);
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
        if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceUserViewID` FROM `Intelligence_User_Views` WHERE `IntelligenceID`=?")) {
            $stmt->bind_param('i', $intelligenceID);
            $stmt->execute();
            $stmt->bind_result($intelligenceUserViewID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $intelligenceUserViewID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return IntelligenceUserView::get($input);
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