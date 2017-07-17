<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\QueryStatementException;

class UserMessage implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_userID = null;
    private $_messageID = null;
    private $_acknowledged = null;
    private $_connection = null;

    public function __construct($id = null, $userID = null, $messagedID = null, $acknowledged = null) {
        $this->_id = $id;
        $this->_userID = $userID;
        $this->_messageID = $messagedID;
        $this->_acknowledged = $acknowledged;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new UserMessage())) {
            throw new BlankObjectException("Cannot store a blank user object");
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `User_Messages` (`UserID`,`MessageID`,`UserMessageAcknowledged`) VALUES (?,?,?)")) {
                $stmt->bind_param('iii', $this->_userID, $this->_messageID, Database::toNumeric($this->_acknowledged));
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function update() {
        if($this->eql(new UserMessage())) {
            throw new BlankObjectException("Cannot store a blank user object");
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `User_Messages` SET `UserID`=?,`MessagedID`=?,`UserMessageAcknowledged`=? WHERE `UserMessageID`=?")) {
                $stmt->bind_param('iiii', $this->_userID, $this->_messageID, Database::toNumeric($this->_acknowledged), $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException("Failed to bind query");
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `User_Messages` WHERE `UserMessageID`=?")) {
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
        if(\get_class($this) && \get_class($anotherObject)) {
            if($this->_id == $anotherObject->getID() && $this->_userID == $anotherObject->getUserID() && $this->_messageID == $anotherObject->getMessageID() && $this->_acknowledged == $anotherObject->getAcknowledged()) {
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

    public function getMessageID() {
        return $this->_messageID;
    }

    public function getAcknowledged() {
        return $this->_acknowledged;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setUserID($userID) {
        $this->_userID = $userID;
    }

    public function setMessageID($messageID) {
        $this->_messageID = $messageID;
    }

    public function setAcknowledged($acknowledged) {
        $this->_acknowledged = $acknowledged;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $userMessageResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `UserMessageID`,`UserID`,`MessageID`,`UserMessageAcknowledged` FROM `User_Messages` WHERE `UserMessageID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($userMessageID, $userID, $messageID, $acknowledged);
                while($stmt->fetch()) {
                    $userMessage = new UserMessage();
                    $userMessage->setID($userMessageID);
                    $userMessage->setUserID($userID);
                    $userMessage->setMessageID($messageID);
                    $userMessage->setAcknowledged(Database::toBoolean($acknowledged));
                    $userMessageResult[] = $userMessage;
                }
                $stmt->close();
                if(\count($userMessageResult) > 0 ) {
                    return $userMessageResult;
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
            $userMessageResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `UserMessageID`,`UserID`,`MessageID`,`UserMessageAcknowledged` FROM `User_Messages`")) {
                $stmt->execute();
                $stmt->bind_result($userMessageID, $userID, $messageID, $acknowledged);
                while($stmt->fetch()) {
                    $userMessage = new UserMessage();
                    $userMessage->setID($userMessageID);
                    $userMessage->setUserID($userID);
                    $userMessage->setMessageID($messageID);
                    $userMessage->setAcknowledged(Database::toBoolean($acknowledged));
                    $userMessageResult[] = $userMessage;
                }
                $stmt->close();
                if(\count($userMessageResult) > 0 ) {
                    return $userMessageResult;
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

    public static function getByUserID($userID) {
        if($stmt = Database::getConnection()->prepare("SELECT `UserMessageID` FROM `User_Messages` WHERE `UserID`=?")) {
            $stmt->bind_param('i', $userID);
            $stmt->execute();
            $stmt->bind_result($userMessageID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $userMessageID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return UserMessage::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException("Failed to bind query");
        }
    }

    public static function getByMessageID($messageID) {
        if($stmt = Database::getConnection()->prepare("SELECT `UserMessageID` FROM `User_Messages` WHERE `MessageID`=?")) {
            $stmt->bind_param('i', $messageID);
            $stmt->execute();
            $stmt->bind_result($userMessageID);
            $input = [];
            while($stmt->fetch()) {
                $input[] = $userMessageID;
            }
            $stmt->close();
            if(\count($input) > 0) {
                return UserMessage::get($input);
            }
            else {
                return null;
            }
        }
        else {
            throw new QueryStatementException("Failed to bind query");
        }
    }

}