<?php
//TODO: Add invalid intelligence exceptions

namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidIntelligenceTypeException;
use AMC\Exceptions\InvalidUserException;
use AMC\Exceptions\QueryStatementException;

class Intelligence implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_authorID = null;
    private $_intelligenceTypeID = null;
    private $_subject = null;
    private $_body = null;
    private $_timestamp = null;
    private $_public = null;
    private $_connection = null;

    public function __construct($id = null, $authorID = null, $intelligenceTypeID = null, $subject = null, $body = null, $timestamp = null, $public = null) {
        $this->_id = $id;
        $this->_authorID = $authorID;
        $this->_intelligenceTypeID = $intelligenceTypeID;
        $this->_subject = $subject;
        $this->_body = $body;
        $this->_public = $public;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new Intelligence())) {
            throw new BlankObjectException('Cannot store blank Intelligence.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Intelligence`(`AuthorID`,`IntelligenceTypeID`,`IntelligenceSubject`,`IntelligenceBody`,`IntelligenceTimestamp`,`IntelligencePublic`) VALUES (?,?,?,?,?,?)")) {
                $stmt->bind_param('iissii', $this->_authorID, $this->_intelligenceTypeID, $this->_subject, $this->_body, $this->_timestamp, Database::toNumeric($this->_public));
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new Intelligence())) {
            throw new BlankObjectException('Cannot store blank Intelligence.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Intelligence` SET `AuthorID`=?,`IntelligenceTypeID`=?,`IntelligenceSubject`=?,`IntelligenceBody`=?,`IntelligenceTimestamp`=?,`IntelligencePublic`=? WHERE `IntelligenceID`=?")) {
                $stmt->bind_param('iissiii', $this->_authorID, $this->_intelligenceTypeID, $this->_subject, $this->_body, $this->_timestamp, Database::toNumeric($this->_public), $this->_id);
                $stmt->exeucte();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
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
            if($this->_id == $anotherObject->getID() && $this->_authorID == $anotherObject->getAuthorID() && $this->_intelligenceTypeID == $anotherObject->getIntelligenceTypeID() && $this->_subject == $anotherObject->getSubject() && $this->_body == $anotherObject->getBody() && $this->_timestamp == $anotherObject->getTimestamp() && $this->_public == $anotherObject->getPublic()) {
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

    //TODO: ########################################
    //TODO: ###### MAKE THE SHOW/HIDE METHODS ######
    //TODO: ########################################

    // Setters and getters

    public function getID() {
        return $this->_id;
    }

    public function getAuthorID() {
        return $this->_authorID;
    }

    public function getIntelligenceTypeID() {
        return $this->_intelligenceTypeID;
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

    public function getPublic() {
        return $this->_public;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setAuthorID($authorID, $verify = false) {
        if($verify) {
            if(User::userExists($authorID)) {
                $this->_authorID = $authorID;
            }
            else {
                throw new InvalidUserException('No user exists with id ' . $authorID);
            }
        }
        else {
            $this->_authorID = $authorID;
        }
    }

    public function setIntelligenceTypeID($intelligenceTypeID, $verify = false) {
        if($verify) {
            if(IntelligenceType::intelligenceTypeExists($intelligenceTypeID)) {
                $this->_intelligenceTypeID = $intelligenceTypeID;
            }
            else {
                throw new InvalidIntelligenceTypeException('No intelligence type exists with id ' . $intelligenceTypeID);
            }
        }
        else {
            $this->_intelligenceTypeID = $intelligenceTypeID;
        }

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

    public function setPublic($public) {
        $this->_public = $public;
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
            if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceID`,`AuthorID`,`IntelligenceTypeID`,`IntelligenceSubject`,`IntelligenceBody`,`IntelligenceTimestamp`,`IntelligencePublic` FROM `Intelligence` WHERE `IntelligenceID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($intelligenceID, $authorID, $intelligenceTypeID, $subject, $body, $timestamp, $public);
                while($stmt->fetch()) {
                    $intelligence = new Intelligence();
                    $intelligence->setID($intelligenceID);
                    $intelligence->setAuthorID($authorID);
                    $intelligence->setIntelligenceTypeID($intelligenceTypeID);
                    $intelligence->setSubject($subject);
                    $intelligence->setBody($body);
                    $intelligence->setTimestamp($timestamp);
                    $intelligence->setPublic(Database::toBoolean($public));
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
            if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceID`,`AuthorID`,`IntelligenceTypeID`,`IntelligenceSubject`,`IntelligenceBody`,`IntelligenceTimestamp`,`IntelligencePublic` FROM `Intelligence`")) {
                $stmt->execute();
                $stmt->bind_result($intelligenceID, $authorID, $intelligenceTypeID, $subject, $body, $timestamp, $public);
                while($stmt->fetch()) {
                    $intelligence = new Intelligence();
                    $intelligence->setID($intelligenceID);
                    $intelligence->setAuthorID($authorID);
                    $intelligence->setIntelligenceTypeID($intelligenceTypeID);
                    $intelligence->setSubject($subject);
                    $intelligence->setBody($body);
                    $intelligence->setTimestamp($timestamp);
                    $intelligence->setPublic(Database::toBoolean($public));
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

    public static function getByIntelligenceTypeID($intelligenceTypeID) {
        if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceID` FROM `Intelligence` WHERE `IntelligenceTypeID`=?")) {
            $stmt->bind_param('i', $intelligenceTypeID);
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

    public static function getByPublic($public) {
        if($stmt = Database::getConnection()->prepare("SELECT `IntelligenceID` FROM `Intelligence` WHERE `IntelligencePublic`=?")) {
            $stmt->bind_param('i', Database::toNumeric($public));
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