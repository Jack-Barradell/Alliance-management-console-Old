<?php
namespace AMC\Classes;

require '../classes/DataObject.php';
require '../classes/Getable.php';
require '../classes/Storable.php';
require '../classes/Database.php';
require '../classes/Intelligence.php';
require '../classes/User.php';
require '../classes/exceptions/BlankObjectException.php';

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class Intelligence implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_authorID = null;
    private $_subject = null;
    private $_body = null;
    private $_timestamp = null;
    private $_connection = null;

    public function __construct($id = null, $authorID = null, $subject = null, $body = null, $timestamp = null) {
        $this->_id = $id;
        $this->_authorID = $authorID;
        $this->_subject = $subject;
        $this->_body = $body;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new Intelligence())) {
            if($stmt = $this->_connection->prepare("INSERT INTO `Intelligence`(`AuthorID`,`IntelligenceSubject`,`IntelligenceBody`,`IntelligenceTimestamp`) VALUES (?,?,?,?)")) {
                $stmt->bind_param('issi', $this->_authorID, $this->_subject, $this->_body, $this->_timestamp);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
        else {
            throw new BlankObjectException('Cannot store blank Intelligence.');
        }
    }

    public function update() {
        if($this->eql(new Intelligence())) {
            if($stmt = $this->_connection->prepare("UPDATE `Intelligence` SET `AuthorID`=?,`IntelligenceSubject`=?,`IntelligenceBody`=?,`IntelligenceTimestamp`=? WHERE `IntelligenceID`=?")) {
                $stmt->bind_param('issii', $this->_authorID, $this->_subject, $this->_body, $this->_timestamp, $this->_id);
                $stmt->exeucte();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
        else {
            throw new BlankObjectException('Cannot store blank Intelligence.');
        }

    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Intelligence` WHERE `IntelligenceID`=?")) {
            $stmt->bind_param('i', $this->_id);
            $stmt->execute();
            $stmt->close();
            $this->_id = null;
        }
        else {
            throw new QueryStatementException('Failed to bind query');
        }
    }

    public function eql($anotherObject) {
        if(\get_class($this) == \get_class($anotherObject)) {
            if($this->_id == $anotherObject->getID() && $this->_authorID == $anotherObject->getAuthorID() && $this->_subject == $anotherObject->getSubject() && $this->_body == $anotherObject->getBody() && $this->_timestamp == $anotherObject->getTimestamp()) {
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

    public function getAuthorID() {
        return $this->_authorID;
    }

    public function getSubject() {
        return $this->_subject;
    }

    public function getBody() {
        return $this->_body;
    }

    public function getTimestamp() {
        return $this->_timestamp;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setAuthorID($authorID) {
        $this->_authorID = $authorID;
    }

    public function setSubject($subject) {
        $this->_subject = $subject;
    }

    public function setBody($body) {
        $this->_body = $body;
    }

    public function setTimestamp($timestamp) {
        $this->_timestamp = $timestamp;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $intelligenceResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceID`,`AuthorID`,`IntelligenceSubject`,`IntelligenceBody`,`IntelligenceTimestamp` FROM `Intelligence` WHERE `IntelligenceID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($intelligenceID, $authorID, $subject, $body, $timestamp);
                while($stmt->fetch()) {
                    $intelligence = new Intelligence();
                    $intelligence->setID($intelligenceID);
                    $intelligence->setAuthorID($authorID);
                    $intelligence->setSubject($subject);
                    $intelligence->setBody($body);
                    $intelligence->setTimestamp($timestamp);
                    $intelligenceResult[] = $intelligence;
                }
                $stmt->close();
                if(\count($intelligenceResult) > 0) {
                    return $intelligenceResult;
                }
                else {
                    return null;
                }
            }
            else {
                throw new QueryStatementException('Failed to bind query');
            }
        }
        else if(\is_array($id) && \count($id) == 0) {
            $intelligenceResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceID`,`AuthorID`,`IntelligenceSubject`,`IntelligenceBody`,`IntelligenceTimestamp` FROM `Intelligence`")) {
                $stmt->execute();
                $stmt->bind_result($intelligenceID, $authorID, $subject, $body, $timestamp);
                while($stmt->fetch()) {
                    $intelligence = new Intelligence();
                    $intelligence->setID($intelligenceID);
                    $intelligence->setAuthorID($authorID);
                    $intelligence->setSubject($subject);
                    $intelligence->setBody($body);
                    $intelligence->setTimestamp($timestamp);
                    $intelligenceResult[] = $intelligence;
                }
                $stmt->close();
                if(\count($intelligenceResult) > 0) {
                    return $intelligenceResult;
                }
                else {
                    return null;
                }
            }
            else {
                throw new QueryStatementException('Failed to bind query');
            }
        }
        else {
            return null;
        }
    }

    public static function getByAuthorID($authorID) {
        if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceID` FROM `Intelligence` WHERE `AuthorID`=?")) {
            $stmt->bind_param('i', $authorID);
            $stmt->execute();
            $stmt->bind_result($intelligenceID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $intelligenceID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return Intelligence::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query');
        }
    }
}