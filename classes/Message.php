<?php
namespace AMC\Classes;

use AMC\Exceptions\BlankObjectException;
use AMC\Exceptions\IncorrectTypeException;
use AMC\Exceptions\InvalidGroupException;
use AMC\Exceptions\InvalidUserException;
use AMC\Exceptions\QueryStatementException;

class Message implements DataObject {
    use Getable;
    use Storable;

    private $_id = null;
    private $_senderID = null;
    private $_subject = null;
    private $_body = null;
    private $_timestamp = null;
    private $_hideInSentBox = null;
    private $_connection = null;

    public function __construct($id = null, $senderID = null, $subject = null, $body = null, $timestamp = null, $hideInSentBox = null) {
        $this->_id = $id;
        $this->_senderID = $senderID;
        $this->_subject = $subject;
        $this->_body = $body;
        $this->_timestamp = $timestamp;
        $this->_hideInSentBox = $hideInSentBox;
        $this->_connection = Database::getConnection();
    }

    public function create() {
        if($this->eql(new Message())) {
            throw new BlankObjectException('Cannot store a blank Message.');
        }
        else {
            if($stmt = $this->_connection->prepare("INSERT INTO `Messages`(`SenderID`,`MessageSubject`,`MessageBody`,`MessageTimestamp`,`MessageHideInSentBox`) VALUES (?,?,?,?,?)")) {
                $stmt->bind_param('issii', $this->_senderID, $this->_subject, $this->_body, $this->_timestamp, Database::toNumeric($this->_hideInSentBox));
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function update() {
        if($this->eql(new Message())) {
            throw new BlankObjectException('Cannot store a blank Message.');
        }
        else {
            if($stmt = $this->_connection->prepare("UPDATE `Messages` SET `SenderID`=?,`MessageSubject`=?,`MessageBody`=?,`MessageTimestamp`=?,`MessageHideInSentBox`=? WHERE `MessageID`=?")) {
                $stmt->bind_param('issiii', $this->_senderID, $this->_subject, $this->_body, $this->_timestamp, Database::toNumeric($this->_hideInSentBox), $this->_id);
                $stmt->execute();
                $stmt->close();
            }
            else {
                throw new QueryStatementException('Failed to bind query.');
            }
        }
    }

    public function delete() {
        if($stmt = $this->_connection->prepare("DELETE FROM `Messages` WHERE `MessageID`=?")) {
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
            if($this->_id == $anotherObject->getID() && $this->_senderID == $anotherObject->getSenderID() && $this->_subject == $anotherObject->getSubject() && $this->_body == $anotherObject->getBody() && $this->_timestamp == $anotherObject->getTimestamp() && $this->_hideInSentBox == $anotherObject->getHideInSentBox()) {
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

    // Join table managers

    public function sendToUser($userID, $commit = true) {
        if(User::userExists($userID)) {
            if($commit) {
                $this->commit();
            }
            $userMessage = new UserMessage();
            $userMessage->setUserID($userID);
            $userMessage->setMessageID($this->_id);
            $userMessage->setAcknowledged(false);
            $userMessage->commit();
        }
        else {
            throw new InvalidUserException('There is no user with id ' . $userID);
        }
    }

    public function sendToGroup($groupID) {
        if(Group::groupExists($groupID)) {
            $this->commit();
            $userGroups = UserGroup::getByGroupID($groupID);
            foreach($userGroups as $userGroup) {
                $this->sendToUser($userGroup->getUserID(), false);
            }
        }
        else {
            throw new InvalidGroupException('There is no group with id ' . $groupID);
        }
    }

    public function deleteFromSentBox() {
        $this->_hideInSentBox = true;
        $this->commit();
    }

    // Getters and setters

    public function getID() {
        return $this->_id;
    }

    public function getSenderID() {
        return $this->_senderID;
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

    public function getHideInSentBox() {
        return $this->_hideInSentBox;
    }

    public function setID($id) {
        $this->_id = $id;
    }

    public function setSenderID($senderID, $verify = false) {
        if($verify) {
            if(User::userExists($senderID)) {
                $this->_senderID = $senderID;
            }
            else {
                throw new InvalidUserException('There is no user with id ' . $senderID);
            }
        }
        else {
            $this->_senderID = $senderID;
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

    public function setHideInSentBox($hideInSentBox) {
        $this->_hideInSentBox = $hideInSentBox;
    }

    // Statics

    public static function select($id) {
        if(\is_array($id) && \count($id) > 0) {
            $messageResult = [];
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
            if($stmt = Database::getConnection()->prepare("SELECT `MessageID`,`SenderID`,`MessageSubject`,`MessageBody`,`MessageTimestamp`,`MessageHideInSentBox` FROM `Messages` WHERE `MessageID` IN (" . $questionString . ")")) {
                \call_user_func_array(array($stmt, 'bind_param'), $param);
                $stmt->execute();
                $stmt->bind_param($messageID, $senderID, $subject, $body, $timestamp, $hideInSentBox);
                while($stmt->fetch()) {
                    $message = new Message();
                    $message->setID($messageID);
                    $message->setSenderID($senderID);
                    $message->setSubject($subject);
                    $message->setBody($body);
                    $message->setTimestamp($timestamp);
                    $message->setHideInSentBox(Database::toBoolean($hideInSentBox));
                    $messageResult[] = $message;
                }
                $stmt->close();
                if(\count($messageResult) > 0) {
                    return $messageResult;
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
            $messageResult = [];
            if($stmt = Database::getConnection()->prepare("SELECT `MessageID`,`SenderID`,`MessageSubject`,`MessageBody`,`MessageTimestamp`,`MessageHideInSentBox` FROM `Messages`")) {
                $stmt->execute();
                $stmt->bind_param($messageID, $senderID, $subject, $body, $timestamp, $hideInSentBox);
                while($stmt->fetch()) {
                    $message = new Message();
                    $message->setID($messageID);
                    $message->setSenderID($senderID);
                    $message->setSubject($subject);
                    $message->setBody($body);
                    $message->setTimestamp($timestamp);
                    $message->setHideInSentBox(Database::toBoolean($hideInSentBox));
                    $messageResult[] = $message;
                }
                $stmt->close();
                if(\count($messageResult) > 0) {
                    return $messageResult;
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

    public static function getBySenderID($senderID) {
        if($stmt = Database::getConnection()->prepare("SELECT `MessageID` FROM `Messages` WHERE `SenderID`=?")) {
            $stmt->bind_param('i', $senderID);
            $stmt->execute();
            $stmt->bind_result($messageID);
            $input = [];
            while ($stmt->fetch()) {
                $input[] = $senderID;
            }
            $stmt->close();
            if (\count($input) > 0) {
                return Message::get($input);
            } else {
                return null;
            }
        }
        else {
            throw new QueryStatementException('Failed to bind query.');
        }
    }

    public static function messageExists($messageID) {
        if(\is_numeric($messageID)) {
            if($stmt = Database::getConnection()->prepare("SELECT `MessageID` FROM `Messages` WHERE `MessageID`=?")) {
                $stmt->bind_params('i', $messageID);
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
            throw new IncorrectTypeException('Message exists must be given an int, was given a ' . \gettype($messageID));
        }
    }


}