<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class Award implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_name = null;
    private $_description = null;
    private $_badge = null;
    private $_connection = null;

    public function __construct($id = null, $name = null, $description = null, $badge = null) {
        $this->_id = $id;
        $this->_name = $name;
        $this->_description = $description;
        $this->_badge = $badge;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new Award())) {
            throw new BlankObjectException("Cannot store a blank award");
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Awards`(`AwardName`,`AwardDescription`,`AwardBadge`) VALUES (?,?,?)")) {
                $stmt->bind_param('sss', $this->_name, $this->_description, $this->_badge);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function update() {
        if($this->eql(new Award())) {
            throw new BlankObjectException("Cannot store a blank object");
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Awards` SET `AwardName`=?,`AwardDescription`=?,`AwardBadge`=? WHERE `AwardID`=?")) {
                $stmt->bind_param('sssi', $this->_name, $this->_description, $this->_badge, $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Awards` WHERE `AwardID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_name == $anotherObject->getName() && $this->_description == $anotherObject->getDescription() && $this->_badge == $anotherObject->getBadge()) {
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

    public function getName() {
        return $this->_name;
    }

    public function getDescription() {
        return $this->_description;
    }

    public function getBadge() {
        return $this->_badge;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setName($name) {
        $this->_name = $name;
    }

    public function setDescription($description) {
        $this->_description = $description;
    }

    public function setBadge($badge) {
        $this->_badge = $badge;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $awardResult = [];
            $typeArray = [];
            $refs = [];
            $typeArray[0] = 'i';
            $questionString = '?';
            foreach($id as $key => $value) {
                $refs[$key] =& $id[$key];
            }
            for($i = 0; $i < \count($id); $i++) {
                $typeArray[0] .= 'i';
                $questionString .= ',?';
            }
            $param = \array_merge($typeArray, $refs);
            if($stmt = Database::getConnection()->prepare("SELECT `AwardID`,`AwardName`,`AwardDescription`,`AwardBadge` FROM `Awards` WHERE `AwardID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($awardID, $name, $description, $badge);
                while($stmt->fetch()) {
                    $award = new Award();
                    $award->setID($awardID);
                    $award->setName($name);
                    $award->setDescription($description);
                    $award->setBadge($badge);
                    $awardResult[] = $award;
                }
                $stmt->close();
                if(\count($awardResult) > 0) {
                    return $awardResult;
                }
                else {
                    return null;
                }
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
        else if(\is_array($id) && \count($id) == 0) {
            $awardResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `AwardID`,`AwardName`,`AwardDescription`,`AwardBadge` FROM `Awards`")) {
                $stmt->execute();
                $stmt->bind_result($awardID, $name, $description, $badge);
                while($stmt->fetch()) {
                    $award = new Award();
                    $award->setID($awardID);
                    $award->setName($name);
                    $award->setDescription($description);
                    $award->setBadge($badge);
                    $awardResult[] = $award;
                }
                $stmt->close();
                if(\count($awardResult) > 0) {
                    return $awardResult;
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