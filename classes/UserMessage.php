<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\InvalidMessageException;
use AMC\Exceptions\InvalidUserException;
use AMC\Exceptions\QueryStatementException;

class UserMessage implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_userID = null;
    private $_messageID = null;
    private $_acknowledged = null;
    private $_hideInInbox = null;
    private $_connection = null;

    public function __construct($id = null, $userID = null, $messagedID = null, $acknowledged = null, $hideInInbox = null) {
        $this->_id = $id;
        $this->_userID = $userID;
        $this->_messageID = $messagedID;
        $this->_acknowledged = $acknowledged;
        $this->_hideInInbox = $hideInInbox;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new UserMessage())) {
            throw new BlankObjectException('Cannot store a blank User Object.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `User_Messages` (`UserID`,`MessageID`,`UserMessageAcknowledged`,`UserMessageHideInInbox`) VALUES (?,?,?,?)")) {
                $stmt->bind_param('iiii', $this->_userID, $this->_messageID, Database::toNumeric($this->_acknowledged), Database::toNumeric($this->_hideInInbox));
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new UserMessage())) {
            throw new BlankObjectException('Cannot store a blank User Object.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `User_Messages` SET `UserID`=?,`MessagedID`=?,`UserMessageAcknowledged`=?,`UserMessageHideInInbox`=? WHERE `UserMessageID`=?")) {
                $stmt->bind_param('iiiii', $this->_userID, $this->_messageID, Database::toNumeric($this->_acknowledged), Database::toNumeric($this->_hideInInbox), $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
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
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public function eql($anotherObject) {
        if(\get_class($this) && \get_class($anotherObject)) {
            if($this->_id == $anotherObject->getID() && $this->_userID == $anotherObject->getUserID() && $this->_messageID == $anotherObject->getMessageID() && $this->_acknowledged == $anotherObject->getAcknowledged() && $this->_hideInInbox == $anotherObject->getHideInInbox()) {
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

    public function deleteFromInbox() {
        $this->_hideInInbox = true;
        $this->commit();
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

    public function getHideInInbox() {
        return $this->_hideInInbox;
    }

    public function setID($id) {
        $this->_id = $id;
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

    public function setMessageID($messageID, $verify = false) {
        if($verify) {
            if(Message::messageExists($messageID)) {
                $this->_messageID = $messageID;
            }
            else {
                throw new InvalidMessageException('No message exists with id ' . $messageID);
            }
        }
        else {
            $this->_messageID = $messageID;
        }
    }

    public function setAcknowledged($acknowledged) {
        $this->_acknowledged = $acknowledged;
    }

    public function setHideInInbox($hideInInbox) {
        $this->_hideInInbox = $hideInInbox;
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
            for($i = 0; $i < \count($id) - 1; $i++) {
                $typeArray[0] .= 'i';
                $questionString .= ',?';
            }
            $param = \array_merge($typeArray, $refs);
            if($stmt = Database::getConnection()->prepare("SELECT `UserMessageID`,`UserID`,`MessageID`,`UserMessageAcknowledged`,`UserMessageHideInInbox` FROM `User_Messages` WHERE `UserMessageID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_result($userMessageID, $userID, $messageID, $acknowledged, $hideInInbox);
                while($stmt->fetch()) {
                    $userMessage = new UserMessage();
                    $userMessage->setID($userMessageID);
                    $userMessage->setUserID($userID);
                    $userMessage->setMessageID($messageID);
                    $userMessage->setAcknowledged(Database::toBoolean($acknowledged));
                    $userMessage->setHideInInbox(Database::toBoolean($hideInInbox));
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
                throw new QueryStatementException('Failed to bind query.');
            }
        }
        else if(\is_array($id) && \count($id) == 0) {
            $userMessageResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `UserMessageID`,`UserID`,`MessageID`,`UserMessageAcknowledged`,`UserMessageHideInInbox` FROM `User_Messages`")) {
                $stmt->execute();
                $stmt->bind_result($userMessageID, $userID, $messageID, $acknowledged, $hideInInbox);
                while($stmt->fetch()) {
                    $userMessage = new UserMessage();
                    $userMessage->setID($userMessageID);
                    $userMessage->setUserID($userID);
                    $userMessage->setMessageID($messageID);
                    $userMessage->setAcknowledged(Database::toBoolean($acknowledged));
                    $userMessage->setHideInInbox(Database::toBoolean($hideInInbox));
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
                throw new QueryStatementException('Failed to bind query.');
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
            throw new QueryStatementException('Failed to bind query.');
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
            throw new QueryStatementException('Failed to bind query.');
        }
    }

}